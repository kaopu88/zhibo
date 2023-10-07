<?php

$config = [
    'host' => '',
    'port' => 6379,
    'auth' => '123456',
    'db' => 0
];

use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/pro_redis.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;