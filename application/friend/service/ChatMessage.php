<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time: 上午 10:18
 */

namespace app\friend\service;

use bxkj_module\service\Service;
use think\Db;

class ChatMessage extends Service
{
    public function add($datain)
    {
        $data['ctime']         = time();
        $data['messages'] = $datain['messages'];
        $data['from_uid'] = $datain['from_uid'];
        $data['to_uid'] = $datain['to_uid'];
        $data['messages_type'] = $datain['messages_type'] ? $datain['messages_type'] : 1;
        $data['imgs'] = '';
        $data['video'] = '';
        $id                    = Db::name('chat_message')->insertGetId($data);
        return $id;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('chat_message');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order)
    {
        $this->db = Db::name('chat_message');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('chat_message');
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

    public function getTotal($get)
    {
        $this->db = Db::name('chat_message');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('chat_message');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['uid'] != 0) {
            $where['uid'] = $get['uid'];
        }
        if ($get['fcmid'] != '') {
            $where['fcmid'] = $get['fcmid'];
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

    public function del($ids)
    {
    }

    public function seeMsg($page_index, $page_size, $condition, $order, $field)
    {
        $where[] = ['from_uid|to_uid', 'in', $condition];
        $rest1   = $this->pageQuery($page_index, $page_size, $where, $order, $field);
        foreach ($rest1['data'] as $k => $v) {
            Db::name('chat_message')->where(['id' => $v['id']])->update(['status' => 1]);
        }
        return $rest1;
    }
}