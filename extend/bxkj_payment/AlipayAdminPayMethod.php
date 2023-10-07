<?php

namespace bxkj_payment;

class AlipayAdminPayMethod extends AlipayPayMethod
{
    protected $sdkPath;
    protected $payMethod = 'alipay_admin';

    public function __construct()
    {
        parent::__construct();
        require_once ALI_PATH . 'AopClient.php';
        require_once ALI_PATH . 'request/AlipayFundTransToaccountTransferRequest.php';
    }

    public function aliTransferAccounts($data, $class = '')
    {
        if (!class_exists($class)) return ['code' => 104, 'msg' => '非法操作'];

        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->config['app_id'];
        $aop->rsaPrivateKey = $this->config['alipay_private_key'];
        $order_no = $data['cash_no'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent("{" .
            "\"out_biz_no\":\"" . $order_no . "\"," . //本地唯一订单号
            "\"payee_type\":\"ALIPAY_LOGONID\"," . //账号类型->ALIPAY_LOGONID 支付宝登录账号  | ALIPAY_USERID 支付宝用户id
            "\"payee_account\":\"" . $data['account'] . "\"," . //收款方支付宝账号或支付宝用户id 和 payee_type 参数对应
            "\"payee_real_name\":\"" . $data['username'] . "\"," .     //收款人姓名
            "\"amount\":\"" . $data['rmb'] . "\"," .               //转账金额，最少0.1元
            "\"remark\":\"单笔支付宝转账\"" .        //转账说明
            "}");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            $update['order'] = $result->$responseNode->out_biz_no;
            $update['thirdNo'] = $result->$responseNode->order_id;
            if (method_exists($class, 'updatewithorder')) {
                call_user_func_array([$class, 'updatewithorder'], [$update]);
            }
            return ['code' => 200];
        } else {
            $update['order'] = $data['cash_no'];
            $update['admin_remark'] = $result->$responseNode->sub_msg;
            if (method_exists($class, 'updatewithorder')) {
                call_user_func_array([$class, 'updatewithorder'], [$update]);
            }
            return ['code' => 103, 'msg' => $result->$responseNode->sub_msg];
        }
    }

}