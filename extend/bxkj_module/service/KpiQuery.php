<?php

namespace bxkj_module\service;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class KpiQuery extends Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function getCache($prefix, $unit, $num)
    {
        $num = trim($num ? str_replace('-', '', $num) : '');
        $redis = RedisClient::getInstance();
        $key = "kcache:{$prefix}:{$unit}:{$num}";
        $value = $redis->get($key);
        if ($value === false) return null;
        return $value;
    }

    public static function setCache($prefix, $unit, $num, $value)
    {
        $num = trim($num ? str_replace('-', '', $num) : '');
        $key = "kcache:{$prefix}:{$unit}:{$num}";
        //$his = self::alreadyHistory($unit, $num);
        $timeout = 0;
        //if ($his) {
            if ($unit == 'd') {
                $timeout = 6;
            } else if ($unit == 'w') {
                $timeout = 45;
            } else if ($unit == 'm') {
                $timeout = 360;
            } else if ($unit == 'f') {
                $timeout = 300;
            }
        //}
        $redis = RedisClient::getInstance();
        if ($timeout > 0) {
            return $redis->set($key, (string)$value, $timeout);
        }
        return $redis->set($key, (string)$value);
    }

    protected static function alreadyHistory($unit, $num)
    {
        if ($unit == 'd') {
            $now = date('Ymd');
        } else if ($unit == 'm') {
            $now = date('Ym');
        } else if ($unit == 'f') {
            $now = DateTools::getFortNum();
        } else if ($unit == 'y') {
            $now = date('Y');
        } else if ($unit == 'w') {
            $now = DateTools::getWeekNum();
        }
        return (int)$now > (int)$num;
    }

    public static function setTimeRange(&$db, $unit, $num)
    {
        $num = $num ? str_replace('-', '', $num) : '';
        if (!empty($num)) {
            if ($unit == 'm') {
                $db->where('month', $num);
            } else if ($unit == 'f') {
                $db->where('fnum', $num);
            } else if ($unit == 'd') {
                $db->where('day', $num);
            } else if ($unit == 'w') {
                $db->where('week', $num);
            }
        }
    }


    public static function clearCache($prefix, $time)
    {
        $month = date('Ym', $time);
        $day = date('Ymd', $time);
        $fnum = DateTools::getFortNum($time);
        $week = DateTools::getWeekNum($time);
        $redis = RedisClient::getInstance();
        $redis->del("kcache:{$prefix}:m:{$month}");
        $redis->del("kcache:{$prefix}:d:{$day}");
        $redis->del("kcache:{$prefix}:f:{$fnum}");
        $redis->del("kcache:{$prefix}:w:{$week}");
    }

}