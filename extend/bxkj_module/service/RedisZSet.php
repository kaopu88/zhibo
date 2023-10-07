<?php

namespace bxkj_module\service;

use bxkj_common\RedisClient;

class RedisZSet extends Service
{
    protected $prefix;

    public function __construct()
    {
        parent::__construct();
    }

    public function inSet($selfId, $toId)
    {
    }


    public function rebuild()
    {
    }
}