<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/13
 * Time: 10:33
 */
namespace app\taoke\service;

use app\admin\service\User;
use app\taoke\controller\Config;
use bxkj_module\service\Service;

class JdOrder extends Service
{
    public function formatOrder($orderList)
    {
        $data = [];
        $orderList = $this->devideOrder($orderList);
        foreach($orderList as $value){
            if($value['cosPrice'] == 0){
                continue;
            }
            $goodsOrder['create_time'] = $value['orderTime']; //订单创建时间
            $goodsOrder['click_time'] = $value['orderTime']; //订单创建时间
            $goodsOrder['type'] = 2; //订单类型 2京东
            $goodsOrder['shop_type'] = '京东'; //订单类型 2京东
            $goodsOrder['goods_order'] = $value['orderId']; //订单编号
            $goodsOrder['goods_sonorder'] = '0'; //订单编号
            $goodsOrder['num'] = $value['skuNum']; //订单购买数量
            switch ($value['validCode']) {
                case '-1':
                    $order_status = 13;
                    break;
                case '2':
                    $order_status = 13;
                    break;
                case '3':
                    $order_status = 13;
                    break;
                case '4':
                    $order_status = 13;
                    break;
                case '5':
                    $order_status = 13;
                    break;
                case '6':
                    $order_status = 13;
                    break;
                case '7':
                    $order_status = 13;
                    break;
                case '8':
                    $order_status = 13;
                    break;
                case '9':
                    $order_status = 13;
                    break;
                case '10':
                    $order_status = 13;
                    break;
                case '11':
                    $order_status = 13;
                    break;
                case '12':
                    $order_status  = 13;
                    break;
                case '13':
                    $order_status = 13;
                    break;
                case '14':
                    $order_status = 13;
                    break;
                case '15':
                    $order_status = 13;
                    break;
                case '16':
                    $order_status = 12;
                    break;
                case '17':
                    $order_status = 3;
                    break;
                case '18':
                    $order_status = 3;
                    break;
                default:
                    $order_status = 13;
                    break;
            }
            $goodsOrder['order_status'] = $order_status; //订单状态
            $goodsOrder['title'] = $value['skuName']; //订单商品名称
            $goodsOrder['goods_id'] = $value['skuId']; //订单商品id
            $goodsOrder['price'] = $value['price'] * $value['skuNum']; //订单原价
            $goodsOrder['pay_price'] = $value['cosPrice']; //订单付款金额
            $goodsOrder['settle_price'] = isset($value['Fee']) ? $value['Fee'] : '0'; //订单结算金额
            $goodsOrder['commission'] = $value['commission']; //订单佣金金额
            $goodsOrder['commission_rate'] = $value['commissionRate'] .' %'; //订单佣金比例  千分比 /10为百分比
            $goodsOrder['position_id'] =  $value['positionId']; //订单pid
            $goodsOrder['earning_time'] =  $value['finishTime'] != NULL ? $value['finishTime'] : 0;  //订单结算时间
            if($value['positionId']){
                $user = new User();
                $userInfo = $user->getUserInfo(["jd_pid" => $value['positionId']]);
                if($userInfo){
                    $goodsOrder['user_id'] = $userInfo['id'];
                    $goodsOrder['position_name'] = $userInfo['nickname'];
                }else{
                    $goodsOrder['user_id'] = 0;
                }
            }
            $data[] = $goodsOrder;
        }
        return $data;
    }

    /**
     * 多个商品订单拆分成单个订单
     * @param $order_list
     * @return array
     */
    public function devideOrder($orderList)
    {
        $list = [];
        foreach($orderList['skuList'] as $key => $value){
            $list[$key]['orderId'] = $orderList['orderId'];
            $list[$key]['skuNum'] = $value['skuNum']; //订单购买数量
            $list[$key]['validCode'] = $value['validCode'];
            $list[$key]['skuName'] = $value['skuName']; //订单商品名称
            $list[$key]['skuId'] = $value['skuId']; //订单商品id
            $list[$key]['price'] = $value['price']; //订单原价
            $list[$key]['cosPrice'] = empty($value['estimateCosPrice']) ? 0 : $value['estimateCosPrice']; //订单付款金额
            $list[$key]['Fee'] = $value['actualCosPrice']; //订单结算金额
            $list[$key]['commission'] = empty($value['estimateFee']) ? 0 : $value['estimateFee']; //订单佣金金额
            $list[$key]['commissionRate'] = $value['commissionRate']; //订单佣金比例  千分比 /10为百分比
            $list[$key]['orderTime'] = $orderList['orderTime'] / 1000; //订单创建时间
            $list[$key]['positionId'] = $value['positionId'];
            $list[$key]['finishTime'] = $orderList['finishTime'] / 1000;  //订单结算时间
        }
        return $list;
    }

    /**
     * 京东订单处理
     * @param $data
     * @param int $rebate
     */
    public function addOrder($data, $rebate=0)
    {
        if (!empty($data)) {
            $orderList = $data['order_list'];
            if ($orderList) {
                $orderList = $this->formatOrder($orderList);
                $order = new Order();
                $commission = new Commission();
                foreach ($orderList as $key => $value) {
                    $where["goods_order"] = $value['goods_order'];
                    $where["goods_sonorder"] = $value['goods_sonorder'];
                    $orderInfo = $order->getOrderInfo($where);
//                    $config = new Config();
//                    $zero = $config->getConfig('ZERO', 22);
//                    $zero = json_decode($zero, true);
                    $zeroPid = '';
//                    if ($zero && isset($zero['base']['pid']) && $zero['base']['pid']) {
//                        $zero = explode('_', $zero['base']['pid']);
//                        $zeroPid = $zero[3];
//                    }
                    if ($orderInfo) {
                        if ($value['position_id'] == $zeroPid && isset($value['uid']) && $value['uid']) {
//                            $this->setZeroOrder($value);
                        } else {
                            if ($value['order_status'] == '3' && $orderInfo['rebate'] == '1') {
                                continue;
                            }
                            if (($value['order_status'] == '12' || $value['order_status'] == '3') && $orderInfo['rebate'] == '0') {
                                //计算预估收入
                                $commission->getEstiCommission($value);
                            }
                            //订单状态为已结算 && 订单没有锁定的情况下
                            if ($value['order_status'] == '3' && $orderInfo['locking'] != '1') {
                                if ($commission->getSettleCommission($value)) {
                                    $value['rebate'] = '1';
                                }
                            }
                        }
                        $order->updateOrderInfo($where, $value);
                    } else {
                        if ($value['position_id'] == $zeroPid && isset($value['uid']) && $value['uid']) {
//                            $this->setZeroOrder($value);
                        } else {
                            if ($value['order_status'] == '12' || $value['order_status'] == '3') {
                                $commission->getEstiCommission($value);
                            }
                            //订单状态为已结算0
                            if ($value['order_status'] == '3') {
                                if ($commission->getEstiCommission($value)) {
                                    $value['rebate'] = '1';
                                }
                            }
                        }
                        $order->addOrder($value);
                    }
                }
            }
        }
    }
}