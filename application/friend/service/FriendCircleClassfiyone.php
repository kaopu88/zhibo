<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/04
 * Time: 下午 4:46
 */

namespace app\friend\service;

use bxkj_module\service\Service;
use think\Db;

class FriendCircleClassfiyone extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_classfiyone');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_classfiyone');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['keyword'] != '') {
            $where1[] = ['class_name', 'like', '%' . $get['keyword'] . '%'];
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
            'class_name'  => $databa['class_name'],
            'status'      => 1,
            'create_time' => time(),
        ];
        $id   = Db::name('friend_circle_classfiyone')->insertGetId($data);
        return $id;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_classfiyone')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function info($id)
    {
        return Db::name('friend_circle_classfiyone')->where(['id' => $id])->find();
    }

    public function backstageedit($databa)
    {
        $data = [
            'class_name'  => $databa['class_name'],
            'create_time' => time(),
        ];
        return Db::name('friend_circle_classfiyone')->where(array('id' => $databa['id']))->update($data);
    }

    public function del($ids)
    {
        return Db::name('friend_circle_classfiyone')->whereIn('id', $ids)->update(['isdel' => 1]);
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_classfiyone');
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
        $this->db = Db::name('friend_circle_classfiyone');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function classfiyCheck($databa)
    {
        $ids = Db::name('friend_circle_classfiyone')->where(['class_name' => $databa['class_name']])->find();
        if ($ids) {
            return ['code' => -1, 'msg' => '数据库分类名称重复'];
        }
        return ['code' => 1, 'msg' => '没有重复'];
    }
}