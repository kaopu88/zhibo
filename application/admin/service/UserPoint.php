<?php


namespace app\admin\service;


use bxkj_module\service\Service;
use think\Db;

class UserPoint extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('user_point_log');
        $this->db->alias('t')
            ->join('user u', 'u.user_id=t.user_id');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('user_point_log');

        $this->db->alias('t')
            ->join('user u', 'u.user_id=t.user_id')
            ->field('t.*,u.nickname, u.phone, u.avatar');

        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            switch ($item['status']){
                case 1:
                    $item['status_txt'] = '待领取';
                    break;
                case 2:
                    $item['status_txt'] = '已完成';
                    break;
                default:
                    $item['status_txt'] = '未完成';
                    break;
            }
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        $this->db->setKeywords(trim($get['keyword']), 'phone u.phone', 'number t.user_id', 'number u.phone,u.nickname');

        if ($get['start_time'] != '' &&  $get['end_time'] != '') {
            $this->db->whereTime('t.create_time', 'between', [$get['start_time'] . ' 0:0:0', $get['end_time'] . ' 23:59:59']);
        }

        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }
}