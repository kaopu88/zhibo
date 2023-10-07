<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/12
 * Time: 10:55
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Estimate extends Service
{
    public function addLog($data)
    {
        $this->db = Db::name("estimate_commission_log");
        $id = $this->db->insertGetId($data);
        return $id;
    }

    public function getLog($where)
    {
        $this->db = Db::name("estimate_commission_log");
        $info = $this->db->where($where)->find();
        return $info;
    }

    /**
     * 获取订单预估佣金
     * @param $order
     * @param $userId
     * @return int
     */
    public function getEstimatedProfit($order, $userId)
    {
        $estimateMoney = 0;
        $order_info = [];
        if (isset($order['goods_sonorder']) && $order['goods_sonorder'] && $order['goods_sonorder'] != '0') {
            $order_info = $this->getLog(['goods_sonorder' => $order['goods_sonorder']]);
        }
        if (empty($order_info)) {
            $order_info = $this->getLog(['goods_order' => $order['goods_order']]);
        }
        if ($order_info) {
            $value = json_decode($order_info['value'], true);
            for ($i = 0; $i < count($value); $i++) {
                if ($value[$i]['user_id'] == $userId) {
                    $estimateMoney = $value[$i]['money'];
                }
            }
        }
        return $estimateMoney;
    }

}