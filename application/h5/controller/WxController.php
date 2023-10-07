<?php

namespace app\h5\controller;

use bxkj_module\service\WxApi;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\Response;

class WxController extends \bxkj_module\controller\WeixinWeb
{
    //登录检查白名单方法
    protected $allows = array(
        "weixin" => array("authorize_callback", 'retry', 'authorize_callback2'),
        "invite" => array("index", "reg", "test"),
        "recharge" => array('get_user_info', 'pay_order', 'index', 'wxwapquery','pay_type','paymentid')
    );

    public function __construct()
    {
        parent::__construct();

        if ((!$this->openId || !$this->accessToken) && !is_allow($this->allows)) {
            $this->authorize(['auth_type' => 'login', 'redirect' => Request::url()]);
        }
    }

    //引导用户网页授权
    protected function authorize($stateData = '', $redirectUri = null)
    {
        $site = config('site.');
        $type = isset($site['register_type']) ? $site['register_type']: 0;
        if ($type != 2) {
            if (!isset($redirectUri)) $redirectUri = H5_URL . '/weixin/authorize_callback2';
            return parent::authorize($stateData, $redirectUri);
        }
    }
}
