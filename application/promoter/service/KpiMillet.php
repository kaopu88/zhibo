<?php

namespace app\promoter\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class KpiMillet extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('kpi_millet');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('kpi_millet');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        list($agentIds, $userIds, $contIds) = self::getIdsByList($result, 'agent_id|promoter_uid,get_uid|cont_uid', true);
        $userService = new User();
        $userList = $userService->getUsersByIds($userIds);
        $contList = $userService->getUsersByIds($contIds);
        $agentService = new Agent();
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($result as &$item) {
            $levelInfo = ExpLevel::getLevelInfo($item['cont_level']);
            $item['cont_level_name'] = $levelInfo['level_name'];
            $item['cont_level_icon'] = $levelInfo['level_icon'];
            $item['cont_level_up'] = $levelInfo['level_up'];
            $fun = 'parse' . parse_name($item['trade_type'], 1, true);
            $item['trade_info'] = [];
            if (method_exists($this, $fun)) {
                call_user_func_array([$this, $fun], [&$item]);
            }
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $userList, 'user_id');
            $item['anchor_info'] = self::getItemByList($item['get_uid'], $userList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
            $item['cont_info'] = self::getItemByList($item['cont_uid'], $contList, 'user_id');
        }
        return $result;
    }

    protected function parseLiveGift(&$item)
    {
        $log = Db::name('gift_log')->where('gift_no', $item['trade_no'])->find();
        $item['trade_info'] = $log;
    }

    protected function parseBarrage(&$item)
    {
        $item['trade_info'] = [
            'gift_no' => $item['trade_no'],
            'name' => '直播弹幕',
            'conv_millet' => $item['millet']
        ];
    }

    protected function parseUserPackage(&$item)
    {
        $item['trade_info'] = [
            'gift_no' => $item['trade_no'],
            'name' => '用户背包',
            'conv_millet' => $item['millet']
        ];
    }

    protected function parseLivePayment(&$item)
    {
        $item['trade_info'] = [
            'gift_no' => $item['trade_no'],
            'name' => '观看付费直播',
            'conv_millet' => $item['millet']
        ];
    }

    protected function parseCoverStarVote(&$item)
    {
        $item['trade_info'] = [
            'gift_no' => $item['trade_no'],
            'name' => '封面之星投票',
            'conv_millet' => $item['millet']
        ];
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
        $this->db->field('id,get_uid,agent_id,millet,trade_type,trade_no,log_no,cont_uid,cont_agent_id,promoter_uid,create_time');
        \bxkj_module\service\Agent::agentWhere($where, ['agent_id' => $get['agent_id']], '');
        if ($get['trade_type'] != '') {
            $where[] = ['trade_type', '=', $get['trade_type']];
        }
        if ($get['user_id'] != '') {
            $where[] = ['cont_uid', '=', $get['user_id']];
        }
        if ($get['anchor_uid'] != '') {
            $where[] = ['get_uid', '=', $get['anchor_uid']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'number trade_no');
        $this->db->where($where);
        return $this;
    }
}