<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/4
 * Time: 9:56
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class DuomaiOrder extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('duomai_order');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('duomai_order');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('id,user_id,ads_id,ads_name,site_id,link_id,euid,order_sn,order_time,orders_price,siter_commission,status,confirm_price,confirm_siter_commission,charge_time,rebate');
        $list = $this->db->limit($offset, $length)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $this->db->alias('c');
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['ads_name|ads_id','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['user_id']) && $get['user_id'] != '') {
            $where['user_id'] = $get['user_id'];
        }
        if (isset($get['site_id']) && $get['site_id'] != '') {
            $where['site_id'] = $get['site_id'];
        }
        if (isset($get['status']) && $get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if (isset($get['rebate']) && $get['rebate'] != '') {
            $where['rebate'] = $get['rebate'];
        }
        if (isset($get['euid']) && $get['euid'] != '') {
            $where['euid'] = $get['euid'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'id DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function addOrder($data)
    {
        $id = Db::name('duomai_order')->insertGetId($data);
        return $id;
    }

    public function getOrdeInfo($where)
    {
        $info = Db::name('duomai_order')->where($where)->find();
        return $info;
    }

    public function updateOrderInfo($where, $data)
    {
        $status = Db::name('duomai_order')->where($where)->update($data);
        return $status;
    }

    public function delOrder($where)
    {
        $status = Db::name('duomai_order')->where($where)->delete();
        return $status;
    }

}