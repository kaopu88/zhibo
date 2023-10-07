<?php

namespace bxkj_recommend;

use bxkj_recommend\exception\Exception;

class ProRedis extends \Redis
{
    protected static $redisClient;
    const PREFIX = '';

    public function __construct($config = null)
    {
        parent::__construct();
        $redisConfig = config('pro_redis.');
        $host = $redisConfig['host'];
        $port = $redisConfig['port'];
        $db = $redisConfig['db'];
        $auth = $redisConfig['auth'];
        $timeout = isset($redisConfig['timeout'])?$redisConfig['timeout']:0;
        $timeout = $timeout ? $timeout : 5;
        //相同地址不同库 connect和pconnect冲突
        if ($redisConfig['persistence']) {
            $this->pconnect($host, $port, (int)$timeout);
        } else {
            $this->connect($host, $port, (int)$timeout);
        }
        if (!empty($auth)) $this->auth($auth);
        $this->select($db);
    }

    public static function getInstance($name = '')
    {
        if (!isset(self::$redisClient)) {
            self::$redisClient = new ProRedis();
        }
        return self::$redisClient;
    }

    public static function genKey($str)
    {
        $prefix = self::PREFIX;
        return "{$prefix}{$str}";
    }

    public static function nxLock($lockName)
    {
        $redis = ProRedis::getInstance();
        $key = self::genKey("lock:{$lockName}");
        $totalSleep = 0;
        while (!$redis->setnx($key, time())) {
            if (isset($timeout)) {
                if ($totalSleep > 1000000 * $timeout)
                    throw new Exception("wait unlock {$lockName} timeout", 1);
            }
            usleep(500);//休眠0.5毫秒
            $totalSleep += 500;
        }
    }

    public static function nxUnlock($lockName)
    {
        $redis = ProRedis::getInstance();
        $key = self::genKey("lock:{$lockName}");
        $redis->del($key);
    }

    public function repair($key, $maxMembers = null, $minScore = null, $order = 'asc')
    {
        $num = 0;
        $num2 = 0;
        if (isset($minScore)) {
            $num2 = $this->zRemRangeByScore($key, '-inf', $minScore);
        }
        if (isset($maxMembers)) {
            $count = $this->zCount($key, '-inf', '+inf');
            if ($count > $maxMembers) {
                if (strtolower($order) == 'asc') {
                    $num = $this->zRemRangeByRank($key, 0, $count - $maxMembers - 1);
                } else {
                    $num = $this->zRemRangeByRank($key, $maxMembers, -1);
                }
            }
        }
        return $num + $num2;
    }

    public function repairRedundancy($key, $maxMembers, $redNum, $order = 'asc')
    {
        $int = mt_rand(0, 100);
        if ($int < 30) return 0;
        $count = $this->zCount($key, '-inf', '+inf');
        $num = 0;
        if ($count > ($maxMembers + $redNum)) {
            if (strtolower($order) == 'asc') {
                $num = $this->zRemRangeByRank($key, 0, $count - $maxMembers - 1);
            } else {
                $num = $this->zRemRangeByRank($key, $maxMembers, -1);
            }
        }
        return $num;
    }

}