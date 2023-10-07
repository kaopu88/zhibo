<?php
namespace app\livepush\service;

use bxkj_module\service\Service;
use think\Db;

class Push extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_message_push');
        $this->setWhere($get);
        return $this->db->count();
    }


    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_message_push');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }



    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $this->db->setKeywords(trim($get['keyword']), '', 'number user_id', 'push_object,user_id');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }
}