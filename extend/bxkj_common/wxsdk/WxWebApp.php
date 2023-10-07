<?php

namespace bxkj_common\wxsdk;
class WxWebApp extends WxApp
{
    const SNSAPI_BASE = 'snsapi_base';
    const SNSAPI_USERINFO = 'snsapi_userinfo';
    const ACCESS_TOKEN_KEY = 'wx_access_token_info';



    public function getUserInfoAuth()
    {
        $accessTokenInfo = session('?' . self::ACCESS_TOKEN_KEY) ? session(self::ACCESS_TOKEN_KEY) : null;
        if (isset($accessTokenInfo)) {
            //access_token已经过期
            if (($accessTokenInfo['expires_in'] - 5) + $accessTokenInfo['refresh_time'] < time()) {
                $this->refreshAccessToken();
            }
            //refresh_token已经过期
            if ((2592000 - 30) + $accessTokenInfo['auth_time'] < time()) {

            }
        }
    }

    //生成网页授权URL
    public function makeAuthorizeUrl($redirect_uri = null, $scope = null, $state = '', $response_type = 'code')
    {
        $redirect_uri = isset($redirect_uri) ? $redirect_uri : 'http://' . rtrim(ENV_BASE_URL, '/') . __SELF__;
        $redirect_uri = preg_match('/\?/', $redirect_uri) ? $redirect_uri . '&_wxauth=1' : $redirect_uri . '?_wxauth=1';
        $scope = isset($scope) ? $scope : self::SNSAPI_USERINFO;
        $state = $state ? urlencode($state) : $state;
        $redirect_uri = 'http://h5.20jie.net/weixin/authorize_callback?redirect_uri=' . urlencode($redirect_uri);
        $redirect_uri = urlencode($redirect_uri);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirect_uri}&response_type={$response_type}&scope={$scope}&state={$state}#wechat_redirect";
        return $url;
    }

    //引导用户网页授权
    public function bootAuthorize($redirect_uri = null, $scope = null, $state = '', $response_type = 'code')
    {
        $url = $this->makeAuthorizeUrl($redirect_uri, $scope, $state, $response_type);
        redirect($url);
    }

    /*
     * 检查access_token
     * 没有则引导授权
     * 过期则刷新access_token
     */
    public function checkAccessToken()
    {
        $accessTokenInfo = session('?access_token_info') ? session('access_token_info') : null;
        if (isset($accessTokenInfo)) {
            //access_token已经过期
            if (($accessTokenInfo['expires_in'] - 5) + $accessTokenInfo['refresh_time'] < time()) {
                $this->refreshAccessToken();
            }
            //refresh_token已经过期
            if ((2592000 - 30) + $accessTokenInfo['auth_time'] < time()) {
            }
        }


    }

    //凭code获取access_token
    public function getAccessToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        $result = $this->curlClient->get($url)->getData();
        if ($result === false) {
            return $this->setError($this->curlClient->getError());
        }
        return $result;
    }

    //凭code获取userinfo
    public function getUserInfoByCode($code)
    {
        $result = $this->getAccessToken($code);
        if (!$result) return false;
        $user = $this->getUserInfo($result['access_token'], $result['openid']);
        if (!$user) return false;
        return $user;
    }

    //获取用户信息
    public function getUserInfo($accessToken, $openId)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
        $result = $this->curlClient->get($url)->getData();
        if ($result === false) {
            return $this->setError($this->curlClient->getError());
        }
        return $result;
    }

    //刷新access_token
    public function refreshAccessToken($refreshToken)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->appId}&grant_type=refresh_token&refresh_token={$refreshToken}";
        $result = $this->curlClient->get($url)->getData();
        if ($result === false) {
            return $this->setError($this->curlClient->getError());
        }
        return $result;
    }

    //获取jsapi ticket
    public function getJsApiTicket($accessToken)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$accessToken}&type=jsapi";
        $result = $this->curlClient->get($url)->getData();
        if ($result === false) {
            return $this->setError($this->curlClient->getError());
        }
        return $result;
    }
}