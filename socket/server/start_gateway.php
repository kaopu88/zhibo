<?php

use GatewayWorker\Gateway;
use Workerman\Worker;

// 自动加载类
require_once ROOT_PATH . '/vendor/autoload.php';

global $config;

if ($config['is_ssl'])
{
    $gateway = new Gateway("websocket://0.0.0.0:5565", [$config['ssl']]);
    $gateway->transport = 'ssl';
}
else{
    $gateway = new Gateway("websocket://0.0.0.0:5565");
}

// 设置名称，方便status时查看
$gateway->name = 'liveRoomGateway';

// 设置进程数，gateway进程数建议与cpu核数相同
$gateway->count = 4;

// 分布式部署时请设置成内网ip（非127.0.0.1）
$gateway->lanIp = '127.0.0.1';

// 内部通讯起始端口，假如$gateway->count=4，起始端口为6000
// 则一般会使用6000 6001 6002 6003 4个端口作为内部通讯端口
// BusinessWorker 进程连接端口
$gateway->startPort = 6100;

// 心跳间隔
$gateway->pingInterval = 55;

// 心跳数据
$gateway->pingData = '';

//为0不需要回复，否则需要客户端回复
$gateway->pingNotResponseLimit = 1;

// 服务注册地址
$gateway->registerAddress = '127.0.0.1:5010';

$gateway->sendToWorkerBufferSize = 512000000;

$gateway->sendToClientBufferSize = 102400000;

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

