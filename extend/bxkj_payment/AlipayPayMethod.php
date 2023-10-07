<?php

namespace bxkj_payment;


class AlipayPayMethod extends ThirdPayMethod
{
    protected $config;

    public function __construct()
    {
        parent::__construct();
        define('ALIPAY_SDK_PATH', config('payment.alipay_sdk_path'));
        define('ALIPAY_SDK_AOP_PATH', ALIPAY_SDK_PATH . '/aop');
        define('AOP_SDK_WORK_DIR',config('payment.alipay_aop_sdk_work_dir'));
        define('AOP_SDK_DEV_MODE',config('payment.alipay_aop_sdk_dev_mode'));
        $this->config = config("payment.alipay");
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