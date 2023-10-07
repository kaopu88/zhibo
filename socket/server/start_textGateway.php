<?php 
/**
* $Author: Hui Yang $
*/

use GatewayWorker\Gateway;
use Workerman\Worker;

// 自动加载类
require_once ROOT_PATH . '/vendor/autoload.php';

// #### 内部推送端口(假设当前服务器内网ip为192.168.100.100) ####
// #### 端口不能与原来start_gateway.php中一样 ####
$internal_gateway = new Gateway("text://0.0.0.0:8191");

$internal_gateway->name = 'internalGateway';

// #### 不要与原来start_gateway.php的一样####
// #### 比原来跨度大一些，比如在原有startPort基础上+1000 ####
$internal_gateway->startPort = 7100;

// #### 这里设置成与原start_gateway.php 一样 ####
$internal_gateway->registerAddress = '127.0.0.1:5010';

// #### 内部推送端口设置完毕 ####
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

