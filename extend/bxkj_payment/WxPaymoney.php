<?php
/**
 * Created by PhpStorm.
 * User: zack
 * qq: 840855344
 * phone：18156825246
 */

namespace bxkj_payment;

use bxkj_common\HttpClient;
use think\Console;

class WxPaymoney extends WxpayPayMethod
{
    public function __construct($myAppKey = 'user')
    {
        parent::__construct();
    }

    public function transfers($inputData)
    {
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $key = $this->config['mch_key'];
        $data['mch_appid'] = $this->config['app_id'];
        $data['mchid'] = $this->config['mch_id'];
        $data['nonce_str'] = md5(uniqid() . get_ucode());
        $data['device_info'] =  '';
        $data['partner_trade_no'] = $inputData['cash_no'];
        $data['openid'] = $inputData['openid'];
        $data['check_name'] = 'NO_CHECK';
        $data['amount'] = (int)bcmul($inputData['rmb'], 100, 2);
        $data['desc'] = $inputData['desc'] ?: '转账';
        $data['sign'] = generate_wx_sign($data, $key);

        if ($data['amount'] == 0) return $this->setError('TOTAL_FEE 0');
        $curlClient = new HttpClient();
        $result = $curlClient->setCA(false)->setssl(true)->setContentType('xml')->post($url, $data)->getData('xml');
        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            return $this->setError($result['err_code_des']);//$result['return_msg']
        }

        return true;
    }
}