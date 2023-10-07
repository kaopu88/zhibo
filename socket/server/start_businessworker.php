<?php
/**
 * $Author: Hui Yang $
 */

use GatewayWorker\BusinessWorker;
use Workerman\Worker;

global $config;

// 自动加载类
require_once ROOT_PATH . '/vendor/autoload.php';

// bussinessWorker 进程
$worker = new BusinessWorker();

$worker->eventHandler = 'app\\Events';

// worker名称
$worker->name = 'liveRoomWorker';

if ($config['logger'])
{
    if (!is_dir(ROOT_PATH.'/log/')) mkdir(ROOT_PATH.'/log', 0777, true);

    BusinessWorker::$stdoutFile = ROOT_PATH.'/log/access.log';
}


// bussinessWorker进程数量
$worker->count = 6;

// 服务注册地址
$worker->registerAddress = '127.0.0.1:5010';

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}


