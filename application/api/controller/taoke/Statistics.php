<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/14
 * Time: 18:07
 */
namespace app\api\controller\taoke;

use app\common\controller\UserController;
use app\common\service\UserRank;
use app\taoke\service\CommissionLog;
use app\taoke\service\Estimate;
use app\taoke\service\Order;

class Statistics extends UserController
{
    /*
     * 获取收益统计
     */
    public function getProfits()
    {
        $data = [];
        $userId = USERID;
        $params = request()->param();
        $time = $params['time'];
        if(empty($time)){
            return $this->jsonError("参数错误");
        }
        $orderType = !empty($params['order_type']) ? $params['order_type'] : "";

        $data['self'] = $this->getUserStatis($userId, $time, $orderType);
        $data['team'] = $this->getFansStatis($userId, $time, $orderType);

        $where['user_id'] = $userId;
        $commissionLog = new CommissionLog();
        $data['rebate'] = $commissionLog->getStatistics($where, "rebate_money");//结算收入累加

        $settlePrice = $commissionLog->getStatistics($where, "settlement_price");//累计结算收入
        $data['unrebate'] = $data['rebate'] - $settlePrice;//未结算收入

        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 用户自购收益预估
     * @param $userId
     * @param string $time  时间类型 today：今日；yest:昨日；month：本月；upMonth：上月
     * @param string $type  0：淘宝；1：拼多多；2：京东
     * @return array
     */
    public function getUserStatis($userId, $time="today", $type="")
    {
        $result = [];
        if($time == "today"){
            $startTime = strtotime(date("Y-m-d", time()));
            $endTime = $startTime + 86399;
        }elseif ($time == "yest"){
            $endTime = strtotime(date("Y-m-d", time())) - 1;
            $startTime = $endTime - 86399;
        }elseif ($time == "month"){
            $startTime = strtotime(date("Y-m-01"));
            $endTime = strtotime("+1 month -1 seconds", $startTime);
        }elseif ($time == "upMonth"){
            $startTime = mktime(0,0,0,date('m'),1,date('Y'));
            $endTime = mktime(23,59,59,date('m'),date('t'),date('Y'));
        }
        $order= new Order();
        $where["user_id"] = $userId;
        $where[] = ["order_status", "neq", 13];
        $where[] = ["create_time", "between", $startTime.",".$endTime];
        if(!empty($type)){
            $where['type'] = $type;
        }
        $num = $order->getOrderCount($where);

        $money = 0;
        $orderList = $order->getAllOrder($where);
        if ($orderList) {
            $estimate = new Estimate();
            foreach ($orderList as $okey => $ovalue) {
                $money += $estimate->getEstimatedProfit($ovalue, $userId);
            }
        }

        $result['order_count'] = $num;
        $result['money'] = $money;
        return $result;
    }

    /**
     * 获取下级粉丝的预估收益
     * @param $userId
     * @param string $time  时间类型 today：今日；yest:昨日；month：本月；upMonth：上月
     * @param string $type  订单类型 0：淘宝；1：拼多多；2：京东
     * @param int $level    粉丝层级
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFansStatis($userId, $time="today", $type="", $level=0)
    {
        $result = [];
        $userRank = new UserRank();
        $fansList = $userRank->getAllFans($userId, $level);
        if($fansList){
            $ids = "";
            foreach ($fansList as $key => $value){
                $ids .= $value['uid'].",";
            }
            $ids = trim($ids, ",");
            $order = new Order();
            if(!empty($type)){
                $where['type'] = $type;
            }
            $where[] = ["order_status", "neq", 13];
            $where[] = ["user_id", "in", $ids];
            if($time == "today"){
                $startTime = strtotime(date("Y-m-d", time()));
                $endTime = $startTime + 86399;
            }elseif ($time == "yest"){
                $endTime = strtotime(date("Y-m-d", time())) - 1;
                $startTime = $endTime - 86399;
            }
            $orderList = [];
            $money = 0;
            $total = $order->getTotal($where);

            if($time == "today" || $time == "yest") {
                $orderList = $order->getAllOrder($where);
                if ($orderList) {
                    $estimate = new Estimate();
                    foreach ($orderList as $okey => $ovalue) {
                        $money += $estimate->getEstimatedProfit($ovalue, $userId);
                    }
                }
            }else{
                if ($time == "month"){
                    $startTime = strtotime(date("Y-m-01"));
                    $endTime = strtotime("+1 month -1 seconds", $startTime);
                }elseif ($time == "upMonth"){
                    $startTime = mktime(0, 0, 0, date('m'), 1, date('Y'));
                    $endTime = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
                }
                $map[] = ["create_time", "between", $startTime.",".$endTime];
                $map[] = ["user_id", "in", $ids];
                $commissionLog = new CommissionLog();
                $money += $commissionLog->getStatistics($map, "predict_money");
            }
        }
        $result['order_count'] = $total;
        $result['money'] = $money;
        return $result;
    }

}