<?php

namespace app\h5\controller;

use bxkj_module\controller\WeixinWeb;
use bxkj_module\service\WxApi;
use bxkj_common\HttpClient;

class Weixin extends WeixinWeb
{
    //授权回调页面(总网关)
    public function authorize_callback()
    {
        /*$whitelist = [
            'v1.live.libx.com.cn',
        ];*/
        $code = input('code');
        $state = input('state');
        if (empty($state)) return $this->errorTip('场景值不存在');
        $wxApi = new WxApi();
        $stateData = $wxApi->getState($state);
        if (empty($stateData)) return $this->errorTip('场景值不存在');
        $callback = $stateData['callback'];
        if (empty($callback)) return $this->errorTip('回调地址错误');
        $host = strtolower(parse_url($callback, 1));
        //if (!in_array($host, $whitelist)) return $this->errorTip('回调域名不合法');
        $query = http_build_query(['code' => $code, 'state' => $state]);
        list($base, $throw) = explode('?', $callback);
        $url = $base . '?' . $query;
        return redirect($url);
    }

    //错误提示
    private function errorTip($message)
    {
        $state = input('state');
        $this->assign('message', $message);
        $this->assign('title', '授权失败');
        $this->assign('state', $state);
        return $this->fetch('authorize_callback');
    }

    public function retry()
    {
        $state = input('state');
        if (!empty($state) && validate_regex($state, 'number')) {
            return $this->authorize($state ? $state : '');
        }
    }

    //二级回调网关
    public function authorize_callback2()
    {
        $code = input('code');
        $state = input('state');
        if (empty($state)) return $this->errorTip('场景值不存在');
        $config = config('app.media_platform.wx_wap');
        $APPID = $config['app_id'];
        $SECRET = $config['secret_key'];
        $getAccessTokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$APPID}&secret={$SECRET}&code={$code}&grant_type=authorization_code";
        $curl = new HttpClient();
        $result = $curl->setCA(false)->get($getAccessTokenUrl, '', 5)->getData('json');
        if (!$result) return $this->errorTip('获取用户授权信息失败');
        if (!empty($result['errcode'])) return $this->errorTip($result['errmsg']);
        $wxApi = new WxApi();
        $stateData = $wxApi->getState($state);
        if (empty($stateData)) return $this->errorTip('场景值不存在');
        $authType = isset($stateData['auth_type']) ? $stateData['auth_type'] : 'login';
        $name = WxApi::WX_ACCESS_TOKEN_KEY;
        if ($authType == 'login') {
            $result['auth_time'] = time();
            $result['refresh_time'] = time();
            session($name, $result);
        }
        $redirect = empty($stateData['redirect']) ? '/' : $stateData['redirect'];
        return redirect($redirect);
    }


}
