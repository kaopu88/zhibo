<?php

namespace app\service;

use app\Common;
use GatewayWorker\Lib\Gateway;

class Positon extends Common
{
    public static function setAllow(array $params)
    {
        global $redis;
        $key = 'BG_VOICE:voice_postion_type:' . $params['room_id'];
        $redis->zAdd($key, $params['type'], $params['position']);
        Gateway::sendToGroup($params['room_id'], self::genMsg('voiceSetTypePostionMsg', '修改成功', ['type' => $params['type']]));
    }

    public static function setApply(array $params)
    {
        global $redis;
        $key = 'BG_VOICE:voice_postion_type:' . $params['room_id'];
        $redis->zAdd($key, $params['type'], $params['position']);
        Gateway::sendToGroup($params['room_id'], self::genMsg('voiceSetTypePostionMsg', '修改成功', ['type' => $params['type']]));
    }

    public static function setPwd(array $params)
    {
        global $redis;
        $key = 'BG_VOICE:voice_postion_type:' . $params['room_id'];
        $redis->zAdd($key, $params['type'], $params['position']);
        Gateway::sendToGroup($params['room_id'], self::genMsg('voiceSetTypePostionMsg', '修改成功', ['type' => $params['type']]));
    }
}