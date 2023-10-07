<?php

namespace bxkj_module\service;

use bxkj_module\service\Service;
use bxkj_common\RedisClient;
use think\Db;

class Follow extends Service
{
    //获取关注的所有人
    public function getFollowList($userId, $order = 'desc', $offset = 0, $length = 10, $options = null)
    {
        $key = "follow:{$userId}";
        $redis = RedisClient::getInstance();
        if (!$redis->exists($key)) {
            $this->rebuildFollow($userId, null);
        }
        $list = $redis->getSZList($key, $order, $offset, $length, $options);
        return $list;
    }

    //获取所有粉丝
    public function getFansList($userId, $order = 'desc', $offset = 0, $length = 10, $options = null)
    {
        $key = "fans:{$userId}";
        $redis = RedisClient::getInstance();
        if (!$redis->exists($key)) {
            $this->rebuildFans($userId, null);
        }
        $list = $redis->getSZList($key, $order, $offset, $length, $options);
        return $list;
    }

    //获取关注信息
    public function getFollowInfo($userId, $followUserId)
    {
        $redis = RedisClient::getInstance();
        $key = "follow:{$userId}";
        $keyCount = "follow:count:{$userId}";
        $followInfo = ['is_follow' => '0', 'follow_time' => ''];

        //$followCount = $redis->get($keyCount) ? $redis->get($keyCount) : 0;
        //$followUserCount = $redis->zCard($key);
        //重建
        if (!$redis->exists($key) || !$redis->exists($keyCount)) {
            $redis->set($keyCount, 1, 86400);
            return $this->rebuildFollow($userId, $followUserId);
        }
        $score = $redis->zScore($key, $followUserId);
        if ($score) {
            $followInfo['is_follow'] = '1';
            $followInfo['follow_time'] = $score;
        }
        return $followInfo;
    }

    public function getFansInfo($userId, $fansUserId)
    {
        $redis = RedisClient::getInstance();
        $key = "fans:{$userId}";
        $keyCount = "fans:count:{$userId}";
        $fansInfo = ['is_fans' => '0', 'follow_time' => ''];
        //重建
        if (!$redis->exists($key)  || !$redis->exists($keyCount)) {
            $redis->set($keyCount, 1, 86400);
            return $this->rebuildFans($userId, $fansUserId);
        }
        $score = $redis->zScore($key, $fansUserId);
        if ($score) {
            $fansInfo['is_fans'] = '1';
            $fansInfo['follow_time'] = $score;
        }
        return $fansInfo;
    }

    //重建关注索引
    public function rebuildFollow($userId, $followUserId = null)
    {
        $followInfo = ['is_follow' => '0', 'follow_time' => ''];
        $key = "follow:{$userId}";
        $redis = RedisClient::getInstance();
        $where = ['user_id' => $userId];
        $followList = Db::name('follow')->field('follow_id,create_time')->where($where)->select();
        $redis->zAdd($key, 0, 0);
        foreach ($followList as $follow) {
            if (empty($follow)) continue;
            $redis->zAdd($key, $follow['create_time'], $follow['follow_id']);
            if (isset($followUserId) && $followUserId == $follow['follow_id']) {
                $followInfo['is_follow'] = '1';
                $followInfo['follow_time'] = $follow['create_time'];
            }
        }
        return isset($followUserId) ? $followInfo : $followList;
    }

    //重建粉丝索引
    public function rebuildFans($userId, $fansUserId = null)
    {
        $key = "fans:{$userId}";
        $redis = RedisClient::getInstance();
        $where['follow_id'] = $userId;
        $fans = Db::name('follow')->field('user_id,follow_id,create_time')->where($where)->select();
        $fansInfo = ['is_fans' => '0', 'follow_time' => ''];
        $redis->zAdd($key, 0, 0);
        foreach ($fans as $fan) {
            if (empty($fan)) continue;
            $redis->zAdd($key, $fan['create_time'], $fan['user_id']);
            if (isset($fansUserId) && $fansUserId == $fan['user_id']) {
                $fansInfo['is_fans'] = '1';
                $fansInfo['follow_time'] = $fan['create_time'];
            }
        }
        return isset($fansUserId) ? $fansInfo : $fans;
    }

    public function isFollow($fansUserId, $followUserId)
    {
        $info = $this->getFollowInfo($fansUserId, $followUserId);
        return ($info && $info['is_follow'] == '1');
    }

}