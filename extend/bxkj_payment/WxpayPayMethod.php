<?php
namespace bxkj_payment;


class WxpayPayMethod extends ThirdPayMethod
{
    protected $config;
    protected $sdkPath;

    public function __construct()
    {
        parent::__construct();
        $this->sdkPath = config('payment.wxpay_sdk_path');
        $this->config = config("payment.wxpay");
    }

    //返回XML格式的数据
    protected function returnXml($return_code, $return_msg)
    {
        header("Content-type:text/xml");
        $xml = array_to_xml(array(
            'return_code' => $return_code,
            'return_msg' => $return_msg
        ));
        echo $xml;
        exit();
    }

    protected function toUrlParams(array $request)
    {
        $buff = "";
        foreach ($request as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}