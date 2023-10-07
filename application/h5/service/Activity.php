<?php

namespace app\h5\service;

use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;

use think\Db;


class Activity
{
    protected $redis;

    protected $current_config = [];

    protected $coreSdk;

    protected static $page = 10;

    protected static $activity_config_name = '';

    protected static $title = '直播活动~';

    protected $day = 0;

    protected $where = [];

    public function __construct()
    {
        $this->day = date('Ymd');

        if (!$this->redis instanceof \Redis) $this->redis = RedisClient::getInstance();

        if (empty($this->current_config)) $this->current_config = $this->getActConfig();

        if (!$this->coreSdk instanceof CoreSdk) $this->coreSdk = new CoreSdk();
    }

    //活动相关配置
    protected function getActConfig()
    {
        return [];
    }


    protected function isLogin($user_id)
    {
        return true;

        $userLoginStatusKey = "loginstate:".$user_id;

        $userTokenKey = "access_token:".$token;

        $login_status = $this->redis->hget($userLoginStatusKey, 'status');

        $token_data = $this->redis->get($userTokenKey);

        if (empty($login_status) || empty($token_data)) return false;

        $token_data = json_decode($token_data, true);

        if ($token_data['user']['user_id'] != $user_id) return false;

        if ($login_status != 1) return false;

        return true;
    }


    protected function sendSocket($msg)
    {
        $socket = config('app.live_config');

        $url = str_replace('ws', 'tcp', $socket['message_server']['chat_server']);

        $url .= ':8181';

        $client = stream_socket_client($url, $err_no, $err_str, 3);

        fwrite($client, json_encode($msg));

        fwrite($client, "\r\n");

        $ack = fread($client, 1024);

        $ack_res = json_decode($ack, true);

        if ($ack_res['type'] != 'hi') {
            fclose($client);
            return make_error('socket链接错误');
        }

        $ack_close = fread($client, 8192);

        $close_res = json_decode($ack_close, true);

        if ($close_res['code'] != 0) {
            fclose($client);
            return make_error('socket通知错误');
        }

        fclose($client);

        return true;
    }

    //概率处理
    protected static function handleDraw($arr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($arr);
        //概率数组循环
        foreach ($arr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($arr);
        return $result;
    }



    //获取用户资料
    public function getUsersInfo($userIds, $visitor=null, $field='_all')
    {
        $arr = [];

        if (!is_array($userIds)) $userIds = [$userIds];

        $users = $this->coreSdk->getUsers($userIds, $visitor, $field);

        if (empty($users)) return $arr;

        foreach ($users as &$info)
        {
            isset($info['avatar']) && $info['avatar'] .= '?imageView2/1/w/50/h/50';

            $arr[$info['user_id']] = $info;
        }

        return $arr;
    }


    public function setWhere(array $where)
    {
        if (count($where) == count($where, 1))
        {
            array_push($this->where, $where);
        }
        else {
            $this->where = array_merge($this->where, $where);
        }

        return $this;
    }

}