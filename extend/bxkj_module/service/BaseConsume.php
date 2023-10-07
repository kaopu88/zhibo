<?php

namespace bxkj_module\service;

use bxkj_module\exception\Exception;
use think\Db;

class BaseConsume extends Service
{
    protected $user;
    protected $orderType;
    protected $order;

    public function __construct(&$user)
    {
        $this->user = &$user;
    }

    public function setOrder($orderType, &$order)
    {
        $this->orderType = $orderType;
        $this->order = &$order;
        return $this;
    }
}