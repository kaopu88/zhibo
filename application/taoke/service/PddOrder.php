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

class PddOrder extends Service
{
    public function formatOrder($orderList)
    {
        $data = [];
        foreach($orderList as $value){
            $order['type'] = 1;
            $order['goods_order'] = $value['order_sn']; //订单号
            $order['goods_sonorder'] = '0'; //子订单号
            $order['goods_id'] = $value['goods_id']; //商品id
            $order['title'] = $value['goods_name']; //商品名称
            $order['img'] = $value['goods_thumbnail_url']; //商品图片
            switch ($value['order_status']) {
                case '-1':
                    $order_status = 13;
                    break;
                case '0':
                    $order_status = 12;
                    break;
                case '1':
                    $order_status = 12;
                    break;
                case '2':
                    $order_status = 12;
                    break;
                case '3':
                    $order_status = 12;
                    break;
                case '4':
                    $order_status = 13;
                    break;
                case '5':
                    $order_status = 3;
                    break;
                case '8':
                    $order_status = 13;
                    break;
                default:
                    $order_status = 13;
                    break;
            }
            $order['order_status'] = $order_status; //订单状态
            $order['price'] = sprintf("%.2f", $value['goods_price'] / 100);//原价
            $order['num'] = intval($value['goods_quantity']); //购买数量
            $order['pay_price'] = sprintf("%.2f", $value['order_amount'] / 100);//付款金额
            $order['settle_price'] = sprintf("%.2f", $value['order_amount'] / 100);//结算金额
            $order['commission_rate'] = $value['promotion_rate'] / 10;//佣金比例  千分比 /10为百分比
            $order['commission'] = sprintf("%.2f", $value['promotion_amount'] / 100); //订单佣金金额
            $order['position_id'] = $value['p_id'];//推广位id
            $order['shop_type'] = '拼多多';
            $order['earning_time'] = isset($value['order_receive_time']) != NULL ? $value['order_receive_time'] :0;//结算时间
            $order['create_time'] = time();
            $order['click_time'] = time();
            if($value["p_id"]){
                $user = new User();
                $where["pdd_pid"] = $value["p_id"];
                $userInfo = $user->getUserInfo($where);
                if($userInfo){
                    $order['user_id'] = $userInfo['id'];
                    $order['position_name'] = $userInfo['nickname'];
                }else{
                    $order['user_id'] = 0;
                }
            }
            $data[] = $order;
        }
        return $data;
    }

    /**
     * 拼多多订单处理
     * @param $data
     * @param int $rebate
     */
    public function addOrder($data, $rebate=0)
    {
        if (!empty($data)) {
            $order_list = $data['order_list'];
            if ($order_list) {
                $order_list = $this->formatOrder($order_list);
                $order = new Order();
                $commission = new Commission();
                foreach ($order_list as $key => $value) {
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