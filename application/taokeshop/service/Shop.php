<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/30
 * Time: 14:24
 */
namespace app\taokeshop\service;

use bxkj_module\service\Service;
use think\Db;

class Shop extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('anchor_shop');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $result = [];
        $this->db = Db::name('anchor_shop');
        $this->setWhere($get)->setOrder($get);
        $fields = 'user.nickname';
        $this->db->field('s.*');
        $result = $this->db->field($fields)->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $this->db->alias('s');
        $where = array();
        $where1 = array();
        if ($get['status'] != '') {
            $where['s.status'] = $get['status'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['s.title|user.nickname','like','%'.$get['keyword'].'%'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('__USER__ user', 'user.user_id=s.user_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['s.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function delShop($where)
    {
        $this->db = Db::name('anchor_shop');
        $status = $this->db->where($where)->delete();
        if($status !== false){
            $anchorGoods = new AnchorGoods();
            $anchorGoods->delGoods(["user_id" => $where['user_id']]);
        }else{
            return false;
        }
    }

    public function addShop($data)
    {
        $this->db = Db::name('anchor_shop');
        $id = $this->db->insertGetId($data);
        return $id;
    }
}