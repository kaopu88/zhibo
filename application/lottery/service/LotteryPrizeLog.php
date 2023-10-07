<?php

namespace app\lottery\service;

use bxkj_module\service\Service;
use think\Db;

class LotteryPrizeLog extends Service
{


    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('lottery_prize_log');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }


    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('lottery_prize_log');
        $this->setWhere($get)->setJoin()->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }

    private function setJoin()
    {
        $this->db->alias('lpl')->join('__USER__ user', 'user.user_id=lpl.user_id', 'LEFT');
        $this->db->field('lpl.*,user.nickname as user_name,user.avatar,user.phone');
        return $this;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if (isset($get['gift_source'])) {
            $where[] = ['lpl.gift_source', '=', $get['gift_source']];
        }

        $this->db->setKeywords(trim($get['keyword']), '', 'number user.user_id', 'user.nickname,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['lpl.id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }
}