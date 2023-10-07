<?php

use \think\facade\Env as Env;

$config = [
    'is_open' => '0',
    'name' => '佣金',
    'commission_cash_rate' => '1',
    'commission_cash_min' => '1',
    'commission_cash_monthlimit' => '1',
    'commission_cash_on' => '0',
];

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/giftdistribute.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;
