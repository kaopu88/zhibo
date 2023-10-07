<?php

namespace bxkj_push\drivers;

class PushDriver
{
    protected $config;
    protected $error;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function setError($message = '', $code = 1)
    {
        $this->error = is_error($message) ? $message : make_error($message, $code);
        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    //安卓单播
    public function androidTo($msgData)
    {
    }

    //IOS单播
    public function iosTo($msgData)
    {
    }


}