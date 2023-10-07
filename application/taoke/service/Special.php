<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/14
 * Time: 15:47
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Special extends Service
{
    public function getTotal($where)
    {
        $this->db = Db::name('taoke_special');
        $this->db->where($where);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($where=[], $offset=0, $length=20, $sort="id desc")
    {
        $this->db = Db::name('taoke_special');
        $this->setWhere($where);
        $orderList = $this->db->order($sort)->limit($offset, $length)->select();
        return $orderList;
    }

    public function setWhere($get)
    {
        $where = array();
        if($get['status'] != ""){
            $where['status'] = $get['status'];
        }
        if($get['sid'] != ""){
            $where['sid'] = $get['sid'];
        }
        if($get['banner_status'] != ""){
            $where['banner_status'] = $get['banner_status'];
        }
        $where['is_show'] = 1;
        if($get['type'] != ""){
            $where['type'] = $get['type'];
        }
        $this->db->where($where);
        return $this;
    }

    public function getInfo($where)
    {
        $info = Db::name('taoke_special')->where($where)->find();
        return $info;
    }

    public function updateInfo($where, $data)
    {
        $status = Db::name('taoke_special')->where($where)->update($data);
        return $status;
    }


}