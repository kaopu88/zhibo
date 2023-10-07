<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/11
 * Time: 14:40
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Order extends Service
{

    public function getTotal($where)
    {
        $this->db = Db::name('order_log');
        $this->db->where($where);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getOrderList($where=[], $offset=0, $length=20, $sort="o.id desc")
    {
        $this->db = Db::name('order_log');
        $this->setWhere($where);
        $fields = "o.id,o.user_id,o.click_time,o.type,o.goods_order,o.goods_sonorder,o.num,o.order_status,o.goods_id,o.title,o.img,o.shop_type,o.price,o.pay_price,o.commission_rate,
        o.commission,o.position_id,o.earning_time,o.relation_id,o.special_id,o.rebate,o.refund_flg,o.refund_price,o.refund_commission,o.refund_start_time,o.refund_end_time";
        $this->db->field("user.nickname");
        $orderList = $this->db->field($fields)->order($sort)->limit($offset, $length)->select();
        return $orderList;
    }

    public function setWhere($get)
    {
        $this->db->alias('o');
        $where = array();
        $where1 = array();
        if($get['order_status'] != ""){
            $where['o.order_status'] = $get['order_status'];
        }
        if($get['rebate'] != ""){
            $where['o.rebate'] = $get['rebate'];
        }
        if($get['goods_order'] != ""){
            $where['o.goods_order'] = $get['goods_order'];
        }
        if($get['goods_sonorder'] != ""){
            $where['o.goods_sonorder'] = $get['goods_sonorder'];
        }
        if($get['keyword'] != ""){
            $where1[] = ['o.title','like','%'.$get['keyword'].'%'];
        }
        if($get['time_type'] != ""){
            if($get['time_type'] == "create"){
                $where[] = ['o.create_time', 'between', $get['start_time'].",".$get['end_time']];
            }else if ($get['time_type'] == "earning"){
                $where[] = ['o.earning_time', 'between', $get['start_time'].",".$get['end_time']];
            }
        }
        if($get['type'] != ""){
            $where['o.type'] = $get['type'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('__USER__ user', 'user.user_id=o.user_id', 'LEFT');
        return $this;
    }

    public function getOrderInfo($where)
    {
        $this->db = Db::name('order_log');
        $orderInfo = $this->db->where($where)->find();
        return $orderInfo;
    }

    public function addOrder($data)
    {
        $this->db = Db::name('order_log');
        $id = $this->db->insertGetId($data);
        return $id;
    }

    public function updateOrderInfo($where, $data)
    {
        $this->db = Db::name('order_log');
        $status = $this->db->where($where)->update($data);
        return $status;
    }

    public function getOrderCount($where)
    {
        $this->db = Db::name('order_log');
        $num = $this->db->where($where)->count();
        return $num;
    }

    public function sumOrderPrice($where, $field="price")
    {
        $this->db = Db::name('order_log');
        $num = $this->db->where($where)->sum($field);
        return $num;
    }

    public function delOrder($where)
    {
        $this->db = Db::name('order_log');
        $status = $this->db->where($where)->delete();
        return $status;
    }

    public function getAllOrder($where)
    {
        $this->db = Db::name('order_log');
        $fields = "o.id,o.user_id,o.click_time,o.type,o.goods_order,o.goods_sonorder,o.num,o.order_status,o.goods_id,o.title,o.img,o.shop_type,o.price,o.pay_price,o.commission_rate,
        o.commission,o.position_id,o.earning_time,o.relation_id,o.special_id,o.rebate,o.refund_flg,o.refund_price,o.refund_commission,o.refund_start_time,o.refund_end_time";
        $orderList = $this->db->field($fields)->where($where)->select();
        return $orderList;
    }

}