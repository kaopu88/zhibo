<?php

use think\facade\Env as Env;

$config = [

    //短信配置
    'aomy_sms' => [
        'platform' => '',
        'sms_code_expire' => '',
        'sms_code_limit' => '',
        'regional' => [
            'access_id' => '',
            'access_secret' => '',
            'region' => '',
            'endpoint_name' => '',
            'sign_name' => ''
        ],
        'global' => [
            'access_id' => '',
            'access_secret' => '',
            'region' => '',
            'endpoint_name' => '',
            'sign_name' => ''
        ],
    ],
    //私信配置
    'aomy_private_letter' => [
        'app_key' => '',
        'app_secret' => '',
        'platform' => ''
    ],

    //推送配置
    'bxkj_push' => [
        'platform' => '',
        'android' =>[
            'app_key' => '',
            'message_secret' => '',
            'app_master_secret' => '',
            'default_activity' => '',
        ],
        'ios' => [
            'app_key' => '',
            'app_master_secret' => '',
        ],
        'push_delay_rate' => 1,
        'push_delay_range' => 900,
        'push_max_delay' => 3600,
        'push_section_length' => 300,
        'push_receipt_period' => 3600,
    ],

];

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/message.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;