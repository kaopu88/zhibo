<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/13
 * Time: 17:56
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class IncomeLog extends Service
{
    public function addLog($data)
    {
        $this->db = Db::name("income_log");
        $id = $this->db->insertGetId($data);
        return $id;
    }

    public function getTotal($get)
    {
        $this->db = Db::name('income_log');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('income_log');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('il.id,il.user_id,il.name,il.money,il.create_time,user.nickname');
        $list = $this->db->limit($offset, $length)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $this->db->alias('il');
        $where = array();
        if (isset($get['name']) && $get['name'] != '') {
            $where['il.name'] = $get['name'];
        }
        if (isset($get['user_id']) && $get['user_id'] != '') {
            $where['il.user_id'] = $get['user_id'];
        }
        $this->db->where($where);
        $this->db->join('__USER__ user', 'user.user_id=il.user_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'il.id DESC';
        }
        $this->db->order($order);
        return $this;
    }

}