<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/28
 * Time: 10:35
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Circle extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('circle');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('circle');
        $this->setWhere($get)->setOrder($get);
        $list = $this->db->limit($offset, $length)->select();
        if($list){
            $circleCate = new CircleCate();
            foreach ($list as $key => $value){
                $cateInfo = $circleCate->getInfo(["id"=>$value['cid']]);
                $list[$key]['ctype'] = $cateInfo['type'];
                $list[$key]['goods_info'] = empty($value['goods_info']) ? [] : json_decode($value['goods_info'], true);
            }
        }
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
        if (isset($get['cid']) && $get['cid'] != '') {
            $where['cid'] = $get['cid'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'id DESC,sort DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $data['add_time'] = time();
        $id = Db::name('circle')->insertGetId($data);
        return $id;
    }

    public function getInfo($where)
    {
        $info = Db::name('circle')->where($where)->find();
        return $info;
    }

    public function updateInfo($where, $data)
    {
        $status = Db::name('circle')->where($where)->update($data);
        return $status;
    }

    public function delete($where)
    {
        $status = Db::name('circle')->where($where)->delete();
        return $status;
    }
}