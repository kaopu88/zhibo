<?php

namespace app\service\activity;

use app\service\Activity;

/**
 * pk排位赛统计粉丝礼物量
 * Class PkRank
 * @package app\service\activity
 */
class PkRankFans extends PkRank
{
    public static function run(array $params)
    {
        if ($params['pk_type'] != 'pk_rank' || $params['type'] != 'gift') return true;

        global $redis;

        $conf = self::getConfig('PkRank');

        if (empty($conf)) return true;

        $now = time();

        if ($now < $conf['start_time'] || $now > $conf['end_time']) return true;

        $fans_key = $conf['rule']['redis_key'].$conf['rule']['current_act'].$conf['rule']['pk_fans_key'];

        $redis->zincrby($fans_key, $params['sendCost'], $params['user_id']);

        return true;
    }




}