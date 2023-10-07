<?php


use GatewayWorker\BusinessWorker;
use Workerman\Lib\Timer;
use Workerman\Worker;

// 自动加载类
require_once ROOT_PATH . '/vendor/autoload.php';

// bussinessWorker 进程
$BroadCast = new BusinessWorker();

$BroadCast->eventHandler = 'app\\Events';

// worker名称
$BroadCast->name = 'liveRoomBroadCast';

// bussinessWorker进程数量
$BroadCast->count = 1;

// 服务注册地址
$BroadCast->registerAddress = '127.0.0.1:5010';

$BroadCast->onWorkerStart = function ($worker) {

    $redis = new \Redis();

    $redis->connect(REDIS_HOST,REDIS_PORT);

    $redis->auth(REDIS_AUTH);

    $redis->select(REDIS_DB);

    Timer::add(2, function () use ($redis){

        $data = $redis->rpop('broadCasts');

        if (!empty($data))
        {
            \GatewayWorker\Lib\Gateway::sendToAll($data);
        }
    });

};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
