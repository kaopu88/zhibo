<?php

namespace app\recharge\controller;

use bxkj_module\controller\Api;

class Controller extends Api
{
    protected $allows = array(
        "open" => array('__un__'),
        "pay_callback" => array('__un__'),
        "film_callback" => array('__un__'),
    );

    public function __construct()
    {
        $RUNTIME_ENVIROMENT = RUNTIME_ENVIROMENT;
        //正式环境需要验证IP
        if ($RUNTIME_ENVIROMENT == 'production' && !is_allow($this->allows)) {
            $allowIps = ['119.23.71.65', '119.23.62.81', '127.0.0.1', '36.57.160.62'];
            $ip = get_client_ip();
            if (!in_array($ip, $allowIps)) {
                //echo 'no access allowed';
                //exit();
            }
        }
        parent::__construct();
    }
}
