<?php

namespace bxkj_recommend\model;

use bxkj_recommend\ProRedis;

class Model
{
    protected $redis;
    protected $data = [];
    protected $error;

    public function __construct()
    {
        $this->redis = ProRedis::getInstance();
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function getData()
    {
        return $this->data;
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