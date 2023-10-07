<?php 
/**
* $Author: Hui Yang $
*/

use \Workerman\Worker;
use \GatewayWorker\Register;

// 自动加载类
require_once ROOT_PATH . '/vendor/autoload.php';

// register 服务必须是text协议
$register = new Register('text://0.0.0.0:5010');

$register->name = 'liveRoomRegister';

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
