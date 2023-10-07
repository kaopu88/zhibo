<?php

namespace bxkj_recommend;

class ProConf
{
    protected static $config = null;

    public static function has($name)
    {
        if (!isset(self::$config)) {
            self::$config = require 'config.php';
        }
        if (empty(self::$config)) return false;
        $value = self::get($name);
        return isset($value);
    }

    public static function get($name = null)
    {
        if (!isset(self::$config)) {
            self::$config = require 'config.php';
        }
        if (!$name) return self::$config;
        $nameArr = explode('.', $name);
        $tmp =& self::$config;
        $lastName = $nameArr[0];
        for ($i = 1; $i < count($nameArr); $i++) {
            if (!is_array($tmp)) return null;
            $tmp =& $tmp[$lastName];
            $lastName = $nameArr[$i];
        }
        return is_array($tmp) ? $tmp[$lastName] : null;
    }

    public static function set($name = null, $value = null)
    {
        if (!isset(self::$config)) {
            self::$config = require 'config.php';
        }
        if (!$name) {
            self::$config = is_null($value) ? [] : $value;
        } else {
            $nameArr = explode('.', $name);
            $tmp =& self::$config;
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
}