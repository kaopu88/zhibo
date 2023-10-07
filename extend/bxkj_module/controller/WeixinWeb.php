<?php

namespace bxkj_module\controller;

use bxkj_module\service\WxApi;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\Response;

class WeixinWeb extends Web
{
    protected $wxAppId;
    protected $wxAppSecret;
    protected $openId;
    protected $accessToken;

    public function __construct()
    {
        parent::__construct();
        $wx = config('app.media_platform.wx_wap');
        $this->wxAppId = $wx['app_id'];
        $this->wxAppSecret = $wx['secret_key'];
        $accessTokenInfo = $this->getAccessToken(false);
        if ($accessTokenInfo) {
            $this->openId = $accessTokenInfo['openid'] ? $accessTokenInfo['openid'] : '';
            $this->accessToken = $accessTokenInfo['access_token'] ? $accessTokenInfo['access_token'] : '';
        }
    }

    //分配jsapi配置
    protected function assignJsapiConfig($self = null)
    {
        $self = isset($self) ? $self : Request::url();
        $wxApi = new WxApi();
        $accessToken = $wxApi->getAccessTokenBySession();
        $jsapiConfig = $wxApi->makeJsapiConfig($accessToken, $self);
        $this->assign('jsapi_config', json_encode($jsapiConfig));
    }

    //获取access_token
    protected function getAccessToken($onlyToken = true)
    {
        $wxApi = new WxApi();
        $accessToken = $wxApi->getAccessTokenBySession($onlyToken);
        return $accessToken;
    }

    //获取当前会话中的微信用户信息
    protected function getWxUserInfo()
    {
        $wxApi = new WxApi();
        $name = WxApi::WX_USER_INFO_KEY;
        $user = session($name);
        if ($user) return $user;
        $accessTokenRes = $this->getAccessToken(false);
        if (!$accessTokenRes) return false;
        $user = $wxApi->getUserInfo($accessTokenRes['access_token'], $accessTokenRes['openid']);
        if (!$user) return false;
        return $user;
    }

    //引导用户网页授权
    protected function authorize($stateData = '', $redirectUri = null)
    {
        $wxApi = new WxApi();
        if (is_array($stateData)) {
            $stateData['callback'] = $redirectUri;//二级网关地址
        }
        $authorizeUrl = $wxApi->makeAuthorizeUrl($stateData);
        if (Request::isAjax()) {
            $response = Response::create(['status' => 0, 'url' => $authorizeUrl, 'message' => '微信授权'], 'json');
        } else {
            $response = Response::create($authorizeUrl, 'redirect', 302);
        }
        throw new HttpResponseException($response);
    }


}
