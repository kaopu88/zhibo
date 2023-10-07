<?php
require_once dirname(dirname(dirname(__FILE__))) . '/AopSdk.php';

class AlipayTradeService
{

    //支付宝网关地址
    public $gateway_url = "https://openapi.alipay.com/gateway.do";

    //支付宝公钥
    public $alipay_public_key;

    //商户私钥
    public $private_key;

    //应用id
    public $appid;

    //编码格式
    public $charset = "UTF-8";

    public $token = NULL;

    //返回数据格式
    public $format = "json";

    //签名方式
    public $signtype = "RSA2";

    function __construct($alipay_config)
    {
        $this->gateway_url = $alipay_config['gateway_url'];
        $this->appid = $alipay_config['app_id'];
        $this->private_key = $alipay_config['alipay_private_key'];
        $this->alipay_public_key = $alipay_config['alipay_public_key'];
        $this->charset = $alipay_config['charset'];
        $this->signtype = $alipay_config['sign_type'];

        if (empty($this->appid) || trim($this->appid) == "") {
            throw new Exception("appid should not be NULL!");
        }
        if (empty($this->private_key) || trim($this->private_key) == "") {
            throw new Exception("private_key should not be NULL!");
        }
        if (empty($this->alipay_public_key) || trim($this->alipay_public_key) == "") {
            throw new Exception("alipay_public_key should not be NULL!");
        }
        if (empty($this->charset) || trim($this->charset) == "") {
            throw new Exception("charset should not be NULL!");
        }
        if (empty($this->gateway_url) || trim($this->gateway_url) == "") {
            throw new Exception("gateway_url should not be NULL!");
        }
    }

    //APP发起支付参数
    function appPay($builder, $notify_url)
    {
        $request = new AlipayTradeAppPayRequest();
        $bizcontent = $builder->getBizContent();
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        return $this->aopclientRequestExecute($request);
    }

    function aopclientRequestExecute($request)
    {
        $aop = new AopClient;
        $aop->gatewayUrl = $this->gateway_url;
        $aop->appId = $this->appid;
        $aop->rsaPrivateKey = $this->private_key;
        $aop->format = $this->format;
        $aop->charset = $this->charset;
        $aop->signType = $this->signtype;
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $aop->debugInfo = true;
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //$response = htmlspecialchars($response);//2018年1月25日问题修复@xulinyang
        //打开后，将报文写入log文件
        $this->writeLog("response: " . var_export($response, true));
        return $response;
    }

    //异步验证
    function check($arr)
    {
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $flag = $aop->rsaCheckV1($arr, NULL, $this->signtype);
        return $flag;
    }

    /**
     * 请确保项目文件有可写权限，不然打印不了日志。
     */
    function writeLog($text)
    {
        // $text=iconv("GBK", "UTF-8//IGNORE", $text);
        //$text = characet ( $text );
        //file_put_contents ( dirname ( __FILE__ ).DIRECTORY_SEPARATOR."./../../log.txt", date ( "Y-m-d H:i:s" ) . "  " . $text . "\r\n", FILE_APPEND );
    }
}

?>