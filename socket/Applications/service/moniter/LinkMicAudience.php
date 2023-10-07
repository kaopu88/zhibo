<?php

namespace app\service\moniter;


class LinkMicAudience extends LinkMic
{

    public static function run(array $params)
    {
        global $redis, $db;

        $sql = "SELECT id FROM ".TABLE_PREFIX."link_mic_log WHERE status=1 AND `room_id`={$params['room_id']} LIMIT 1";

        $res = $db->query($sql);

        if (empty($res)) return;

        $key = 'BG_LIVE:'.$params['room_id'].':linkMic'.$res[0]['id'].'audience';

        $redis->incr($key);

        return true;
    }

}