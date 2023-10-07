<?php

namespace app\core\controller;

use bxkj_common\ClientInfo;
use bxkj_module\controller\Api;
use think\facade\Request;

class Controller extends Api
{
    protected $allows = array(
        "open" => array('__un__'),
        "pay_callback" => array('__un__'),
        "callback" => array('__un__'),
        "video_callback" => array('__un__'),
    );

    public function __construct()
    {
        parent::__construct();

        //正式环境需要验证IP
        if (RUNTIME_ENVIROMENT == 'pro' && !is_allow($this->allows))
        {
            $allowIps = ['127.0.0.1','43.132.175.146'];

            $ip = Request::ip();

            if (!in_array($ip, $allowIps)) {
                echo 'no access allowed';
                exit();
            }
        }

        $client_seri = input('client_seri');

        if (!empty($client_seri)) ClientInfo::refreshByParams(['client_seri' => $client_seri]);
    }
}
