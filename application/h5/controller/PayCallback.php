<?php

namespace app\h5\controller;

use bxkj_module\service\RechargeOrder;

class PayCallback extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $post = input();
        $sign = $this->getSign($post);
        if(isset($post['pay_orderid']) && isset($post['sign']) && $sign!= $post['sign'] ){
            echo 'notify sign error';
            exit();
        }
        if (!verify_payment_notify($post) && !isset($post['pay_orderid'])) {
            echo 'notify sign error';
            exit();
        }
            
        
       
    }
    
    /**
     * 创建签名 皇嘉支付
     * @param $Md5key   秘钥
     * @param $list     参数数组
     * @return string
     */
    public function getSign($list)
    {   
        $Md5key = 'SY5VPRGRDOK9NF42JAFKEWXNWOVQK1TIL9AU4QQFJX9Q1K44V8BUCEL9TEEA0C9F305TO3BYVHHQAM5KKBMV94LSRBUCOXY14LSGEPFWG2EV9RLFWTQTVVYTMBCSE4RX';
        ksort($list);
        unset($list['attach']);
        $md5str = "";
        foreach ($list as $key => $val) {
            if (!empty($val) && $key != 'sign') {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        $md5str = rtrim($md5str, '&');
        $sign = md5($md5str . $Md5key);
        return $sign;
    }
    
    public function recharge_notify()
    {
        $post = input();
        $myfile = fopen("PayCallback.txt", "a");
        fwrite($myfile, "\r\n");
        fwrite($myfile, var_export($post,true));
        fclose($myfile);
        $rec = new RechargeOrder();
        $result = $rec->paySuccess($post);
        if (!$result) {
            $error = $rec->getError();
            echo (string)$error;
            exit();
        }
        echo 'success';
        exit();
    }
}
