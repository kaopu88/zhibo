<?php

namespace app\api\controller;

use app\common\controller\UserController;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use think\Db;

class Thirdorder extends UserController
{
    //统一下单中心
    public function unifiedorder()
    {
        $coreSdk = new CoreSdk();
        $get = input();
        if (empty($get['order_no'])) return $this->jsonError('订单号不能为空');
        if (empty($get['order_type'])) return $this->jsonError('订单类型不能为空');
        if (empty($get['pay_method'])) return $this->jsonError('支付方式不能为空');
        $payMethod = $get['pay_method'];
        $payInfo = Db::name('payments')->where('status',1)->where('class_name',$payMethod)->find();
        if (!$payInfo) return $this->jsonError('支付方式不支持');
        // if (!enum_in($get['pay_method'], 'pay_methods')) return $this->jsonError('支付方式不支持');
        $notify_url = '';
        // if ($get['order_type'] == 'recharge') {
        //     $notify_url = API_URL . '/?s=PayCallback.rechargeNotify';
        // } elseif ($get['order_type'] == 'taokeShop'){
        //     $notify_url = API_URL . '/?s=PayCallback.taokeShopNotify';
        // } elseif ($get['order_type'] == 'shopGoods'){
        //     $notify_url = API_URL . '/?s=PayCallback.shopGoodsNotify';
        // } elseif ($get['order_type'] == 'mall') {
        //     $notify_url = API_URL . '/?s=PayCallback.mallNotify';
        // } else {
        //     return $this->jsonError('不支持的订单类型');
        // }
        if ($get['order_type'] == 'recharge') {
            $notify_url = H5_URL . '/pay_callback/recharge_notify';
        } elseif ($get['order_type'] == 'taokeShop'){
            $notify_url = H5_URL . '/pay_callback/taokeShopNotify';
        } elseif ($get['order_type'] == 'shopGoods'){
            $notify_url = H5_URL . '/pay_callback/shopGoodsNotify';
        } elseif ($get['order_type'] == 'mall') {
            $notify_url = H5_URL . '/pay_callback/mallNotify';
        } else {
            return $this->jsonError('不支持的订单类型');
        }
        $result = $coreSdk->post('third_order/unifiedorder', array(
            'rel_type' => $get['order_type'],
            'rel_no' => $get['order_no'],
            'pay_method' => $get['pay_method'],
            'user_id' => USERID,
            'notify_url' => $notify_url,
            'client_seri' => ClientInfo::encode()
        ));
        if (!$result) return $this->jsonError($coreSdk->getError());
        return $this->success($result, '获取成功');
    }
}
