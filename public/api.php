<?php
namespace think;

if(isset($_GET['service'])){
    $_GET['s']='api/'.str_replace('.','/',$_GET['service']);
    unset($_GET['service']);
}else{
    $_GET['s']='api/'.str_replace('.','/',$_GET['s']);
}

defined('ROOT_PATH') || define('ROOT_PATH', __DIR__.'/../');

require 'init.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
$APP = Container::get('app')->run();

/*if (RUNTIME_ENVIROMENT != 'pro')
{
    $data = $APP->getData();

    bxkj_console(['api' => $data]);
}*/

$APP->send();