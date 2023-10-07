<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/23
 * Time: 16:12
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class AdPosition extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('taoke_ads_position');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('taoke_ads_position');
        $this->setWhere($get)->setOrder($get);
        $list = $this->db->limit($offset, $length)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['name','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['type']) && $get['type'] != '') {
            $where['type'] = $get['type'];
        }
        if (isset($get['status']) && $get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'add_time DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $data['add_time'] = time();
        $id = Db::name('taoke_ads_position')->insertGetId($data);
        return $id;
    }

    public function update($where, $data)
    {
        $status = Db::name('taoke_ads_position')->where($where)->update($data);
        return $status;
    }

    public function getInfo($where)
    {
        $info = Db::name('taoke_ads_position')->where($where)->find();
        return $info;
    }

}