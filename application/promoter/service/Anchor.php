<?php

namespace app\promoter\service;

use bxkj_module\service\ExpLevel;
use think\Db;

class Anchor extends \bxkj_module\service\Anchor
{
    public function getIndexTotal($get)
    {
        $this->db = Db::name('anchor');
        $this->setIndexWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getIndex($get, $offset = 0, $length = 20)
    {
        $fields = 'anchor.user_id,anchor.agent_id,anchor.total_millet,anchor.total_duration,anchor.create_time,
        user.nickname,user.username,user.phone,user.avatar,user.status,user.type,user.live_status,user.level';
        $this->db = Db::name('anchor');
        $this->setIndexWhere($get);
        $this->setIndexOrder($get);
        $index = $this->db->field($fields)->limit($offset, $length)->select();
        $agentService = new Agent();
        $agentIds = self::getIdsByList($index, 'agent_id');
        $agents = $agentService->getAgentsByIds($agentIds);
        foreach ($index as &$item) {
            $item['total_duration_str'] = $item['total_duration'] > 0 ? time_str($item['total_duration'], 'i') : '无';
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agents, 'id');
            $item['location'] = $this->getLocation($item['user_id']);
        }
        return $index ? $index : [];
    }

    protected function parseList(&$index)
    {
        foreach ($index as $i => &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            $item['duration_str'] = $item['duration'] > 0 ? time_str($item['duration'], 'i') : '无';
            $item['total_duration_str'] = $item['total_duration'] > 0 ? time_str($item['total_duration'], 'i') : '无';
        }
    }

    protected function setIndexWhere($get)
    {
        $this->db->alias('anchor');
        $where = [];
        if ($get['agent_id']){
            Agent::agentWhere($where, ['agent_id' => $get['agent_id']], 'anchor.');
        }else{
            $agentIds = Agent::getAgentIds(AGENT_ID);
        }
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        if ($get['live_status'] != '') {
            $where[] = ['user.live_status', '=', $get['live_status']];
        }
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        if (!isset($get['agent_id'])){
            $this->db->whereIn('anchor.agent_id', $agentIds);
        }
        return $this;
    }

    protected function setIndexOrder($get)
    {
        $this->db->order('anchor.create_time desc,anchor.id desc');
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('anchor');
        $this->db->alias('anchor');
        $where = [['anchor.delete_time', 'null']];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'anchor.');
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->setKeywords($keyword, 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        $this->db->field('anchor.user_id,user.nickname,user.username,user.phone');
        $this->db->order(['anchor.create_time' => 'desc', 'anchor.id' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['user_id'],
                'name' => $item['nickname'] . ($item['phone'] ? "({$item['phone']})" : '')
            ];
        }
        return $arr;
    }

    public function getInfo($userId)
    {
        if (empty($userId)) return $this->setError('主播不存在');
        $where = [['user_id', '=', $userId], ['delete_time', 'null']];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID]);
        $anchor = Db::name('anchor')->where($where)->find();
        return $anchor;
    }

    public function add($inputData)
    {
        $userId = $inputData['user_id'];
        if (empty($userId)) return $this->setError('请选择用户');
        $where = [['user_id', '=', $userId], ['delete_time', 'null']];
        $user = Db::name('user')->where($where)->find();
        if (empty($user)) return $this->setError('用户不存在');
        //判断是否具有新增主播权限
        $add_anchor = Db::name('agent')->where('id',AGENT_ID)->value('add_anchor');
        if (!$add_anchor) return $this->setError('无新增主播权限，请联系总后台管理员！');
        //判断是否超出主播限额
        $max_anchor_num = Db::name('agent')->where('id',AGENT_ID)->value('max_anchor_num');
        $anchor_num = Db::name('agent')->where('id', AGENT_ID)->value('anchor_num');
        if ($anchor_num >= $max_anchor_num) return $this->setError('超出主播限额，请联系总后台管理员！');
        $agentId = $inputData['agent_id'] ? $inputData['agent_id'] : $user['agent_id'];
        if (empty($agentId)) return $this->setError('用户需要分配到'.config('app.agent_setting.agent_name').'名下');
        if ($user['is_anchor'] == '1') return $this->setError('用户已是主播');
        $where2 = ['user_id' => $userId, 'delete_time' => null];
        $num = Db::name('anchor')->where($where2)->count();
        if ($num > 0) return $this->setError('用户已是主播');
        $anchorData = [];
        $anchorData['agent_id'] = $user['agent_id'] ? $user['agent_id'] : '0';
        $anchorData['user_id'] = $userId;
        $anchorData['create_time'] = time();
        $res = Db::name('anchor')->insert($anchorData);
        if (!$res) return $this->setError('新增失败');
        $update = ['is_anchor' => '1', 'live_status' => '1'];
        Db::name('user')->where('user_id', $userId)->update($update);
        Db::name('agent')->where('id', $anchorData['agent_id'])->setInc('anchor_num', 1);
        \bxkj_module\service\User::updateRedis($userId, $update);
        return $anchorData;
    }


}