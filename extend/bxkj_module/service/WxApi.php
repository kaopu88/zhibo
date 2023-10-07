<?php

namespace bxkj_module\service;

use bxkj_common\CoreSdk;
use bxkj_common\HttpClient;
use think\Db;
use think\facade\Request;

class WxApi extends Service
{
    const WX_USER_INFO_KEY = 'wx_user_info';
    const WX_ACCESS_TOKEN_KEY = 'wx_access_token';

    protected $httpClient;
    protected $appId;
    protected $appSecret;

    public function __construct()
    {
        parent::__construct();
        $this->httpClient = new HttpClient([
            'format' => 'json'
        ]);
        $config = config('app.media_platform.wx_wap');
        $this->appId = $config['app_id'];
        $this->appSecret = $config['secret_key'];
    }

    //凭code获取access_token
    public function getAccessTokenByCode($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        $result = $this->httpClient->get($url)->getData();
        if (!empty($result['errcode'])) return $this->setError($result['errmsg'], $result['errcode']);
        return $result;
    }

    //刷新access_token
    public function refreshAccessToken($refreshToken)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->appId}&grant_type=refresh_token&refresh_token={$refreshToken}";
        $result = $this->httpClient->get($url)->getData();
        if (!empty($result['errcode'])) return $this->setError($result['errmsg'], $result['errcode']);
        return $result;
    }

    //获取用户信息
    public function getUserInfo($accessToken, $openId)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
        $result = $this->httpClient->get($url)->getData();
        if (!empty($result['errcode'])) return $this->setError($result['errmsg'], $result['errcode']);
        return $result;
    }

    //生成网页授权URL
    public function makeAuthorizeUrl($stateData, $redirectUri = null, $response_type = 'code', $scope = 'snsapi_userinfo')
    {
        $state = '';
        $APPID = $this->appId;
        if (!empty($stateData)) {
            if (!is_array($stateData) && preg_match('/^\d+$/', $stateData)) {
                $state = $stateData;
            } else {
                $state = $this->saveState($stateData);
            }
        }
        if (!isset($redirectUri)) $redirectUri = H5_URL . '/weixin/authorize_callback';//固定的一级网关地址

        $redirectUri = urlencode($redirectUri);
        $authorizeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$APPID}&redirect_uri={$redirectUri}&response_type={$response_type}&scope={$scope}&state={$state}#wechat_redirect";
        return $authorizeUrl;
    }

    public function saveState($stateData)
    {
        $coreSdk = new CoreSdk(CORE_URL);
        $state = $coreSdk->post('common/save_wx_state', [
            'appid' => $this->appId,
            'data' => json_encode($stateData)
        ]);
        if (!$state) return $this->setError($coreSdk->getError());
        return $state;
    }

    public function getState($state)
    {
        $coreSdk = new CoreSdk(CORE_URL);
        $stateData = $coreSdk->post('common/get_wx_state', [
            'state' => $state
        ]);
        if (!$stateData) return $this->setError($coreSdk->getError());
        $stateData = json_decode($stateData, true);
        return $stateData;
    }

    public function getJsApiTicketCache($accessToken)
    {
        $WX_APPID = $this->appId;
        $key = 'wx:jsapi_ticket:' . $WX_APPID . ':' . $accessToken;
        $result = cache($key);
        if (empty($result) || $result['expires_time'] <= time() + 180) {
            $result = $this->getJsApiTicket($accessToken);
            if (!$result) return false;
            $result['expires_time'] = time() + $result['expires_in'];
            cache($key, $result);
        }
        return $result;
    }

    //获取jsapi ticket
    public function getJsApiTicket($accessToken)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$accessToken}&type=jsapi";
        $result = $this->httpClient->get($url)->getData();
        if (!empty($result['errcode'])) return $this->setError($result['errmsg'], $result['errcode']);
        return $result;
    }

    public function makeJsapiConfig($accessToken, $self)
    {
        $jsapiConfig = array();
        if (!$accessToken) {
            $jsapiConfig['errmsg'] = 'access_token error';
        } else {
            $signData = $this->getUrlSign($accessToken, $self);
            if (!$signData) {
                $jsapiConfig['errmsg'] = 'jsapi error';
            } else {
                $jsapiConfig['appId'] = $this->appId;
                $jsapiConfig['nonceStr'] = $signData['noncestr'];
                $jsapiConfig['signature'] = $signData['sign'];
                $jsapiConfig['timestamp'] = $signData['timestamp'];
                $jsapiConfig['debug'] = config('app.app_debug');
            }
        }
        return $jsapiConfig;
    }

    //获取当前会话中access_token信息（如果过期会自动刷新）
    public function getAccessTokenBySession($onlyToken = true)
    {
        $name = self::WX_ACCESS_TOKEN_KEY;
        $result = session('?' . $name) ? session($name) : null;
        if (isset($result)) {
            //access_token已经过期
            if (($result['expires_in'] - 5) + $result['refresh_time'] < time()) {
                $this->refreshAccessToken($result['refresh_token']);
            }
            //refresh_token已经过期(refresh_token是30天有效期)
            if ((2592000 - 30) + $result['auth_time'] < time()) {
                return null;
            }
            return $onlyToken ? $result['access_token'] : $result;
        }
        return null;
    }

    //url签名
    public function getUrlSign($accessToken, $currentUrl)
    {
        if (empty($_SERVER['REQUEST_SCHEME'])) $_SERVER['REQUEST_SCHEME'] = 'http';
        $REQUEST_SCHEME = Request::server('REQUEST_SCHEME');
        $REQUEST_SCHEME = strtolower($REQUEST_SCHEME);
        $HTTP_HOST = Request::server('HTTP_HOST');
        list($base, $anchor) = explode('#', $currentUrl);
        if (preg_match('/^(http|https)\:\/\//', $base)) {
            $signUrl = $base;
        } else {
            $base = ltrim($base, '/');
            $signUrl = $REQUEST_SCHEME . '://' . $HTTP_HOST . "/{$base}";
        }
        if (!$accessToken) return $this->setError('require access_token');
        $result = $this->getJsApiTicketCache($accessToken);
        if (!$result) return $this->setError('ticket error');
        $ticket = $result['ticket'];
        $signData = array(
            'noncestr' => md5(uniqid() . get_ucode()),
            'jsapi_ticket' => $ticket,
            'timestamp' => time(),
            'url' => $signUrl
        );
        ksort($signData);
        $str = '';
        foreach ($signData as $key => $value) {
            $str .= ($key . '=' . $value . '&');
        }
        $str = rtrim($str, '&');
        $signData['sign'] = sha1($str);
        return $signData;
    }

}