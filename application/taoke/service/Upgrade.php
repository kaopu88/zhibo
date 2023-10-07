<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/3
 * Time: 16:07
 */
namespace app\taoke\service;

use app\common\service\UserRank;
use bxkj_module\service\Service;

class Upgrade extends Service
{
    /**
     * 获取用户的订单条件完成进度
     * @param $userId
     * @param int $level
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderProcess($userId, $level=1)
    {
        $data = [];
        $order = new Order();
        $orderNumSelf = $order->getOrderCount(["user_id" => $userId]);//自购订单数
        $orderPriceSelf = $order->sumOrderPrice(["user_id" => $userId], "pay_price");//获取用户自购订单付款金额

        $orderNumFans = [];
        $orderPriceFans = [];
        $userRank = new UserRank();
        $fansList = $userRank->getAllFans($userId);//获取下级所有粉丝
        $allFansIds = "";
        foreach ($fansList as $k => $v) {
            $allFansIds .= $v['uid'];
        }
        $allFansIds = trim($allFansIds, ",");
        $allMap[] = ['user_id', 'in', $allFansIds];
        $orderNumTeam = $order->getOrderCount($allMap);
        $orderPriceTeam = $order->sumOrderPrice($allMap, "pay_price");

        for ($i=0; $i<$level; $i++) {
            $fansNum = 0;
            $fansPrice = 0;
            $fansLevelList = $userRank->getAllFans($userId, $i+1);//获取下级对应层级粉丝
            if ($fansLevelList) {
                $fansIds = "";
                foreach ($fansLevelList as $key => $value) {
                    $fansIds .= $value['uid'];
                }
                $fansIds = trim($fansIds, ",");

                $map = [];
                $map[] = ['user_id', 'in', $fansIds];
                $fansNum = $order->getOrderCount($map);
                $fansPrice = $order->sumOrderPrice($map, "pay_price");
            }
            $orderNumFans[$i] = $fansNum;
            $orderPriceFans[$i] = $fansPrice;
        }
        $data['order_num_self'] = $orderNumSelf;//自购订单数
        $data['order_num_team'] = $orderNumTeam;//团队订单数
        $data['order_price_self'] = $orderPriceSelf;//自购订单付款金额
        $data['order_price_team'] = $orderPriceTeam;//团队订单付款金额
        $data['order_num_fans'] = $orderNumFans;//下级粉丝订单数
        $data['order_price_fans'] = $orderPriceFans;//下级粉丝订单付款金额
        return $data;
    }

    /**
     * @param $userId
     * @param int $level
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCommissionProgress($userId, $level=1)
    {
        $data = [];
        $commissionLog = new CommissionLog();
        $where['user_id'] = $userId;
        $where['type'] = 0;
        $selfCommission = $commissionLog->getStatistics($where, "settlement_price");//自购佣金
        $teamCommission = $commissionLog->getStatistics($where, "team_income");//团队佣金

        $levelFansOrderCommission = [];
        $userRank = new UserRank();
        for ($i=0; $i<$level; $i++){
            $levelFansList = $userRank->getAllFans($userId, $i+1);
            $fansCommission = 0;
            if($levelFansList){
                $fansIds = "";
                foreach ($levelFansList as $fk => $fv){
                    $fansIds .= $fv['uid'].",";
                }
                $fansIds = trim($fansIds, ",");
                $map = [];
                $map[] = ['user_id', 'in', $fansIds];
                $fansCommission = $commissionLog->getStatistics($map, "rebate_money");
            }
            $levelFansOrderCommission[$i] = $fansCommission;
        }
        $data['commission_self'] = $selfCommission;//自购订单佣金
        $data['commission_team'] = $teamCommission;//团队订单佣金
        $data['commission_fans'] = $levelFansOrderCommission;//下级粉丝订单佣金
        return $data;
    }

    /**
     * 获取用户下级人数完成进度
     * @param $userId
     * @param int $level
     * @return array
     */
    public function getPeopleProgress($userId, $level=1)
    {
        $data = [];
        $userRank = new UserRank();
        $teamNum = $userRank->getCountFans($userId);//获取团队人数

        $levelFans = [];
        for ($i=0; $i<$level; $i++){
            $levelFans[$i] = $userRank->getCountFans($userId, $i+1);//获取对应层级粉丝数
        }
        $data['fans_team_num'] = $teamNum;//团队人数
        $data['fans_level_num'] = $levelFans;//下级粉丝人数
        return $data;
    }
}