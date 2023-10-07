<?php

namespace bxkj_payment;

use bxkj_common\HttpClient;
use think\Console;

class WxpayH5PayMethod extends WxpayPayMethod
{
    protected $payMethod = 'wxpay_h5';

    public function __construct($myAppKey = 'user')
    {
        parent::__construct();
        $this->config = config("payment.wxpay_wap");
    }

    public function check($tradeData, &$inputData)
    {
        $inputData['app_id'] = $this->config['app_id'];
        if (empty($inputData['openid'])) return $this->setError('OPENID不能为空');
        return true;
    }

    //下单
    public function unifiedorder($inputData, $tradeData)
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $key = $this->config['mch_key'];
        $data['appid'] = $this->config['app_id'];
        $data['mch_id'] = $this->config['mch_id'];
        $data['device_info'] = $tradeData['client_key'] ? $tradeData['client_key'] : '';
        $data['nonce_str'] = md5(uniqid() . get_ucode());
        $data['sign_type'] = 'MD5';
        $data['body'] = $tradeData['body'];
        $data['out_trade_no'] = $tradeData['trade_no'];
        $data['total_fee'] = (int)bcmul($tradeData['total_fee'], 100, 2);
        $data['spbill_create_ip'] = $tradeData['client_ip'];
        $time = time();
        $data['time_start'] = date('YmdHis', $time);
        $timeout = $tradeData['valid_period'];
        if (isset($timeout)) $data['time_expire'] = date('YmdHis', $time + $timeout);
        $data['notify_url'] = $this->getNotifyUrl();
        $data['trade_type'] = 'JSAPI';
        $data['openid'] = $inputData['openid'];
        $data['attach'] = json_encode(array(
            'rel_type' => $tradeData['rel_type'],
            'rel_no' => $tradeData['rel_no'],
        ));
        if ($data['total_fee'] == 0) return $this->setError('TOTAL_FEE 0');
        $data['sign'] = generate_wx_sign($data, $key);
        $curlClient = new HttpClient();
        $result = $curlClient->setCA(false)->setContentType('xml')->post($url, $data)->getData('xml');
        if ($result['return_code'] != 'SUCCESS') {
            bxkj_console([$result, $data]);
            return $this->setError('调用微信支付失败');//$result['return_msg']
        }
        $nonceStr = get_ucode(10, '1aA');
        $wxpayInfo = array(
            'appId' => $this->config['app_id'],
            'timeStamp' => (string)$time,
            'nonceStr' => md5($nonceStr),
            'package' => 'prepay_id=' . $result['prepay_id'],
            'signType' => 'MD5',
        );
        $wxpayInfo['paySign'] = generate_wx_sign($wxpayInfo, $key);
        //unset($wxpayInfo['appId']);
        return $wxpayInfo;
    }

    //检查通知参数
    public function checkNotify($post)
    {
        $postStr = file_get_contents('php://input');;
        if (!empty($postStr)) {
            $post = xml_to_array($postStr);
        }
        if (empty($post)) return array('status' => 'failed', 'message' => 'FAILED');
        $sign = $post['sign'];
        unset($post['sign']);
        $wxConfig = config("payment.wxpay_wap");
        $key = $wxConfig['mch_key'];
        $testSign = generate_wx_sign($post, $key);
        if ($testSign != $sign) return array('status' => 'failed', 'message' => 'sign error');
        return true;
    }

    public function getNotifyData($post)
    {
        $postStr = file_get_contents('php://input');;
        if (!empty($postStr)) {
            $post = xml_to_array($postStr);
        }
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