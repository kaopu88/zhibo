<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/9
 * Time: 17:14
 */

namespace app\taokeshop\service;

use bxkj_module\service\Service;
use think\Db;

class DredgeLog extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('dredge_log');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getLogList($get, $offset = 0, $length = 20)
    {
        $result = [];
        $this->db = Db::name('dredge_log');
        $this->setWhere($get)->setOrder($get);
        $fields = 'user.nickname';
        $this->db->field('dl.id,dl.order_no,dl.total_fee,dl.name,dl.pay_status,dl.create_time,dl.pay_time,dl.pay_method');
        $result = $this->db->field($fields)->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $this->db->alias('dl');
        $where = array();
        $where1 = array();
        if ($get['pay_status'] != '') {
            $where['dl.pay_status'] = $get['pay_status'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['user.phone|user.nickname', 'like', '%' . $get['keyword'] . '%'];
        }
        if ($get['type'] != '') {
            $where['dl.type'] = $get['type'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('__USER__ user', 'user.user_id=dl.user_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['dl.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function getLogInfo($where)
    {
        $this->db = Db::name('dredge_log');
        $result   = $this->db->where($where)->select();
        return $result;
    }
}