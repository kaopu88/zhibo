<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class BeanLog extends Service
{
    public function getTotal($get){
        $this->db = Db::name('bean_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getTotalAmount($get){
        $this->db = Db::name('bean_log');
        $this->setWhere($get);
        return $this->db->sum('total');
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['type'])) {
            $where['type'] = $get['type'];
        }
        if (!empty(trim($get['user_id']))) {
            $where['user_id'] = trim($get['user_id']);
        }
        if ($get['trade_type'] != '') {
            $where['trade_type'] = $get['trade_type'];
        }
        if ($get['start_time'] != '' &&  $get['end_time'] != '') {
            $this->db->whereTime('create_time', 'between', [$get['start_time'] . ' 0:0:0', $get['end_time'] . ' 23:59:59']);
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','log_no,trade_no,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('bean_log');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
        }
    }
}