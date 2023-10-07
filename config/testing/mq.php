<?php
return [
    'host' => '127.0.0.1',
    'port' => 5672,
    'user' => 'admin',
    'password' => 'Xu8244288*',
    'vhost' => '/',
    'prefix' => 'aomy',
    'main_threads' => 3,//进程数量
    'debug' => true,
    'processes' => [
        //主进程
        'main' => [
            'threads' => null,
            'exchanges' => 'main'//搭载的交换机
        ],
    ],
];