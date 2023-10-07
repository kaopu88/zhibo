<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserPackage extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('user_package');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('user_package');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            switch ($item['status']){
                case 1:
                    $item['status_txt'] = '有效';
                    break;
                case 2:
                    $item['status_txt'] = '已使用';
                    break;
                default:
                    $item['status_txt'] = '失效';
                    break;
            }
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if ($get['type'] != '')
        {
            $where['type'] = $get['type'];
        }
        if ($get['status'] != '')
        {
            $where['status'] = $get['status'];
        }
        if ($get['user_id'] != '')
        {
            $where['user_id'] = $get['user_id'];
        }

        $this->db->setKeywords(trim($get['keyword']), 'number gift_id', '', 'number gift_id,name');

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
