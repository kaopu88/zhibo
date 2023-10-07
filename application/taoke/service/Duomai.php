<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/3
 * Time: 11:53
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Duomai extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('duomai_ads');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('duomai_ads');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('id,ads_id,ads_name,cate_id,cate_name,ads_endtime,ads_commission,site_url,site_logo,site_description,adser,charge_period,status,is_top');
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
        if (isset($get['cate_id']) && $get['cate_id'] != '') {
            $where['cate_id'] = $get['cate_id'];
        }
        if (isset($get['is_top']) && $get['is_top'] != '') {
            $where['is_top'] = $get['is_top'];
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
            $order = 'ads_id ASC';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $id = Db::name('duomai_ads')->insertGetId($data);
        return $id;
    }

    public function getInfo($where)
    {
        $info = Db::name('duomai_ads')->where($where)->find();
        return $info;
    }

    public function updateInfo($where, $data)
    {
        $status = Db::name('duomai_ads')->where($where)->update($data);
        return $status;
    }

    public function del($where)
    {
        $status = Db::name('duomai_ads')->where($where)->delete();
        return $status;
    }
}