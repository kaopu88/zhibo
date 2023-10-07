<?php

namespace app\h5\controller;

use bxkj_common\RedisClient;
use think\Db;
use think\Exception;
use think\facade\Request;

class LoginController extends Controller
{
    protected $config;
    protected $token;
    protected $data;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('app.live_setting.user_live');
        $this->token = Request::param('token');
        try {
            if (empty($this->token)) throw new Exception('非法操作~', 1);
            $redis = new RedisClient();
            $result = $redis->get("access_token:{$this->token}");
            if (empty($result)) throw new Exception('无效请求',1);
            $this->data = empty($result) ? array() : json_decode($result, true);
            $userService = new \app\common\service\User();
            $checkRes = $userService->checkLogin($this->data['user'], 'android');
            if ($checkRes === false) {
                $error = $userService->getError();
                $errorMsg = $error->getMessage();
                $errorMsg = $errorMsg ? $errorMsg : '登录失效';
                throw new Exception($errorMsg,1);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    protected function jsonSuccess($data, $msg = '')
    {
        return json(array(
            'code' => 0,
            'data' => $data,
            'msg'  => $msg
        ));
    }

    //错误返回
    protected function jsonError($msg, $code = 1, $data = null)
    {
        $message = '系统繁忙~';
        if (is_string($msg)) {
            $message = $msg;
        } else if (is_error($msg)) {
            $message = $msg->getMessage();
            $code    = $msg->getStatus();
        }
        $obj = array(
            'code' => $code,
            'msg'  => $message
        );
        if (isset($data)) $obj['data'] = $data;
        return json($obj);
    }
}
