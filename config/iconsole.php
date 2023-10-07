<?php

$config = [
    'record_type' => 'file',//记录方式 file db
    'log_record' => false,
    'log_path' => '',//文件记录位置
    'log_db' => '',
    'log_level' => 'EMERG,ALERT,CRIT,ERR',//记录级别
    'log_types' => array('info', 'error', 'warning'),
    'push_types' => array('info', 'error', 'warning'),//推送级别
    'push_url' => 'http://console.com:8084',//推送地址
];
use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/iconsole.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;
