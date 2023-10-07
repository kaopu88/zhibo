<?php

namespace app\push\controller;

use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_recommend\ProRedis;
use think\Db;

class Recycling extends Api
{
    public function process()
    {
        $type = input('type');
        if (!in_array($type, ['index', 'pool'])) return json_error('type not support');
        $rabbitChannel = new RabbitMqChannel(['prophet.recycling']);
        $rabbitChannel->send("prophet.recycling.{$type}", ['code' => uniqid() . get_ucode()], 2);
        $rabbitChannel->close();
        return json_success(1, $type . ' start');
    }

}
