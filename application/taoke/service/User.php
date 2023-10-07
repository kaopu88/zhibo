<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/16
 * Time: 17:30
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class User extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('user');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('user');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('u.user_id,u.nickname,u.taoke_money,u.taoke_money_status,u.taoke_level,u.relation_id,u.special_id,u.pdd_pid,u.jd_pid,u.invite_code,tl.name');
        $list = $this->db->limit($offset, $length)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $this->db->alias('u');
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['u.nickname|u.relation_id|u.special_id|u.pdd_pid|u.jd_pid','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['user_id']) && $get['user_id'] != '') {
            $where['u.user_id'] = $get['user_id'];
        }
        if (isset($get['taoke_level']) && $get['taoke_level'] != '') {
            $where['u.taoke_level'] = $get['taoke_level'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('taoke_level tl', 'u.taoke_level=tl.id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'u.user_id DESC';
        }
        $this->db->order($order);
        return $this;
    }

}