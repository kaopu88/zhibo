<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/22
 * Time: 下午 2:10
 */

namespace app\friend\service;

use bxkj_module\service\Service;
use think\Db;

class FriendCircleMessageExpress extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_message_express');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_message_express');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['classid'] != 0) {
            $where['classid'] = $get['classid'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['content', 'like', '%' . $get['keyword'] . '%'];
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

    public function backstageadd($databa)
    {
        $data = [
            'classid'     => $databa['classid'],
            'content'     => $databa['content'] ? $databa['content'] : '',
            'from'        => $databa['from'] ? $databa['from'] : 0,
            'status'      => $databa['status'] ? $databa['status'] : 1,
            'create_time' => time(),
        ];
        $id   = Db::name('friend_circle_message_express')->insertGetId($data);
        return $id;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_message_express')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function info($id)
    {
        return Db::name('friend_circle_message_express')->where(['id' => $id])->find();
    }

    public function backstageedit($databa)
    {
        $data = [
            'classid'     => $databa['classid'],
            'content'     => $databa['content'] ? $databa['content'] : '',
            'from'        => $databa['from'] ? $databa['from'] : 0,
            'status'      => $databa['status'] ? $databa['status'] : 1,
            'create_time' => time(),
        ];
        return Db::name('friend_circle_message_express')->where(array('id' => $databa['id']))->update($data);
    }

    public function del($ids)
    {
        return Db::name('friend_circle_message_express')->whereIn('id', $ids)->delete();
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_message_express');
        $count    = $this->db->where($condition)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
                ->where($condition)
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

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_message_express');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }
}