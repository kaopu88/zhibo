<?php

namespace bxkj_payment;

use bxkj_common\HttpClient;

class PayMethod extends ThirdPayMethod
{
    protected $config;
    protected $curlClient;

    public function __construct()
    {
        parent::__construct();
        $this->config = config("payment.aomypay");
        $this->curlClient = new HttpClient();
        define('KDBPAY_BASE', config('payment.payment_base'));
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