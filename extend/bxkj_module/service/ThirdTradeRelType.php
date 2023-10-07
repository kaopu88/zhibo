<?php

namespace bxkj_module\service;

use bxkj_module\service\Service;
use think\Db;

class ThirdTradeRelType extends Service
{
    //充值订单
    public function rechargeHandler(&$order)
    {
        $rechargeOrder = Db::name('recharge_order')->where(array('order_no' => $order['rel_no'], 'user_id' => $order['user_id']))->find();
        if (empty($rechargeOrder)) return $this->setError('充值订单不存在');
        if ($rechargeOrder['pay_status'] != '0') return $this->setError('充值订单已支付或已取消');
        if (!isset($order['subject'])) {
            $order['subject'] = APP_BEAN_NAME . '充值-' . $rechargeOrder['bean_num'];
        }
        if (!isset($order['body'])) {
            $APP_BEAN_NAME = APP_BEAN_NAME;
            $order['body'] = "{$order['rel_no']}-{$APP_BEAN_NAME}充值-{$rechargeOrder['bean_num']}";
        }
        $order['total_fee'] = $rechargeOrder['price'];
        return true;
    }

    //开通权限小店
    public function taokeShopHandler(&$order)
    {
        $dredgeOrder = Db::name('dredge_log')->where(array('order_no' => $order['rel_no'], 'user_id' => $order['user_id']))->find();
        if (empty($dredgeOrder)) return $this->setError('订单不存在');
        if ($dredgeOrder['pay_status'] != '0') return $this->setError('订单已支付或已取消');
        if (!isset($order['subject'])) {
            $order['subject'] = '淘客小店权限开通';
        }
        if (!isset($order['body'])) {
            $order['body'] = "淘客小店权限开通";
        }
        $order['total_fee'] = $dredgeOrder['price'];
        return true;
    }


    //店铺商品
    public function shopGoodsHandler(&$order)
    {
        $shopGoodsOrder = Db::name('ns_order')->where(array('out_trade_no' => $order['rel_no'], 'buyer_id' => $order['user_id']))->find();

        if (empty($shopGoodsOrder)) return $this->setError('订单不存在');
        if ($shopGoodsOrder['pay_status'] != '0') return $this->setError('订单已支付或已取消');
        if (!isset($order['subject'])) {
            $order['subject'] = '商品订单支付';
        }
        if (!isset($order['body'])) {
            $order['body'] = "商品订单支付";
        }
        $order['total_fee'] = $shopGoodsOrder['order_money'];
        return true;
    }

    //开通商城
    public function mallHandler(&$order)
    {
        $dredgeOrder = Db::name('dredge_log')->where(array('order_no' => $order['rel_no'], 'user_id' => $order['user_id']))->find();
        if (empty($dredgeOrder)) return $this->setError('订单不存在');
        if ($dredgeOrder['pay_status'] != '0') return $this->setError('订单已支付或已取消');
        if (!isset($order['subject'])) {
            $order['subject'] = '商城权限开通';
        }
        if (!isset($order['body'])) {
            $order['body'] = "商城权限开通";
        }
        $order['total_fee'] = $dredgeOrder['price'];
        return true;
    }

    public function raiseHandler(&$order)
    {
        $raiseOrder = Db::name('movie_raise')->where(array('order_no' => $order['rel_no'], 'user_id' => $order['user_id']))->find();
        if (empty($raiseOrder)) return $this->setError('认购订单不存在');
        if ($raiseOrder['pay_status'] != '0') return $this->setError('认购订单已支付或已取消');
        if (!isset($order['subject'])) {
            $order['subject'] = $raiseOrder['title'] . '-认购';
        }
        if (!isset($order['body'])) {
            $order['body'] = "【{$order['rel_no']}】{$raiseOrder['title']}-认购";
        }
        $order['total_fee'] = $raiseOrder['total_fee'];
        return true;
    }
}