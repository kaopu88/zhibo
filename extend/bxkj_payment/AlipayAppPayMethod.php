<?php

namespace bxkj_payment;



use think\facade\Request;

class AlipayAppPayMethod extends AlipayPayMethod
{
    protected $sdkPath;
    protected $payMethod = 'alipay_app';

    public function __construct()
    {
        parent::__construct();
        $this->sdkPath = ALIPAY_SDK_PATH . '/apppay/';
        require_once $this->sdkPath . 'service/AlipayTradeService.php';
    }

    public function check($tradeData, &$inputData)
    {
        $inputData['app_id'] = $this->config['app_id'];
        return true;
    }

    public function unifiedorder($inputData, $tradeData)
    {
        require_once $this->sdkPath . 'buildermodel/AlipayTradeAppPayContentBuilder.php';
        $out_trade_no = $tradeData['trade_no'];
        $subject = $tradeData['subject'];
        $total_amount = $tradeData['total_fee'];
        $body = $tradeData['body'];
        //构造参数
        $payRequestBuilder = new \AlipayTradeAppPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setBusinessParams(['outTradeRiskInfo' => ['mcCreateTradeTime' => date('Y-m-d H:i:s')], 'mcCreateTradeIp' => Request::ip()]);
        if (!empty($tradeData['valid_period'])) {
            $timeout_express = "{$tradeData['valid_period']}s";
            $payRequestBuilder->setTimeExpress($timeout_express);
        }
        $aop = new \AlipayTradeService($this->config);
        $response = $aop->appPay($payRequestBuilder, $this->getNotifyUrl());
        return $response;
    }

    public function checkNotify($post)
    {
        $alipaySevice = new \AlipayTradeService($this->config);
        $alipaySevice->writeLog(var_export($post, true));
        $result = $alipaySevice->check($post);
        if (!$result) return array('status' => 'failed', 'message' => 'sign error');
        return true;
    }

    public function getNotifyData($post)
    {
        $params = array();
        $params['out_trade_no'] = $post['out_trade_no'];
        $params['trade_no'] = $post['trade_no'];
        $params['total_fee'] = $post['total_amount'];
        $params['uid'] = $post['buyer_id'];
        $params['pay_method'] = $this->payMethod;
        $params['app_key'] = $post['app_id'];
        $params['trade_status'] = $post['trade_status'];//TRADE_FINISHED TRADE_SUCCESS
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

}