<?php

namespace bxkj_payment;
use bxkj_common\HttpClient;

class HjpayPayMethod extends ThirdPayMethod
{
    protected $config;
    protected $payMethod = 'hjpay';
    public function __construct()
    {
        parent::__construct();
        $this->config = array (
                'app_id' => '886',
                'ptype'=> '917',
                'mch_key' => 'SY5VPRGRDOK9NF42JAFKEWXNWOVQK1TIL9AU4QQFJX9Q1K44V8BUCEL9TEEA0C9F305TO3BYVHHQAM5KKBMV94LSRBUCOXY14LSGEPFWG2EV9RLFWTQTVVYTMBCSE4RX',
                'gateway_url' => 'http://www.hjiajiapay.cn/pay/trade',
              );
        // $this->config = config("payment.alipay");
    }
    
    public function check($tradeData, &$inputData)
    {
        $inputData['app_id'] = $this->config['app_id'];
        if (!isset($inputData['receipt_account'])) {
            $inputData['receipt_account'] = $this->config['receipt_account'];
        }
        return true;
    }
    
    /**
     * 创建签名
     * @param $Md5key   秘钥
     * @param $list     参数数组
     * @return string
     */
    public function getSign($list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            if (!empty($val) && $key != 'sign') {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        $md5str = rtrim($md5str, '&');
        $sign = md5($md5str . $this->config['mch_key']);
        return $sign;
    }
    
    public function unifiedorder($inputData, $tradeData)
    {   
        $url = 'http://www.hjiajiapay.cn/pay/trade/payinsert';
        $data = [
            'pay_orderid'=>$tradeData['trade_no'],
            'money'=>$tradeData['total_fee'],
            'pay_notifyurl'=>$tradeData['notify_url'],
            'pay_callbackurl'=>$tradeData['return_url'],
            'memberid'=>$this->config['app_id'],
            'ptype'=>$this->config['ptype']
        ];
        $data['sign'] = self::getSign($data);
        $data['format'] = 'json';
        $data['attach'] = $inputData['rel_no'];
        $curlClient = new HttpClient();
        $result = $curlClient->setCA(false)->setssl(true)->setContentType('url')->post($url, $data)->getData('json');
        if($result['code']==200)return $result['datas'];
    }
      
    
    public function responseNotify($data)
    {
        header('Content-Type:text/plain;charset=utf-8');
        if ($data['status'] == 'success') {
            echo 'success';
        } else {
            echo 'fail!' . $data['message'];
        }
        exit();
    }
}