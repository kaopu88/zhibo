<?php

namespace bxkj_payment;

use think\Console;

class WxpayAppPayMethod extends WxpayPayMethod
{
    protected $payMethod = 'wxpay_app';

    public function __construct()
    {
        parent::__construct();
        if (!class_exists('WxPayApi')) {
            require_once $this->sdkPath . 'WxPay.Api.php';
        }
    }

    public function check($tradeData, &$inputData)
    {
        $inputData['app_id'] = $this->config['app_id'];
        return true;
    }

    //下单
    public function unifiedorder($inputData, $tradeData)
    {
        $time = time();
        $nonceStr = get_ucode(10, '1aA');
        $input = new \WxPayUnifiedOrder();
        // 订单信息设置
        $input->SetOut_trade_no($tradeData['trade_no']); // $tradeData['trade_no'] . 'V' . rand(10, 99)
        $totalFee = bcmul($tradeData['total_fee'], 100, 2);
        $input->SetTotal_fee((int)$totalFee);
        $input->SetBody($tradeData['subject']);//微信的body相当于subject
        // 统一下单基础设置
        $input->SetNotify_url($this->getNotifyUrl());
        $input->SetTrade_type('APP');
        $input->SetSpbill_create_ip($tradeData['client_ip'] ? $tradeData['client_ip'] : get_client_ip());
        $input->SetAppid($this->config['app_id']);//公众账号ID
        $input->SetMch_id($this->config['mch_id']);//商户号
        $input->SetNonce_str(md5($nonceStr . time()));
        // 统一下单功能设置
        $timeout = !empty($tradeData['valid_period']) ? $tradeData['valid_period'] : 3600;//默认60分钟
        $input->SetTime_start(date('YmdHis', $time));
        if (isset($timeout)) $input->SetTime_expire(date('YmdHis', $time + $timeout));
        // 开始下单
        $order = \WxPayApi::unifiedOrder($input);
        if (!isset($order['return_code']) || $order['return_code'] !== 'SUCCESS') {
            bxkj_console(json_encode($order));
            return $this->setError('微信下单失败');
        }
        $request = [
            'appid' => $order['appid'],
            'partnerid' => $order['mch_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => md5($nonceStr),
            'timestamp' => (string)$time,
        ];
        if (isset($order['prepay_id'])) {
            $request['prepayid'] = $order['prepay_id'];
        }
        $request['sign'] = $this->makeSign($request);
        return $request;
    }

    private function makeSign(array $request)
    {
        //签名步骤一：按字典序排序参数
        ksort($request);
        $string = $this->toUrlParams($request);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->config['mch_key'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    //检查通知参数
    public function checkNotify($post)
    {
        $xml = file_get_contents('php://input');
        try {
            $res = \WxPayResults::Init($xml);
        } catch (\WxPayException $e) {
            $res = false;
        }
        if (!$res) return array('status' => 'failed', 'message' => 'sign error');
        return true;
    }

    public function getNotifyData($post)
    {
        $postStr = file_get_contents('php://input');;
        if (!empty($postStr)) $post = xml_to_array($postStr);
        $params = array();
        $params['out_trade_no'] = $post['out_trade_no'];
        $params['trade_no'] = $post['transaction_id'];
        $params['total_fee'] = bcdiv($post['total_fee'], 100, 2);//分转成元
        $params['uid'] = $post['openid'];
        $params['pay_method'] = $this->payMethod;
        $params['app_key'] = $post['appid'];
        $params['trade_status'] = $post['result_code'] == 'SUCCESS' ? 'TRADE_SUCCESS' : 'FAIL';
        return array('params' => $params, 'raw' => $post);
    }

    public function checkReturn($get)
    {
        return array('status' => 'failed', 'message' => 'not support');
    }

    public function getReturnData($get)
    {
        return null;
    }

    //响应通知
    public function responseNotify($data)
    {
        if ($data['status'] == 'success') {
            $this->returnXml('SUCCESS', $data['message'] ? $data['message'] : 'OK');
        } else {
            $this->returnXml('FAIL', $data['message']);
        }
    }
}