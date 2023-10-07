<?php

use \think\facade\Env as Env;

$config = [
    'lottery_is_open' => '0',
    'lottery_desc' => '',
    'red_packet_is_open' => '0',
    'red_packet_desc' => '',
    'lottery_egg_is_open' => '0',
    'lottery_egg_bean' => '500',
    'lottery_egg_desc' => '',
];


$env = Env::get('RUN_ENV');

if (!empty($env)) {
    $path = ROOT_PATH . 'config/' . $env . '/activity.php';

    if (file_exists($path)) {
        $env_config = require_once $path;
        $config = array_merge($config, $env_config);
    }
}

return $config;
