<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/11
 * Time: 14:40
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class CommissionLog extends Service
{

    public function getTotal($where)
    {
        $this->db = Db::name('commission_log');
        $count = $this->db->where($where)->count();
        return (int)$count;
    }

    public function getList($where=[], $sort="add_time desc", $offset=0, $length=20)
    {
        $this->db = Db::name('commission_log');
        $fields = "c.user_id,c.create_time,c.predict_money,c.settlement_price,c.team_income,c.level_income,c.rebate_money,c.type,c.settlement_pay,c.team_pay,c.level_pay";
        $this->db->field("user.nickname");
        $orderList = $this->db->field($fields)->alias("c")->join('__USER__ user', 'user.user_id=c.user_id', 'LEFT')->where($where)->order($sort)->limit($offset, $length)->select();
        return $orderList;
    }

    public function addLog($data)
    {
        $this->db = Db::name('commission_log');
        $id = $this->db->insertGetId($data);
        return $id;
    }

    public function getLogInfo($where)
    {
        $this->db = Db::name('commission_log');
        $orderInfo = $this->db->where($where)->find();
        return $orderInfo;
    }

    public function updateLogInfo($where, $data)
    {
        $this->db = Db::name('commission_log');
        $status = $this->db->where($where)->update($data);
        return $status;
    }

    public function delLog($where)
    {
        $this->db = Db::name('commission_log');
        $status = $this->db->where($where)->delete();
        return $status;
    }

    public function getStatistics($where, $field="predict_money")
    {
        $this->db = Db::name('commission_log');
        $num = $this->db->where($where)->sum($field);
        return $num;
    }

}