<?php

//session名
define('SESSION_NAME', 'hywebsess:');
//session_id
define('VAR_SESSION_ID', 'hywebsess');
//名称
define('NAME', 'hywebsess');
//数据加密盐值
define('DATA_AUTH', 'bx_system_data_auth');
//Token加密盐值
define('DATA_TOKEN', 'bx_system_data_token');
//定时器加密盐值
define('TIMER_TOKEN', 'bx_system_timer_token');
//前端初始化安全key
define('APP_SECRET_KEY', '2bc29158f230db6c2a7a6712e57de6e4b48116f2');
//管理后台传送公会后台签名
define('TRANSFER_AGENT_KEY', '');
//存储空间域名
define('STORAGE_URL', '');
//本地协议域名
define('LOCAL_PROTOCOL_DOMAIN', 'bx://router.bxtv.com/');
//用户id起始值
define('USER_ID_START', 100000);
//内部回环地址
define('LOOP_BACK_URL', 'http://127.0.0.1/');
//全局地址
define('DOMAIN_URL', 'http://'.$_SERVER['HTTP_HOST']);
//支付签名token
define('PAY_VERIFY_TOKEN', '');
//授权浏览器来源规则
define('BX_USER_AGENT', 'Hywebsess');

require ROOT_PATH . '/thinkphp/base.php';