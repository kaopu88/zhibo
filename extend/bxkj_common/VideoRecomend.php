<?php
/**
 * Created by PhpStorm.
 * User: zack
 * qq: 840855344
 * phone：18156825246
 */

namespace bxkj_common;

use think\Db;

class VideoRecomend
{
    protected static $redis;
    protected $limit;

    public function __construct($userId = '', $meid = '')
    {
        if (!isset(self::$redis)) self::$redis = RedisClient::getInstance();;
    }

    public function getList($offset = 0, $length = 10)
    {
        $randlist = [];
        $pushKey = "video:newpushtotal";

        if (!empty(APP_MEID)) {
            $watchkey = "video:watch:user:" . APP_MEID;
            $lookNum = self::$redis->sCard($watchkey);
            $offset = $offset + $lookNum;
        }

        $result = self::$redis->zRevRange($pushKey, $offset, $offset + $length - 1);

        $list = $this->getVideos($result);
        if (count($result) < 10) {
            $this->limit = $length - count($result);
            $randlist = $this->getRandVideos($this->limit);
        }
        $list = array_merge($list, $randlist);
        return $list;
    }

    public function getVideos($ids)
    {
        if (empty($ids)) return [];
        $videoList = Db::name('video')->where(['is_ad' => 0])->whereIn('id', $ids)->limit(count($ids))->select();
        return $videoList;
    }

    //最低保证
    protected function getRandVideos($length)
    {
        $videos = Db::name('video')->where(['is_ad' => 0])->orderRand()->limit($length)->select();
        return $videos ? $videos : [];
    }
}