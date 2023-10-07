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
// | Cookie设置
// +----------------------------------------------------------------------
$config = [
    // cookie 名称前缀
    'prefix' => '',
    // cookie 保存时间
    'expire' => 0,
    // cookie 保存路径
    'path' => '/',
    // cookie 有效域名
    'domain' => '',
    //  cookie 启用安全传输
    'secure' => false,
    // httponly设置
    'httponly' => '',
    // 是否使用 setcookie
    'setcookie' => true,
];
use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/cookie.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;


