<?php

namespace app\common\service;

use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use think\Db;

class AppleReceipt extends Service
{
    protected $clientInfo;
    protected $maps;
    protected $coreSdk;

    public function __construct()
    {
        parent::__construct();
        $this->coreSdk = new CoreSdk();
    }

    //核销苹果收据
    public function writeOff($user_id, $receipt, $clientInfo)
    {
        $this->clientInfo = array_merge([
            'client_ip' => '',
            'v' => '',
        ], $clientInfo);
        $this->maps = $this->getProductTypes();
        if (empty($receipt)) return $this->setError('支付收据不能为空');
        $testArr = config('app.test_user');
        $RUNTIME_ENVIROMENT = strtolower(RUNTIME_ENVIROMENT);
        $sandbox = (in_array($user_id, $testArr) || $RUNTIME_ENVIROMENT != 'pro') ? true : null;
        $applePay = new \bxkj_payment\ApplePay($sandbox);
        $info = $applePay->verifyReceipt($receipt);
        $verifyReceiptApi = $applePay->getVerifyReceiptApi();
        $receiptLog = [];
        $receiptLog['user_id'] = $user_id ? $user_id : 0;
        $receiptLog['verify_receipt_api'] = $verifyReceiptApi ? $verifyReceiptApi : '';
        $receiptLog['receipt'] = $receipt ? $receipt : '';
        $receiptLog['data'] = $info ? json_encode($info) : '';
        $receiptLog['meid'] = defined('APP_MEID') ? APP_MEID : '';
        $receiptLog['app_v'] = defined('APP_V') ? APP_V : '';
        $sha1 = sha1($receiptLog['receipt']);
        $hasLog = Db::name('apple_receipt')->where(['sha1' => $sha1])->find();
        if ($hasLog) {
            $receiptLog['refresh_time'] = empty($receiptLog['refresh_time']) ? time() : ($receiptLog['refresh_time'] . ',' . time());
            Db::name('apple_receipt')->where(array('id' => $hasLog['id']))->update($receiptLog);
        } else {
            $receiptLog['sha1'] = $sha1;
            $receiptLog['verify_time'] = time();
            $logId = Db::name('apple_receipt')->insertGetId($receiptLog);
            $hasLog['id'] = $logId;
        }
        if (!$info) return $this->setError('支付验证失败 info null');
        if ($info['status'] != 0) return $this->setError($info['message']);
        $info = $info['data'];
        $inApps = $info['in_app'];
        $total = 0;
        $orderNos = [];
        foreach ($inApps as $inApp) {
            $productId = $inApp['product_id'];
            $type = $this->maps[$productId];
            if (empty($type)) continue;
            $result = $this->writeOffOne($user_id, $inApp);
            if (!$result) continue;
            $orderNos[] = $result;
            $total++;
        }
        $this->clientInfo = [];
        $res = ['total' => $total, 'order_nos' => $orderNos];
        Db::name('apple_receipt')->where(array('id' => $hasLog['id']))->update(['result' => json_encode($res)]);
        return $res;
    }

    protected function writeOffOne($user_id, $inApp)
    {
        self::startTrans();
        $productId = $inApp['product_id'];
        $type = $this->maps[$productId];
        $funName = parse_name($type . '_handler', 1, false);
        if (!method_exists($this, $funName)) {
            self::rollback();
            return $this->setError('产品类型不存在');
        }
        $result = call_user_func_array([$this, $funName], [$user_id, $inApp]);
        if (!$result) {
            self::rollback();
            return $this->setError('核销[' . $type . ']失败:' . $this->error);
        }
        self::commit();
        return $result;
    }

    protected function getProductTypes()
    {
        $arr = ['vip' => 'vip', 'recharge_bean' => 'recharge'];
        $maps = cache('apple_product_types');
        if (empty($maps)) {
            $maps = [];
            foreach ($arr as $table => $type) {
                $result = Db::name($table)->field('apple_id')->where([['apple_id', 'neq', '']])->select();
                if (!empty($result)) {
                    foreach ($result as $item) {
                        $maps[$item['apple_id']] = $type;
                    }
                }
            }
            cache('apple_product_types', $maps, 7200);
        }
        return $maps ? $maps : [];
    }

    protected function vipHandler($user_id, $inApp)
    {
        $productId = $inApp['product_id'];
        $quantity = $inApp['quantity'];//VIP购买数量
        $order = Db::name('vip_order')->where([
            'third_trade_no' => $inApp['transaction_id'],
            'pay_method' => 'applepay_app'
        ])->find();
        $vipService = new Vip();
        if (!$order) {
            $params['apple_id'] = $productId;
            $params['quantity'] = $quantity;
            $params['user_id'] = $user_id;
            $params['client_ip'] = $this->clientInfo['client_ip'];
            $params['app_v'] = $this->clientInfo['v'];
            $order = $vipService->create($params);
            if (!$order) return $this->setError($vipService->getError());
        }
        if (empty($order)) return $this->setError('订单不存在');
        if ($order['pay_status'] == '1') return $order['order_no'];
        $thirdData['rel_no'] = $order['order_no'];//关联单号
        $thirdData['trade_no'] = $inApp['transaction_id'];
        $thirdData['pay_method'] = 'applepay_app';
        $result2 = $vipService->paySuccess($thirdData, false);
        if (!$result2) return $this->setError('vip支付失败');
        /*  $this->kpi('vip', $thirdData, $user_id, [
              'total_fee' => $order['rmb'],
              'subject' => $order['name']
          ]);*/
        return $result2['order_no'];
    }

    protected function rechargeHandler($user_id, $inApp)
    {
        $productId = $inApp['product_id'];
        $quantity = $inApp['quantity'];
        $order = Db::name('recharge_order')->where([
            'third_trade_no' => $inApp['transaction_id'],
            'pay_method' => 'applepay_app'
        ])->find();
        $rec = new Recharge();
        if (!$order) {
            $params['apple_id'] = $productId;
            $params['quantity'] = (int)$quantity;
            $params['user_id'] = $user_id;
            $params['client_ip'] = $this->clientInfo['client_ip'];
            $params['app_v'] = $this->clientInfo['v'];
            $order = $rec->create($params);
            if (!$order) return $this->setError($rec->getError());
        }
        if (empty($order)) return $this->setError('订单不存在');
        if ($order['pay_status'] == '1') return $order['order_no'];
        $thirdData['rel_no'] = $order['order_no'];//关联单号
        $thirdData['trade_no'] = $inApp['transaction_id'];
        $thirdData['pay_method'] = 'applepay_app';
        $result2 = $rec->paySuccess($thirdData);
        if (!$result2) return $this->setError('充值支付失败');
        /* $this->kpi('recharge', $thirdData, $user_id, [
             'total_fee' => $order['price'],
             'subject' => $order['name']
         ]);*/

        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.user_recharge', ['user_id' => $user_id, 'pay_method' => 'applepay_app', 'value' => $order['total_fee']]);
        return $order['order_no'];
    }

    protected function kpi($relType, $thirdData, $userId, $order)
    {
        //废弃 2018-10-12
        return false;
        $trade['rel_type'] = $relType;
        $trade['rel_no'] = $thirdData['rel_no'];
        $trade['user_id'] = $userId;
        $trade['total_fee'] = $order['total_fee'];
        $trade['trade_no'] = $thirdData['trade_no'];
        $trade['subject'] = $order['subject'] ? $order['subject'] : '';
        $trade['pay_method'] = $thirdData['pay_method'];
        list($platform, $m) = explode('_', $thirdData['pay_method']);
        $trade['pay_platform'] = $platform;
        $result = $this->coreSdk->post('bean/kpi_cons', $trade);
        if (!$result) return false;
        return $result;
    }


}