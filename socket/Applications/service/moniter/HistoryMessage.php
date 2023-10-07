<?php

namespace app\service\moniter;


class HistoryMessage
{

    //存入redis50条消息
    public static function run(array $params)
    {
        global $redis;

        $message_key = 'BG_LIVE:'.$params['room_id'].':message';

        if ($redis->llen($message_key) > 50) $redis->lpop($message_key);

        $redis->rpush($message_key, $params['message']);

        return true;
    }


}