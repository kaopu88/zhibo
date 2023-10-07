<?php

namespace app\api\service;
use app\common\service\Service;
use think\Db;
use bxkj_common\RedisClient;

class UserBlacklist extends Service
{
    //是否在黑名单内
    public function isBlack($userId, $toUserId)
    {
        $redis = RedisClient::getInstance();
        $key = "blacklist:{$userId}";
        $isBlack = false;
        if (!$redis->exists($key)) {
            $where = array('user_id' => $userId, 'status' => '1');
            $blacklist = Db::name('user_blacklist')->field('user_id,to_uid,create_time')->where($where)->select();
            $redis->zAdd($key, time(), 0);
            foreach ($blacklist as $item) {
                $redis->zAdd($key, $item['create_time'], $item['to_uid']);
                if ($item['to_uid'] == $toUserId) $isBlack = true;
            }
            return $isBlack;
        }
        $score = $redis->zScore($key, $toUserId);
        return (bool)$score;
    }
}