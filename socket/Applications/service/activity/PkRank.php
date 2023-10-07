<?php

namespace app\service\activity;

use app\service\Activity;

/**
 * pk排位赛
 * Class PkRank
 * @package app\service\activity
 */
class PkRank extends Activity
{
    public static function run(array $params)
    {
        if ($params['pk_type'] != 'pk_rank') return true;

        global $redis;

        $conf = self::getConfig('PkRank');

        if (empty($conf)) return true;

        $now = time();

        if ($now < $conf['start_time'] || $now > $conf['end_time']) return true;

        $points_key = $conf['rule']['redis_key'].$conf['rule']['current_act'].$conf['rule']['pk_points_key'];

        //1、输赢奖励积分计算
        switch ($params['pk_res'])
        {
            case -1:
                $win_points = 10;
                $win_uid = $params['target_id'];
                $lose_points = -10;
                $lose_uid = $params['active_id'];
                break;

            case 1:
                $win_points = 10;
                $win_uid = $params['active_id'];
                $lose_points = -10;
                $lose_uid = $params['target_id'];
                break;

            default:
                $win_points = -10;
                $win_uid = $params['active_id'];
                $lose_points = -10;
                $lose_uid = $params['target_id'];
                break;
        }

        $day = date('Ymd').':';

        //2、每日pk达到指定次数奖励计算
        $active_day_pk_num_key = $conf['rule']['redis_key'].$conf['rule']['current_act'].$day.$conf['rule']['pk_num_key'].$params['active_id'];
        $target_day_pk_num_key = $conf['rule']['redis_key'].$conf['rule']['current_act'].$day.$conf['rule']['pk_num_key'].$params['target_id'];

        //当日pk场次加1
        $redis->incr($active_day_pk_num_key);
        $redis->incr($target_day_pk_num_key);
        //获取当日全部pk场次
        $active_day_pk_num = $redis->get($active_day_pk_num_key);
        $target_day_pk_num = $redis->get($target_day_pk_num_key);

        $active_points = $active_day_pk_num >= 10 ? 80 : ($active_day_pk_num >= 5 ? 30 : 0);

        $target_points = $target_day_pk_num >= 10 ? 80 : ($target_day_pk_num >= 5 ? 30 : 0);

        if ($win_uid == $params['active_id'])
        {
            $win_points += $active_points;
            $lose_points += $target_points;
        }
        else{
            $win_points += $target_points;
            $lose_points += $win_points;
        }

        //3、连胜奖励积分计算
        if ($params['pk_res'] != 0)
        {
            //胜者连胜key
            $win_key = $conf['rule']['redis_key'].$conf['rule']['current_act'].$day.$conf['rule']['pk_win_key'].$win_uid;
            $lose_key = $conf['rule']['redis_key'].$conf['rule']['current_act'].$day.$conf['rule']['pk_win_key'].$lose_uid;

            //胜者连胜加1
            $redis->incr($win_key);

            //获取胜者连胜次数
            $win_num = $redis->get($win_key);

            $win_num >= 3 && $win_points += $win_num;

            //输者连胜的次数
            $lose_num = $redis->get($lose_key);

            //4、打破对手连胜积分计算
            if ($lose_num >=3 && $lose_num <= 5)
            {
                $win_points += 30;
            }
            else if ($lose_num > 5 && $lose_num <= 9) {
                $win_points += 50;
            }else if ($lose_num >= 10) {
                $win_points += 100;
            }

            //删除输者的连胜记录
            $redis->del($lose_key);
        }

        $redis->zincrby($points_key, $win_points, $win_uid);
        $redis->zincrby($points_key, $lose_points, $lose_uid);

        if ($redis->zscore($points_key, $win_uid) < 1) $redis->zadd($points_key, 0, $win_uid);
        if ($redis->zscore($points_key, $lose_uid) < 1) $redis->zadd($points_key, 0, $lose_uid);

        return true;
    }




}