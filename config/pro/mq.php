<?php
return [
    'host' => '127.0.0.1',
    'port' => 5672,
    'user' => 'admin',
    'password' => '123456',
    'vhost' => 'my_vhost',
    'prefix' => 'bxkj',
    'main_threads' => 100,//进程数量
    'debug' => true,
    'processes' => [
        //主进程
        'main' => [
            'threads' => null,
            'exchanges' => 'main'//搭载的交换机
        ],
    ],
];