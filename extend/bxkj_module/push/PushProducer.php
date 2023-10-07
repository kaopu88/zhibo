<?php

namespace bxkj_module\push;

class PushProducer
{
    protected $redis;

    public function __construct(&$redis)
    {
        $this->redis = $redis;
        if (false) $this->redis = new \Redis();
    }

    public static function getTaskKey($taskId)
    {
        return "task:push:{$taskId}";
    }
}