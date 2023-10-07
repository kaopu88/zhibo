<?php

namespace app\common\service;

use bxkj_common\RedisClient;

class DsSession
{
    protected static $isRestore = false;
    protected static $data = [];
    protected static $access_token = '';

    //恢复会话数据
    public static function restore($access_token)
    {
        if (empty($access_token)) return false;
        $redis = RedisClient::getInstance();
        $result = $redis->get("access_token:{$access_token}");
        if ($result === false) return false;
        $data = empty($result) ? array() : json_decode($result, true);
        self::$data = $data;
        self::$access_token = $access_token;
        self::$isRestore = true;
        return true;
    }

    public static function isRestore()
    {
        return self::$isRestore;
    }

    //初始化
    public static function init($data)
    {
        $access_token = sha1(uniqid() . get_ucode(8, 'a1A'));
        $data['access_token'] = $access_token;
        self::$data = $data;
        self::$access_token = $access_token;
        return $access_token;
    }

    public static function has($name)
    {
        if (empty(self::$data)) return false;
        $value = self::get($name);
        return isset($value);
    }

    public static function get($name = null)
    {
        if (!$name) return self::$data;
        $nameArr = explode('.', $name);
        $tmp =& self::$data;
        $lastName = $nameArr[0];
        for ($i = 1; $i < count($nameArr); $i++) {
            if (!is_array($tmp)) return null;
            $tmp =& $tmp[$lastName];
            $lastName = $nameArr[$i];
        }
        if (!isset($tmp[$lastName])) return null;
        return is_array($tmp) ? $tmp[$lastName] : null;
    }

    public static function set($name = null, $value = null)
    {
        if (!$name) {
            self::$data = is_null($value) ? [] : $value;
        } else {
            $nameArr = explode('.', $name);
            $tmp =& self::$data;
            $lastName = $nameArr[0];
            for ($i = 1; $i < count($nameArr); $i++) {
                if (!is_array($tmp)) return false;
                $tmp =& $tmp[$lastName];
                $lastName = $nameArr[$i];
            }
            if (!is_array($tmp)) return false;
            if (is_null($value)) {
                unset($tmp[$lastName]);
            } else {
                $tmp[$lastName] = $value;
            }
        }
    }

    public static function save($access_token = null)
    {
        $access_token = isset($access_token) ? $access_token : self::$access_token;
        if (!empty($access_token) && !empty(self::$data)) {
            $json = json_encode(self::$data);
            $redis = RedisClient::getInstance();
            $key = "access_token:{$access_token}";
            $res = $redis->set($key, $json);
            if ($res) {
                $redis->expire($key, 30 * 86400);
            }
        }
    }

}