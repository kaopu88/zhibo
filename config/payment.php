<?php
$config = [
    //支付配置
    'pay_verify_token' => '',
    'payment_base' => '',
    'alipay_aop_sdk_work_dir' => \think\facade\Env::get('runtime_path') . 'alipay/',//SDK缓存目录
    'alipay_aop_sdk_dev_mode' => true,//SDK调试模式
    'alipay_sdk_path' => ROOT_PATH . 'extend/alipay',//支付宝SDK位置
    'wxpay_sdk_path' => ROOT_PATH . 'extend/wxsdk/',//微信支付SDK位置
    'aomypay' => [],//默认官方支付方式
];


use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/payment.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;