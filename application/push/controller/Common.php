<?php

namespace app\push\controller;

use bxkj_common\RedisClient;
use bxkj_module\service\Kpi;
use bxkj_module\service\User;
use bxkj_recommend\ProRedis;
use think\Db;

class Common extends Api
{


    public function test()
    {
        $vid = input('video_id');
        $redis = ProRedis::getInstance();
        $userId = input('user_id');
        $score = $redis->zScore("viewed:user:{$userId}:total", $vid);
        echo 'view:<br/>';
        var_dump($score);
        echo '<br/>';
        $sort = $redis->zScore("index:user:{$userId}", $vid);
        echo 'sort:<br/>';
        var_dump($sort);
    }
}
