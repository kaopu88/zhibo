<?php

namespace app\promoter\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class RechargeOrder extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('recharge_order');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('recharge_order');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $levelInfo = ExpLevel::getLevelInfo($item['level']);
            $item = array_merge($item, $levelInfo ? $levelInfo : []);
            $item['total_bean'] = $item['quantity'] * $item['bean_num'];
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['recharge.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function getSummary($get)
    {
        $db = Db::name('recharge_order');
        $db->alias('recharge');
        $db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
        $db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');

        if ($get['promoter_uid'] != '') {
            $promoter_uids = explode(',', $get['promoter_uid']);
            if (count($promoter_uids) > 1)
            {
                $where[] = ['pr.promoter_uid', 'in', $get['promoter_uid']];
            }else{
                $where[] = ['pr.promoter_uid', '=', $get['promoter_uid']];
            }
        }

        if (!empty($get['pay_method'])) $where[] = ['recharge.pay_method', '=', $get['pay_method']];
        if (!empty($get['pay_status'])) $where[] = ['recharge.pay_status', '=', $get['pay_status']];
        $where[] = ['recharge.isvirtual', '=', '0'];
        if (!empty($get['user_id'])) $where[] = ['recharge.user_id', '=', $get['user_id']];
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = str_replace('-', '', $get['start_time']);
            $end_time = str_replace('-', '', $get['end_time']);
            $where[] = ['recharge.day', 'between', [$start_time, $end_time]];
        }

        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'pr.');
        //增加非绑定过滤
        $where[] = ["pr.promoter_uid", 'neq', 0];
        $db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $db->setKeywords($get['order_no'], '', '', 'number recharge.order_no');

        $result = $db->where($where)->field('sum(recharge.price) price_total, recharge.pay_method')->group('recharge.pay_method')->select();

        if (!empty($result))
        {
            $result = array_column($result, 'price_total', 'pay_method');
            $result['summary'] = array_sum($result);
        }
        return $result;
    }

    protected function setWhere($get)
    {
        $where = [];
        $this->db->alias('recharge');
        $this->db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
        $this->db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.remark_name,user.level,user.phone');
        $this->db->field('recharge.id,recharge.order_no,recharge.bean_id,recharge.apple_id,recharge.bean_num,recharge.price,recharge.name,recharge.user_id,recharge.pay_method,recharge.third_trade_no,recharge.pay_status,recharge.pay_time,recharge.client_ip,recharge.app_v,recharge.log_no,recharge.create_time,recharge.quantity,recharge.total_fee,recharge.isvirtual');
        if ($get['promoter_uid'] != '') {
            $promoter_uids = explode(',', $get['promoter_uid']);
            if (count($promoter_uids) > 1)
            {
                $where[] = ['pr.promoter_uid', 'in', $get['promoter_uid']];
            }else{
                $where[] = ['pr.promoter_uid', '=', $get['promoter_uid']];
            }
        }
        if ($get['pay_method'] != '') {
            $where[] = ['recharge.pay_method', '=', $get['pay_method']];
        }
        if ($get['pay_status'] != '') {
            $where[] = ['recharge.pay_status', '=', $get['pay_status']];
        }
        if ($get['user_id'] != '') {
            $where[] = ['recharge.user_id', '=', $get['user_id']];
        }
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = str_replace('-', '', $get['start_time']);
            $end_time = str_replace('-', '', $get['end_time']);
            $where[] = ['recharge.day', 'between', [$start_time, $end_time]];
        }
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'pr.');
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $this->db->setKeywords($get['order_no'], '', '', 'number recharge.order_no');
        $this->db->where($where);
        return $this;
    }
}