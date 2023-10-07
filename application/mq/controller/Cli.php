<?php

namespace app\mq\controller;

use think\facade\Request;

class Cli
{
    public function __construct()
    {
        if (!Request::isCli()) {
            echo 'unsupported mode of operation';
            exit();
        }
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('default_socket_timeout', -1);
    }
}
