<?php

namespace app\admin\service;

use bxkj_common\HttpClient;
use bxkj_module\service\Auth;
use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class Promoter extends \bxkj_module\service\Promoter
{

    public function getTotal($get)
    {
        $this->db = Db::name('promoter');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('promoter');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('user.user_id,user.nickname,user.avatar,user.level,user.remark_name,user.sign,
        promoter.agent_id,promoter.create_time,promoter.client_num');
        $result = $this->db->limit($offset, $length)->select();
        list($agentIds, $promoterIds, $cityIds) = self::getIdsByList($result, 'agent_id|promoter_uid|city_id', true);
        $userService = new \bxkj_module\service\User();
        $promoterList = $userService->getUsersByIds($promoterIds);
        foreach ($result as &$item) {
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['promoter.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $this->db->alias('promoter');
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id');
        $where = [
            ['user.delete_time', 'null'],
        ];
        if ($get['agent_id'] != '') {
            $where[] = ['promoter.agent_id', '=', $get['agent_id']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        return $this;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('promoter');
        $this->db->alias('promoter');
        $where = [];
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id', 'LEFT');
        $this->db->setKeywords($keyword, 'phone user.phone', 'number promoter.user_id', 'number user.phone,user.nickname,user.remark_name');
        $this->db->where($where);
        $this->db->field('promoter.user_id,user.nickname,user.username,user.phone,user.remark_name');
        $this->db->order(['promoter.create_time' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['user_id'],
                'name' => "[{$item['user_id']}]" . user_name($item) . ($item['phone'] ? "({$item['phone']})" : '')
            ];
        }
        return $arr;
    }


    public function getProTotal($get)
    {
        $this->db = Db::name('promoter');
        $this->setProWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getProList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('promoter');
        $this->setProWhere($get);
        $this->setProOrder($get);
        $fields = 'user.user_id,user.nickname,user.remark_name,user.phone,user.isvirtual,user.avatar,user.sign,user.level,user.gender,user.vip_expire,user.millet,promoter.create_time,user.fre_millet,user.millet_status,user.live_status,user.status,promoter.agent_id,promoter.total_cons,promoter.total_fans,promoter.client_num';
        $list = $this->db->field($fields)->limit($offset, $length)->select();
        $this->parseList($list);
        return $list ? $list : [];
    }

    protected function setProWhere($get)
    {
        $this->db->alias('promoter');
        $where = [];
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id', 'LEFT');
        $this->db->where($where);
        return $this;
    }

    protected function setProOrder($get)
    {
        $this->db->order('promoter.create_time desc,promoter.user_id desc');
    }

    protected function parseList(&$list)
    {
        $agentService = new Agent();
        $agentIds = self::getIdsByList($list, 'agent_id');
        $agents = $agentService->getAgentsByIds($agentIds);
        foreach ($list as $i => &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agents, 'id');
        }
    }

    public function getInfo($userId)
    {
        $where = [
            ['user_id', '=', $userId]
        ];
        $user = Db::name('user')->where($where)->find();
        if (empty($userId) || empty($user)) return $this->setError(config('app.agent_setting.promoter_name').'不存在1');
        $promoter = Db::name('promoter')->where($where)->find();
        return $promoter;
    }
}