<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/19
 * Time: 15:24
 */
namespace app\taokegoods\service;

use bxkj_module\service\Service;
use think\Db;

class Cate extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('goods_cate');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $this->db = Db::name('goods_cate');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['name', 'like', '%'.$get['keyword'].'%'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['cate_id'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function getAllCate($where)
    {
        $this->db = Db::name('goods_cate');
        $result = $this->db->field("cate_id,name,img,desc,dtk_cate_id")->where($where)->select();
        return $result;
    }

    public function add($data)
    {
        $id = Db::name('goods_cate')->insertGetId($data);
        return $id;
    }

    public function update($where, $data)
    {
        $status = Db::name('goods_cate')->where($where)->update($data);
        return $status;
    }

    public function getInfo($where)
    {
        $info = Db::name('goods_cate')->where($where)->find();
        return $info;
    }

    public function delete($where)
    {
        $status = Db::name('goods_cate')->where($where)->delete();
        return $status;
    }
}