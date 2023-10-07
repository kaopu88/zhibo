<?php

namespace bxkj_common\wxsdk;

class WxApp
{
    protected $appId;
    protected $appSecret;
    protected $appType;
    protected $error;

    public function __construct($appId = null, $appSecret = null)
    {
    	$config = config('app.media_platform.wx_wap');
        $this->appId = isset($appId) ? $appId : $config['app_id'];
        $this->appSecret = isset($appSecret) ? $appSecret : $config['secret_key'];
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getAppSecret()
    {
        return $this->appSecret;
    }

    public function getAppType()
    {
        return $this->appType;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($message = '', $code = 1)
    {
        $this->error = is_error($message) ? $message : make_error($message, $code);
        return false;
    }
}