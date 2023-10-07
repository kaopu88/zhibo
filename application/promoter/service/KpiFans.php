<?php

namespace app\promoter\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class KpiFans extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('kpi_fans');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('kpi_fans');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        list($agentIds, $promoterIds) = self::getIdsByList($result, 'agent_id|promoter_uid', true);
        $userService = new User();
        $promoterList = $userService->getUsersByIds($promoterIds);
        $agentService = new Agent();
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($result as &$item) {
            //$levelInfo = ExpLevel::getLevelInfo();
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [];
        $this->db->alias('fans');
        \bxkj_module\service\Agent::agentWhere($where, ['agent_id' => $get['agent_id']]);
        if ($get['promoter_uid'] != '') {
            $promoter_uids = explode(',', $get['promoter_uid']);
            if (count($promoter_uids) > 1)
            {
                $where[] = ['promoter_uid', 'in', $get['promoter_uid']];
            }else{
                $where[] = ['promoter_uid', '=', $get['promoter_uid']];
            }
        }
        if ($get['user_id'] != '') {
            $where[] = ['user_id', '=', $get['user_id']];
        }
        if ($get['gender'] != '') {
            $where[] = ['gender', '=', $get['gender']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number user_id', 'number user_id,nickname');
        $this->db->where($where);
        return $this;
    }
}