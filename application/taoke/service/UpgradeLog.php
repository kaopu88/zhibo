<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/8
 * Time: 09:39
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class UpgradeLog extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('upgrade_log');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $this->db = Db::name('upgrade_log');
        $this->setWhere($get)->setOrder($get);
        $field = "tl.name as level_name,u.username,ul.upgrade_condition,ul.status,ul.id,ul.add_time,ul.update_time";
        $result = $this->db->field($field)->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $this->db->alias("ul");
        $where = array();
        if ($get['status'] != '') {
            $where['ul.status'] = $get['status'];
        }
        if ($get['type'] != '') {
            $where['ul.type'] = $get['type'];
        }
        $this->db->join("taoke_level tl","ul.level=tl.level", "left");
        $this->db->join("user u","ul.user_id=u.user_id", "left");
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['ul.add_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function getInfo($where)
    {
        $this->db = Db::name('upgrade_log');
        $result = $this->db->where($where)->find();
        return $result;
    }
}