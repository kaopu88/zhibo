<?php

namespace bxkj_recommend;


class Base
{
    protected $redis;
    protected $error;

    public function __construct()
    {
        $this->redis = ProRedis::getInstance();
    }

    public function setError($message = '', $code = 1, $data = [])
    {
        $this->error = is_error($message) ? $message : make_error($message, $code, $data);
        return false;
    }

    public function getError()
    {
        return $this->error;
    }
}