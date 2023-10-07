<?php
use \think\facade\Env as Env;

$config = [
    'is_open' => '0',
    'desc' => ''
];


$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/medal.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;
