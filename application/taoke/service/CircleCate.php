<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/28
 * Time: 9:00
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class CircleCate extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('circle_cate');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('circle_cate');
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
        if (isset($get['status'])) {
            $where['status'] = $get['status'];
        }
        if (isset($get['pid'])) {
            $where['pid'] = $get['pid'];
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

    public function add($data)
    {
        $id = Db::name('circle_cate')->insertGetId($data);
        return $id;
    }

    public function getInfo($where)
    {
        $info = Db::name('circle_cate')->where($where)->find();
        return $info;
    }

    public function updateInfo($where, $data)
    {
        $status = Db::name('circle_cate')->where($where)->update($data);
        return $status;
    }

    public function delete($where)
    {
        $status = Db::name('circle_cate')->where($where)->delete();
        return $status;
    }
}