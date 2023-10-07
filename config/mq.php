<?php

$config = [
    'host' => '127.0.0.1',
    'port' => 5672,
    'user' => 'admin',
    'password' => '123456',
    'vhost' => '/',
    'prefix' => 'cnibx',
    'main_threads' => 100,//进程数量
    'block_time' => 50000,//阻塞时长 us
    'debug' => false,
    'processes' => [],
    'exchanges' => [
        //主业务交换机
        'main' => [
            'type' => 'topic',//交换机类型
            'durable' => true,//持久化
            'auto_delete' => false,
            'prefetch_count' => 1,//流量控制参数
            'retry' => true,//是否需要重试队列
            'max_retry' => 4,
            'retry_delay' => 10 * 1000,
            'queues' => [
                //用户行为队列
                'user.behavior' => [
                    'routing_keys' => 'user.behavior.*',
                    'no_ack' => true,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\UserBehavior', 'process']
                ],
                //视频发布前置队列 向腾讯发起异步任务
                'video.create_before' => [
                    'routing_keys' => 'video.create.upload',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\VideoBefore', 'process']
                ],
                //视频异步处理完成后本地处理
                'video.create_after' => [
                    'routing_keys' => 'video.create.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\VideoProcess', 'process']
                ],
                //视频发布阶段
                'video.create_publish' => [
                    'routing_keys' => 'video.create.publish',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\VideoPublish', 'process']
                ],
                'video.delete' => [
                    'routing_keys' => 'video.delete',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\VideoDelete', 'process']
                ],
                'video.update' => [
                    'routing_keys' => 'video.update.*',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\VideoUpdate', 'process']
                ],
                'common.callbacks' => [
                    'routing_keys' => 'callback.*',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\CallbackManager', 'process']
                ],
                'gift.common' => [
                    'routing_keys' => 'gift.*',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\Gift', 'process']
                ],
                'user.credit' => [
                    'routing_keys' => 'user.credit.*',
                    'no_ack' => true,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\UserCredit', 'process']
                ],
                'prophet.vupdater' => [
                    'routing_keys' => 'prophet.vupdater.*',
                    'no_ack' => true,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\Prophet', 'vupdater']
                ],
                'prophet.recycling' => [
                    'routing_keys' => 'prophet.recycling.*',
                    'no_ack' => true,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\Recycling', 'process']
                ],
                'prophet.building' => [
                    'routing_keys' => 'prophet.building',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\Prophet', 'building']
                ],
                'user.user_data_deal' => [
                    'routing_keys' => 'user.user_data_deal.audit',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\UserDataDeal', 'audit']
                ],
                'user.user_add_book' => [
                    'routing_keys' => 'user.user_add_book.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\UserAddBook', 'process']
                ],
                'user.distribute' => [
                    'routing_keys' => 'user.distribute.process',
                    'no_ack' => true,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\UserDistribute', 'process']
                ],
                'anchor.anchor_apply_after' => [
                    'routing_keys' => 'anchor.anchor_apply_after.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\AnchorApplyAfter', 'process']
                ],
                'goods.add_goods' => [
                    'routing_keys' => 'goods.add.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\Goods', 'process']
                ],
                /*'order.tb_order' => [
                    'routing_keys' => 'tb_order.add.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\TaobaoOrder', 'process']
                ],
                'order.pdd_order' => [
                    'routing_keys' => 'pdd_order.add.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\PddOrder', 'process']
                ],
                'order.jd_order' => [
                    'routing_keys' => 'jd_order.add.process',
                    'no_ack' => false,
                    'durable' => true,
                    'callback' => ['\app\mq\callbacks\JdOrder', 'process']
                ],*/

            ]
        ]
    ]
];

use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/mq.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;