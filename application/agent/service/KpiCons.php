<?php

namespace app\agent\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class KpiCons extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('kpi_cons');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('kpi_cons');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        list($agentIds, $promoterIds, $userIds) = self::getIdsByList($result, 'agent_id|promoter_uid|cons_uid', true);
        $userService = new User();
        $promoterList = $userService->getUsersByIds($promoterIds);
        $userList = $userService->getUsersByIds($userIds);
        $agentService = new Agent();
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($result as &$item) {
            $item['rel_type'] = strtolower($item['rel_type']);
            $levelInfo = ExpLevel::getLevelInfo($item['cons_level']);
            $item['cons_level_name'] = $levelInfo['level_name'];
            $item['cons_level_icon'] = $levelInfo['level_icon'];
            $item['cons_level_up'] = $levelInfo['level_up'];
            $fun = 'parse' . parse_name($item['rel_type'], 1, true);
            $item['rel_info'] = [];
            if (method_exists($this, $fun)) {
                call_user_func_array([$this, $fun], [&$item]);
            }
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
            $item['user'] = self::getItemByList($item['cons_uid'], $userList, 'user_id');
        }
        return $result;
    }

    protected function parseBarrage(&$item)
    {
        $item['rel_info'] = [
            'order_no' => $item['rel_no'],
            'name' => '直播弹幕',
            'price' => $item['total_fee']
        ];
    }

    public function parseBuylottery(&$item)
    {
        $item['rel_info'] = [
            'order_no' => $item['rel_no'],
            'name' => '转盘抽奖',
            'price' => $item['total_fee']
        ];
    }


    protected function parseLiveGift(&$item)
    {
        $rel_no = $item['rel_no'];
        $item['rel_info'] = Db::name('gift_log')->where('gift_no', $rel_no)->find();
    }

    protected function parseCoverStarVote(&$item)
    {
        $item['rel_info'] = [
            'order_no' => $item['rel_no'],
            'name' => '封面之星投票',
            'price' => $item['total_fee']
        ];
    }

    protected function parseUserPackage(&$item)
    {
        $item['rel_info'] = [
            'order_no' => $item['rel_no'],
            'name' => '用户背包',
            'price' => $item['total_fee']
        ];
    }

    protected function parseLivePayment(&$item)
    {
        $item['rel_info'] = [
            'order_no' => $item['rel_no'],
            'name' => '观看付费直播',
            'price' => $item['total_fee']
        ];
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            //$order['create_time'] = 'DESC';
            $order['id'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [];
        $this->db->field('id,agent_id,trade_no,rel_type,rel_no,total_fee,loss_total,subject,pay_method,pay_platform,promoter_uid,cons_uid,create_time');
        \bxkj_module\service\Agent::agentWhere($where, ['agent_id' => $get['agent_id']], '');
        if ($get['rel_type'] != '') {
            $where[] = ['rel_type', '=', $get['rel_type']];
        } else {

        }
        if ($get['pay_platform'] != '') {
            $where[] = ['pay_platform', '=', $get['pay_platform']];
        }
        if (trim($get['promoter_uid']) != '') {
            $promoter_uids = explode(',', trim($get['promoter_uid']));
            if (count($promoter_uids) > 1) {
                $where[] = ['promoter_uid', 'in', trim($get['promoter_uid'])];
            } else {
                $where[] = ['promoter_uid', '=', trim($get['promoter_uid'])];
            }
        }
        if (trim($get['user_id']) != '') {
            $where[] = ['cons_uid', '=', trim($get['user_id'])];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'number rel_no');
        $this->db->where($where);
        return $this;
    }
}