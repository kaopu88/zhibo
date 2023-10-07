<?php

namespace bxkj_payment;


class ApplePay
{
    protected $verifyReceiptApi;

    public function __construct($sandbox = null)
    {
        $config = config('payment.applepay');
        $sandbox = isset($sandbox) ? $sandbox : $config['sandbox'];
        $this->verifyReceiptApi = $sandbox ? $config['sandbox_verify_receipt'] : $config['verify_receipt_url'];
    }

    public function getVerifyReceiptApi()
    {
        return $this->verifyReceiptApi;
    }

    public function verifyReceipt($receipt)
    {
        if (empty($this->verifyReceiptApi)) return array('status' => -1, 'message' => '支付验证失败[01]');
        $postData = json_encode(array('receipt-data' => trim($receipt)));
        $ch = curl_init($this->verifyReceiptApi);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);//10秒
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($errno) return array('status' => -1, 'message' => "支付验证失败{$errno}[02]");
        $data = json_decode($response, true);
        if (!$data || !is_array($data)) return array('status' => -1, 'message' => '支付验证失败[03]');
        $messageArr = [
            '21000' => 'AppStore无法读取你提供的JSON数据',
            '21002' => '收据数据不符合格式',
            '21003' => '收据数据无法被验证',
            '21004' => '你提供的共享密钥和账户的共享密钥不一致',
            '21005' => '收据服务器当前不可用',
            '21006' => '订阅服务已过期',
            '21007' => '收据信息仅适用于沙盒环境',
            '21008' => '收据信息仅适用于生产环境'
        ];
        if (!empty($data['status'])) {
            $message = '支付验证失败[04]';
            if (isset($messageArr[(string)$data['status']])) {
                $message = $messageArr[(string)$data['status']];
            }
            return array('status' => $data['status'], 'message' => $message);
        }
        $receiptRes = $data['receipt'];
        return array('status' => 0, 'data' => $receiptRes);
    }
}