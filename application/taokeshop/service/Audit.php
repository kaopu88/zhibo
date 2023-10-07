<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/24
 * Time: 16:23
 */
namespace app\taokeshop\service;

use bxkj_module\service\Service;
use think\Db;

class Audit extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('user_taoke_audit');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $result = [];
        $this->db = Db::name('user_taoke_audit');
        $this->setWhere($get)->setOrder($get);
        $fields = 'uv.name,uv.card_num,uv.front_idcard,uv.back_idcard,uv.hand_idcard';
        $this->db->field('uta.*');
        $result = $this->db->field($fields)->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $this->db->alias('uta');
        $where = array();
        $where1 = array();
        if ($get['status'] != '') {
            $where['uta.status'] = $get['status'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['uta.user_id|uv.name','like','%'.$get['keyword'].'%'];
        }
        $where['uv.status'] = 1;
        $this->db->where($where)->where($where1);
        $this->db->join('user_verified uv', 'uv.user_id=uta.user_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['uta.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function addAudit($data)
    {
        $data['create_time'] = time();
        $id = Db::name("user_taoke_audit")->insertGetId($data);
        return $id;
    }

    public function getInfo($where)
    {
        $auditInfo = Db::name("user_taoke_audit")->where($where)->find();
        return $auditInfo;
    }

    public function updateAudit($where, $data)
    {
        $status = Db::name("user_taoke_audit")->where($where)->update($data);
        return $status;
    }
}