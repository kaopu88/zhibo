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

class TaobaoOrder extends Service
{
    public function formatOrder($orderList)
    {
        $data = [];
        foreach($orderList as $value){
            $order['type'] = 0;
            $order['create_time'] = strtotime($value['tk_create_time']);//创建时间
            $order['click_time'] = strtotime($value['click_time']);//点击时间
            $order['goods_order'] = $value['trade_parent_id'];//订单号
            $order['goods_sonorder'] = isset($value['trade_id']) ? $value['trade_id'] : '0';//子订单号
            $order['goods_id'] = $value['item_id'];//商品id
            $order['title'] = $value['item_title'];//商品标题
            $img = isset($value['item_img']) ? $value['item_img'] : "";//商品主图
            $order['img'] = strpos($img, "http") === false ? "http:".$img : $img;
            $order['price'] = $value['item_price'];//商品单价
            $order['num'] = $value['item_num'];//商品数量
            $order['pay_time'] = isset($value['tk_paid_time']) ? strtotime($value['tk_paid_time']) : 0;//付款时间
            $order['pay_price'] = isset($value['alipay_total_price']) ? $value['alipay_total_price'] : 0;//实际付款金额
            $order['settle_price'] = isset($value['alipay_total_price']) ? $value['alipay_total_price'] : 0;//支付总金额
            $order['commission'] = isset($value['pub_share_pre_fee']) ? $value['pub_share_pre_fee'] : 0;//预估佣金
            $order['commission_rate'] = isset($value['tk_total_rate']) ? $value['tk_total_rate'] : 0;//佣率
            $order['shop_type'] = $value['order_type'];//订单类型
            $order['position_id'] = $value['adzone_id'];//推广位id
            $order['position_name'] = $value['adzone_name'];//推广位名称
            $order['relation_id'] = isset($value['relation_id']) ? $value['relation_id']: 0;//渠道id
            $order['special_id'] = isset($value['special_id']) ? $value['special_id']: 0;//会员id
            $order['create_time'] = time();
            // 淘客订单状态，3：订单结算，12：订单付款， 13：订单失效，14：订单成功
            if ($value['tk_status'] == '14') {//订单状态
                $order['order_status'] = 3;
            }else{
                $order['order_status'] = $value['tk_status'];
            }
            if ($order['order_status'] == 3) {//结算时间
                $order['earning_time'] = isset($value['tk_earning_time']) && $value['tk_earning_time'] ? strtotime($value['tk_earning_time']) : strtotime($value['tk_earning_time']);
            }else{
                $order['earning_time'] = isset($value['tk_earning_time'])  ? strtotime($value['tk_earning_time']) : 0;
            }
            if(($order['relation_id'] != 0 || $order['special_id'] != 0)){
                $user = new User();
                $where["relation_id"] = $order["relation_id"];
                if($order['special_id'] != 0){
                    $where["special_id"] = $order["special_id"];
                }
                $userInfo = $user->getUserInfo($where);
                if($userInfo){
                    $order['user_id'] = $userInfo['user_id'];
                }else{
                    $order['user_id'] = 0;
                }
            }
            $data[] = $order;
        }
        return $data;
    }

    /**
     * 淘宝订单处理
     * @param $data
     * @param int $rebate
     */
    public function addOrder($data, $rebate=0)
    {
        if ($data) {
            $order_list = $this->formatOrder($data);
            $order = new Order();
            $commission = new Commission();
            foreach ($order_list as $key => $value) {
                $where["goods_order"] = $value['goods_order'];
                $where["goods_sonorder"] = $value['goods_sonorder'];
                $orderInfo = $order->getOrderInfo($where);
//                $config = new Config();
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