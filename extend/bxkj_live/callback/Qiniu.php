<?php

namespace bxkj_live\callback;


use bxkj_live\CallBack;
use Qiniu\Pili\Mac;

class Qiniu extends CallBack
{

    public function disconnect(array $params)
    {
        $stream = $params['title'];

        $body = "title={$stream}";

        $Mac = new Mac($this->live_config['access_key'], $this->live_config['secret_key']);

        $sign = $Mac->MACToken('POST', API_URL.'/?service=LiveCallback.callBack', 'application/x-www-form-urlencoded', $body);

        //签名校验
        if ($params['sign'] != $sign) return make_error('直播回调签名错误', 'wrong', ['type'=>'qiniu', 'sing'=>$params['sign'], 'check_sing'=> $sign, 'stream'=>$stream]);

        return $this->callbackCloseRoom($stream);

    }


    public function connect(array $params)
    {
        // TODO: Implement pushStream() method.
    }


}