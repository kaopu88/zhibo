<?php

namespace bxkj_payment;

class ThirdPayMethod
{
    protected $error;
    protected $payMethod;

    public function __construct()
    {
    }

    public function getError()
    {
        return $this->error;
    }

    protected function setError($message = '', $code = 1)
    {
        $this->error = is_error($message) ? $message : make_error($message, $code);
        return false;
    }

    //获取返回商家页地址
    protected function getReturnUrl($payMethod = null)
    {
        $payMethod = isset($payMethod) ? $payMethod : $this->payMethod;
        $notifyUrl = DOMAIN_URL.'/pay_success_return/'.$payMethod;
        return $notifyUrl;
    }

    //获取异步通知地址
    protected function getNotifyUrl($payMethod = null)
    {
        $payMethod = isset($payMethod) ? $payMethod : $this->payMethod;
        $notifyUrl = DOMAIN_URL.'/pay_success_notify/'.$payMethod;
        return $notifyUrl;
    }

    //下列方法需要被重写
    public function checkNotify($data)
    {
        return false;
    }

    public function checkReturn($data)
    {
        return false;
    }

    public function getNotifyData($data)
    {
        return $data;
    }

    public function getReturnData($data)
    {
        return $data;
    }

    public function responseNotify($data)
    {
        exit();
    }

    public function check($tradeData, &$inputData)
    {
        return false;
    }

    public function unifiedorder($inputData, $tradeData)
    {
        return false;
    }
}