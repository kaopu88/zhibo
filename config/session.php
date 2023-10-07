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
// | 会话设置
// +----------------------------------------------------------------------

$config = [
    'id' => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    // SESSION 前缀
    'prefix' => 'think',
    // 驱动方式 支持redis memcache memcached
    'type' => 'redis',
    // 是否自动开启 SESSION
    'auto_start' => true,
    'life_time' => [
        'admin' => 0,
        'user' => 0
    ],
    'ip_auth' => 1,
    'login_auto_expire' => 1209600,//2周内自动登录
    'host' => '',
    'port' => 6379,
    'password' => '',
    'select' => '',
    'session_name' => 'hywebsess:',
    'var_session_id' => 'hywebsess',
    'name' => 'hywebsess',
    'persistence' => true
];
use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/session.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;