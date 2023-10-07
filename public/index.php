<?php
namespace think;

defined('ROOT_PATH') || define('ROOT_PATH', __DIR__.'/../');

require 'init.php';

// 支持事先使用静态方法设置Request对象和Config对象
// 执行应用并响应
$APP = Container::get('app')->run();

/*if (RUNTIME_ENVIROMENT != 'pro')
{
    $data = $APP->getData();

    bxkj_console(['index' => $data]);
}*/

$APP->send();