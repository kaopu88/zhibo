<?php

namespace bxkj_module\service;

use bxkj_common\HttpClient;

class WxTemplateMessage extends WxApi
{
    //发送模板消息
    public function send($touser, $templateId, $url, $data)
    {
        $ACCESS_TOKEN = $this->accessToken;
        $api = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$ACCESS_TOKEN}";
        $msgData['touser'] = $touser;
        $msgData['template_id'] = $templateId;
        $msgData['url'] = $url ? $url : '';
        if (isset($data['miniprogram'])) {
            $msgData['miniprogram'] = $data['miniprogram'];
        }
        $msgData['data'] = $data;
        $result = $this->httpClient->setContentType(HttpClient::FORMAT_JSON)->post($api, $msgData)->getData('json');
        if (!empty($result['errcode'])) return $this->setError($result['errmsg'], $result['errcode']);
        return true;
    }
}