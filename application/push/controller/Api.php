<?php

namespace app\push\controller;

use think\facade\Request;

class Api extends \bxkj_module\controller\Api
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function persistent()
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        ini_set('default_socket_timeout', -1);
    }

}
