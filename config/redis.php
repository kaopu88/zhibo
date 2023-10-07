<?php

$config = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'auth' => '123456',
    'db' => 3,
    'persistence' => false
];
use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/redis.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;