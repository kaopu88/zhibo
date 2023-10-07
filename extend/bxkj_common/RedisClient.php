<?php

namespace bxkj_common;



use think\facade\Config;

class RedisClient extends \Redis
{
    protected $error;
    protected static $clients = array();

    public function __construct($config = null)
    {
        parent::__construct();
        empty($config) && $config = Config::pull('redis');
        $host = $config['host'];
        $port = $config['port'];
        $db = $config['db'];
        $auth = $config['auth'];
        $timeout = isset($config['timeout']) ? $config['timeout'] : 0;
        $persistence = $config['persistence'];
        $db = $db ? $db : 0;
        $auth = $auth ? $auth : '';
        $timeout = $timeout ? $timeout : 5;
        if ($persistence) {
            $this->pconnect($host, $port, (int)$timeout);
        } else {
            $this->connect($host, $port, (int)$timeout);
        }
        if (!empty($auth)) $this->auth($auth);
        $this->select($db);
    }

    public function getError()
    {
        return $this->error;
    }

    //获得实例
    public static function getInstance($name = 'app')
    {
        if (isset($name))
        {
            if (!isset(self::$clients[$name]))
            {
                self::$clients[$name] = new RedisClient();
            }
            return self::$clients[$name];
        }
        return new RedisClient();
    }

    public function getSZList($key, $order = 'asc', $offset = 0, $length = null, $options = null, &$members = [])
    {
        $start = $offset;
        $stop = isset($length) ? ($offset + $length - 1) : -1;
        if ($order == 'asc') {
            $ranks = $this->zRange($key, $start, $stop, true);
        } else {
            $ranks = $this->zRevRange($key, $start, $stop, true);
        }
        if (isset($options)) {
            $list = [];
            foreach ($ranks as $member => $score) {
                $item = [];
                $item[$options['member']] = $member;
                $item[$options['score']] = $score;
                $list[] = $item;
                $members[] = $member;
            }
            return $list;
        }
        return $ranks;
    }

    public function getSZTotal($key)
    {
        return $this->zCount($key, '-inf', '+inf');
    }


}