<?php

namespace app\service;


final class Monitor
{
    protected $event = [];

    protected static $instance;

    private static $portal = 'run';


    public static function init()
    {
        if (!self::$instance instanceof self) self::$instance = new self();

        return self::$instance;
    }


    public static function import(array $config=[])
    {
        if (!empty($config))
        {
            foreach ($config as $event => $callback)
            {
                !empty($callback) && self::register($event, $callback);
            }
        }
    }



    public static function register($event, $callback)
    {
        if (array_key_exists($event, self::$instance->event)) return true;

        self::$instance->event[$event] = $callback;
    }



    public static function remover()
    {


    }



    public static function listen($event, $params = null, $once = false)
    {
        $results = [];

        $tags = self::get($event);

        if (empty($tags)) return '';

        foreach ($tags as $key => $name)
        {
            $results[$key] = self::trigger($name, $params);

            if (false === $results[$key] || (!is_null($results[$key]) && $once)) {
                break;
            }
        }

        return $once ? end($results) : $results;
    }



    public static function trigger($event, $params)
    {
        if ($event instanceof \Closure || is_array($event)) {
            $method = $event;
        } else {
            $method = [$event, self::$portal];
        }

        try {
            $res = call_user_func_array($method, [$params]);
        } catch (\Exception $e) {
            $res = true;
            Logger::info($e->getMessage(), 'monitor');
        }

        return $res;
    }



    public static function get($tag = '')
    {
        if (empty($tag)) return self::$instance->event;

        return array_key_exists($tag, self::$instance->event) ? self::$instance->event[$tag] : [];
    }





}