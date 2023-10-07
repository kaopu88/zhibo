<?php

namespace bxkj_common\wxsdk;

use bxkj_common\HttpClient;

class WxSmallApp extends WxApp
{
    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->appType = 'small_app';
    }

    public function getUserInfoByCode($code)
    {
        $curlClient = new HttpClient();
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";
        $result = $curlClient->setCA(false)->get($url)->getData('json');
        if (!empty($result['errcode'])) return $this->setError($result['errmsg']);
        return $result;
    }

    //解密用户信息
    public function decryptUserInfo($sessionKey, $encryptedData, $iv)
    {
        $errMsgs = array(
            '41001' => 'encodingAesKey 非法',
            '41003' => 'aes 解密失败',
            '41004' => '解密后得到的buffer非法',
            '41005' => 'base64加密失败',
            '41016' => 'base64解密失败'
        );
        if (!class_exists('\WXBizDataCrypt', false)) {
            require ROOT_PATH . '/extend/wxBizDataCrypt/wxBizDataCrypt.php';
        }
        $pc = new \WXBizDataCrypt($this->appId, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode != 0) return $this->setError($errMsgs[(string)$errCode]);
        $result = json_decode($data, true);
        return $result;
    }

    public function saveUserInfo($userInfo)
    {
        $data = array(
            'openid' => $userInfo['openId'],
            'nickname' => $userInfo['nickName'],
            'gender' => (string)$userInfo['gender'],
            'language' => $userInfo['language'],
            'city' => $userInfo['city'],
            'province' => $userInfo['province'],
            'country' => $userInfo['country'],
            'avatar' => $userInfo['avatarUrl'],
            'uuid' => $userInfo['unionId'] ? $userInfo['unionId'] : ''
        );
        $wxUser = new WxUser($this);
        $wxUser->updateByOpenId($userInfo['openId']);
        $result = $wxUser->save($data);
        if (!$result) return $this->setError($wxUser->getError());
        return $wxUser;
    }

}