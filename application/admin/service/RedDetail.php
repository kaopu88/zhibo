<?php
namespace app\admin\service;
use bxkj_module\service\Service;
use think\Db;

class RedDetail extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('activity_red_detail');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('activity_red_detail');
        $this->setJoin()->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }

    private function setJoin()
    {
        $this->db->alias('ard')->join('__USER__ user', 'user.user_id=ard.user_id', 'LEFT');
        $this->db->field('ard.*,user.nickname,user.avatar,user.phone');
        return $this;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['id'] != '') {
            $where[] = ['id', '=', $get['id']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'user_name,user_id');
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