<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

$config = [
    // 驱动方式
    'type' => 'redisjson',
    // 缓存保存目录
    'path' => '',
    // 缓存前缀
    'prefix' => 'cache:',
    // 缓存有效期 0表示永久缓存
    'expire' => 0,
    'host' => '',
    'port' => '',
    'password' => '',
    'select' => '',
    'persistence' => true
];
use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/cache.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;
