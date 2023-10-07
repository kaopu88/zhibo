<?php

namespace app\promoter\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class Promoter extends \bxkj_module\service\Promoter
{
    public function getIndexTotal($get)
    {
        $this->db = Db::name('promoter');
        $this->setIndexWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getIndex($get, $offset = 0, $length = 20)
    {
        $fields = 'promoter.user_id,promoter.agent_id,promoter.total_cons,promoter.total_fans,promoter.create_time,promoter.client_num,
        user.nickname,user.username,user.phone,user.avatar,user.status,user.type,user.live_status,user.level';
        $this->db = Db::name('promoter');
        $this->setIndexWhere($get);
        $this->setIndexOrder($get);
        $index = $this->db->field($fields)->limit($offset, $length)->select();
        list($agentIds, $promoterIds) = self::getIdsByList($index, 'agent_id|promoter_uid', true);
        $userService = new User();
        $promoterList = $userService->getUsersByIds($promoterIds);
        $agentService = new Agent();
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($index as &$item) {
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
        }
        return $index ? $index : [];
    }

    protected function setIndexWhere($get)
    {
        $this->db->alias('promoter');
        $where = [];
        if ($get['agent_id']){
            Agent::agentWhere($where, ['agent_id' => $get['agent_id']], 'promoter.');
        }else{
            $agentIds = Agent::getAgentIds(AGENT_ID);
        }
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id', 'LEFT');
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        $this->db->where($where);
        if (!isset($get['agent_id'])) {
            $this->db->whereIn('promoter.agent_id', $agentIds);
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number promoter.user_id', 'number user.phone,user.nickname');
        return $this;
    }

    protected function setIndexOrder($get)
    {
        $this->db->order('promoter.create_time desc,promoter.id desc');
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('promoter');
        $this->db->alias('promoter');
        $where = [['promoter.delete_time', 'null']];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'promoter.');
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id', 'LEFT');
        $this->db->setKeywords($keyword, 'phone user.phone', 'number promoter.user_id', 'number user.phone,user.nickname,user.remark_name');
        $this->db->where($where);
        $this->db->field('promoter.user_id,user.nickname,user.username,user.phone,user.remark_name');
        $this->db->order(['promoter.create_time' => 'desc', 'promoter.id' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['user_id'],
                'name' => user_name($item) . ($item['phone'] ? "({$item['phone']})" : '')
            ];
        }
        return $arr;
    }

    public function getInfo($userId)
    {
        if (empty($userId)) return $this->setError(config('app.agent_setting.promoter_name').'不存在');
        $where = [
            ['user_id', '=', $userId],
            ['delete_time', 'null']
        ];
        $agentIds = Agent::getAgentIds(AGENT_ID);
        $promoter = Db::name('promoter')->where($where)->whereIn('agent_id', $agentIds)->find();
        return $promoter;
    }

    public function add($inputData)
    {
        $userId = $inputData['user_id'];
        if (empty($userId)) return $this->setError('请选择用户');
        $where = [['user_id', '=', $userId], ['delete_time', 'null'],];
        Service::startTrans();
        $user = Db::name('user')->where($where)->find();
        if (empty($user)) return $this->setError('用户不存在');
        //判断是否具有新增config('app.agent_setting.promoter_name')权限
        $add_promoter = Db::name('agent')->where('id',AGENT_ID)->value('add_promoter');
        if (!$add_promoter) return $this->setError('无新增'.config('app.agent_setting.promoter_name').'权限，请联系总后台管理员！');
        //判断是否超出config('app.agent_setting.promoter_name')限额
        $max_promoter_num = Db::name('agent')->where('id',AGENT_ID)->value('max_promoter_num');
        $promoter_num = Db::name('agent')->where('id', AGENT_ID)->value('promoter_num');
        if ($promoter_num >= $max_promoter_num) return $this->setError('超出'.config('app.agent_setting.promoter_name').'限额，请联系总后台管理员！');
        if ($user['is_promoter'] == '1') return $this->setError('用户已是'.config('app.agent_setting.promoter_name'));
        $where2 = array('user_id' => $userId, 'delete_time' => null);
        $num = Db::name('promoter')->where($where2)->count();
        if ($num > 0) return $this->setError('用户已是'.config('app.agent_setting.promoter_name'));
        if (empty($user['agent_id'])) return $this->setError('用户需要分配到'.config('app.agent_setting.agent_name').'名下');
        $promoterData = [];
        $promoterData['agent_id'] = $user['agent_id'];
        $where3 = [
            ['promoter_uid', '=', $userId],
            ['delete_time', 'null']
        ];
        $promoterData['user_id'] = $userId;
        $promoterData['create_time'] = time();
        $promoterData['client_num'] = Db::name('user')->where($where3)->count();
        $res = Db::name('promoter')->insert($promoterData);
        if (!$res) return $this->setError('新增失败');
        $update = ['is_promoter' => '1'];
        Db::name('user')->where('user_id', $userId)->update($update);
        Db::name('agent')->where('id', $promoterData['agent_id'])->setInc('promoter_num', 1);
        if ($promoterData['client_num'] > 0) {
            $this->updateAgentInfo($userId, $promoterData['agent_id']);
        }
        \bxkj_module\service\User::updateRedis($userId, $update);
        Service::commit();
        return $promoterData;
    }

}