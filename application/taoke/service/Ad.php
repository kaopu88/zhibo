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

class Ad extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('taoke_ads');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('taoke_ads');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('ta.ad_id,ta.title,ta.desc,ta.image,ta.position_id,ta.sort,ta.status,ta.add_time,tap.name,tap.id');
        $list = $this->db->limit($offset, $length)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $this->db->alias('ta');
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['ta.title','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['position_id']) && $get['position_id'] != '') {
            $where['ta.position_id'] = $get['position_id'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('__TAOKE_ADS_POSITION__ tap', 'tap.id=ta.position_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'ta.ad_id DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $data['add_time'] = time();
        $id = Db::name('taoke_ads')->insertGetId($data);
        return $id;
    }

    public function update($where, $data)
    {
        $status = Db::name('taoke_ads')->where($where)->update($data);
        return $status;
    }

    public function getInfo($where)
    {
        $info = Db::name('taoke_ads')->where($where)->find();
        if($info){
            $info['bg_color'] = json_decode($info['bg_color'], true);
        }
        return $info;
    }

    public function delete($where)
    {
        $status = Db::name('taoke_ads')->where($where)->delete();
        return $status;
    }
}