<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/08
 * Time: 下午 2:08
 */

namespace app\h5\controller;

use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\HttpClient;
use think\Request;
use think\facade\Session;

class User extends Controller
{
    //登录接口
    public function login()
    {

       $rest = $_POST;
       if(!empty($rest)){
           $params   = $rest;
        $validate = new \app\h5\validate\User();
        $result   = $validate->scene('login')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $httpClient = new HttpClient();
        $para       = [
            "api_v"   => 'v2',
            'v'       => 1,
            'meid'    => mt_rand(11111111, 99999999),
            'os_name' => 'web'
        ];
        $result = $httpClient->post(API_URL . ".php?s=Common.appinit", $para)->getData('json')['data'];
        if (empty($result)) {
            return $this->jsonError("登录失败");
        }
        $paras = [
            'username'     => $params['username'],
            'password'     => $params['password'],
            'access_token' => $result['access_token'],
        ];
        $rest  = $httpClient->post(API_URL . ".php?s=Account.login", $paras)->getData('json')['data'];
        if (empty($rest)) return $this->jsonError("登录失败");
           Session::set('user_id',$rest['user_id']);
           Session::set('access_token',$result['access_token']);
           \session('access_token',$result['access_token']) ;
        return $this->successr($paras, '登录成功');
           die;
       }else{
           return $this->fetch();
       }


    }

    //成功返回 兼容返回
    protected function successr($data, $msg = '')
    {
        header('Access-Control-Allow-Origin: *');
        return $this->jsonSuccess($data, $msg);
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