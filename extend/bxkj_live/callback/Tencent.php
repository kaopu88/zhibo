<?php

namespace bxkj_live\callback;

use bxkj_common\RedisClient;
use bxkj_live\CallBack;


class Tencent extends CallBack
{

    protected $redis;

    public function __construct()
    {
        parent::__construct();

        $this->redis = RedisClient::getInstance();
    }

    public function disconnect(array $params)
    {
        if ($params['t'] < time()) return false;

        if (!$this->checkSign($params['sign'], $params['t'])) return make_error('直播回调签名错误', 'wrong', ['type'=>'tencent', 'sing'=>$params['sign'], 'stream'=>$params['stream_id']]);

        if ($this->redis->exists('tencent:'.$params['stream_id']))
        {
            $this->redis->del('tencent:'.$params['stream_id']);

            return $this->callbackCloseRoom($params['stream_id']);
        }
        else{
            $data = [
               'event_type' => 'disconnect',
               'data' => json_encode($params),
            ];

            $timer_id = $this->coreSdk->post('timer/add', ['url'=>API_URL.'/?service=LiveCallback.tencent', 'data'=>json_encode($data), 'cycle'=>0, 'trigger_time'=>time()+60]);

            $this->redis->set('tencent:'.$params['stream_id'], $timer_id['key']);

            return false;
        }

    }


    public function connect(array $params)
    {
        $target = $this->redis->get('tencent:'.$params['stream_id']);

        if ($target)
        {
            $this->redis->del('tencent:'.$params['stream_id']);

            $this->coreSdk->post('timer/remove', ['key'=>$target]);
        }
    }


    protected function checkSign($sign, $t)
    {
        $check_sign = md5($this->live_config['access_key'].$t);

        return $check_sign == $sign;
    }


}