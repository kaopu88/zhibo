<?php

namespace bxkj_common;

class BaseError
{
    protected $message;//错误消息
    protected $status;//状态码
    protected $data;

    public function __construct($message = '', $status = 1, $data = [])
    {
        $this->message = $message;
        $this->status = $status;
        $this->data = $data;
    }

    public static function getInstance($message = '', $status = 1, $data = [])
    {
        return new BaseError($message, $status, $data);
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function __toString()
    {
        return $this->message;
    }

    public function toArray()
    {
        $array = array(
            'message' => $this->message,
            'status' => $this->status
        );
        if (!empty($this->data)) $array['data'] = $this->data;
        return $array;
    }
}