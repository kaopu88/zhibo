<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/15
 * Time: 下午 5:51
 */

namespace app\friend\service;

use bxkj_module\service\Service;
use think\Db;

class FriendCircleTimelin extends Service
{
    public function add($data)
    {
        $id = Db::name('friend_circle_timeline')->insertGetId($data);
        return $id;
    }

    public function insertAll($data)
    {
        $result = Db::name('friend_circle_timeline')->insertAll($data);
        return $result;
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_timeline');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_timeline');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['type'] != 0) {
            $where['type'] = $get['type'];
        }
        if ($get['msg_type'] != '') {
            $where['msg_type'] = $get['msg_type'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['title|content', 'like', '%' . $get['keyword'] . '%'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        } else {
            $order = $get;
        }
        $this->db->order($order);
        return $this;
    }

    public function pageQuery($page_index, $page_size, $condition, $condition1, $order, $field)
    {
        $this->db = Db::name('friend_circle_timeline');
        $count    = $this->db->where($condition)->whereOr($condition1)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->whereOr($condition1)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
                ->where($condition)
                ->whereOr($condition1)
                ->order($order)
                ->limit($start_row . "," . $page_size)
                ->select();
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int)($count / $page_size) + 1;
            }
        }
        return array(
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }

    public function getQueryOr($condition, $condition1, $field, $order)
    {
        //时间过滤，暂留
        //    $now  =  date("Y-m-d ",time()+24*60*60);
        //   $lastMonthDatys = getlastMonthDays($now)[0];
        //    $list     = $this->db->field($field)->where($condition)->whereOr($condition1)->whereTime('create_time','between',[$lastMonthDatys,$now])->order($order)->select();
        $this->db = Db::name('friend_circle_timeline');
        $list = $this->db->field($field)->where($condition)->whereOr($condition1)->order($order)->select();
        return $list;
    }

    public function getQuery($condition, $field, $order)
    {
        $now            = date("Y-m-d ", time() + 24 * 60 * 60);
        $lastMonthDatys = getlastMonthDays($now)[0];
        $this->db       = Db::name('friend_circle_timeline');
        $list           = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function column($condition, $field)
    {
        $this->db = Db::name('friend_circle_timeline');
        $list     = $this->db->where($condition)->column($field);
        return $list;
    }
}