<?php

namespace app\admin\service;


use bxkj_module\service\Service;
use think\Db;

class Activity extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('activity');
        $this->setWhere($get);
        return $this->db->count();
    }


    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('activity');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }



    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['type'] != '') {
            $where[] = ['type', '=', $get['type']];
        }

        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');

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


    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择活动');
        $num = Db::name('activity')->whereIn('id', $ids)->delete();
        return $num;
    }


}