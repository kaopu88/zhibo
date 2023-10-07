<?php

namespace app\core\controller;

use bxkj_common\RabbitMqChannel;
use think\facade\Request;

class Rabbitmq extends Controller
{
    public function send()
    {
        ignore_user_abort(true);
        $json = Request::post('data');
        $data = json_decode($json, true);
        if (empty($data)) return json_error('data empty');
        $rabbitChannel = new RabbitMqChannel();
        $rabbitChannel->exchange($data['exchange'])->send($data['routing_key'], $data['data'], $data['properties']['delivery_mode'], $data['properties']['expiration']);
        $rabbitChannel->close();
    }
}
