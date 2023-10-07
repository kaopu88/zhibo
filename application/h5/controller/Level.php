<?php

namespace app\h5\controller;

use think\facade\Request;
use think\Db;
use bxkj_common\RedisClient;
use bxkj_module\controller\Web;


class Level extends Web
{

    function index()
    {
        $keys = 1;
        $userId = input("uid") ? input("uid") : input("user_id");
        $redis = RedisClient::getInstance();
        $level = $redis->zrange('config:exp_level', 0, -1, true);
        if (empty($level)) {
            $level = Db::name('exp_level')->field('name, level_up')->select();
            foreach ($level as $key => $val) {
                $redis->zadd('config:exp_level', $val['level_up'], $val['name']);
            }
            $level = $redis->zrange('config:exp_level', 0, -1, true);
        }
        $user = Db::name("user")->field('avatar, level, exp')->where('user_id', $userId)->find();
        if (empty($user)) return 'User does not exist';
        foreach ($level as $key => $val) {
            if ($user['exp'] <= $val) break;
            $keys = $key;
        }

        $currentLevel = $level[$keys];
        $nextLevel = $level[$keys + 1];
        $cha = $nextLevel - $user['exp'];
        $rate = round((($user['exp'] - $currentLevel) / ($nextLevel - $currentLevel)) * 100, 2);
        $this->assign("user", $user);
        $this->assign("cha", $cha);
        $this->assign("rate", $rate);
        return $this->fetch();
    }

}