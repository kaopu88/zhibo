<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/27
 * Time: 10:25
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class BussinessCate extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('bussiness_cate');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('bussiness_cate');
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
            $order = 'id DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $data['add_time'] = time();
        $id = Db::name('bussiness_cate')->insertGetId($data);
        return $id;
    }

    public function getInfo($where)
    {
        $info = Db::name('bussiness_cate')->where($where)->find();
        return $info;
    }

    public function updateInfo($where, $data)
    {
        $status = Db::name('bussiness_cate')->where($where)->update($data);
        return $status;
    }

    public function delete($where)
    {
        $status = Db::name('bussiness_cate')->where($where)->delete();
        return $status;
    }
}