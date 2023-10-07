<?php

namespace bxkj_recommend;

use bxkj_recommend\model\Video;

class TotalPool extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function getTotalPoolKey()
    {
        return ProRedis::genKey("pool:total");
    }

    public function pushVideo(Video $video)
    {
        $score = $video->score;
        $totalPoolKey = self::getTotalPoolKey();
        $quantity = ProConf::get('pool_max_quantity');
        if ($quantity > 0) {
            $thr = ProConf::get('pool_thr');
            $this->redis->repairRedundancy($totalPoolKey, $quantity, $thr);
        }
        $this->redis->zAdd($totalPoolKey, $score, $video->id);
    }

    public function updateVideo(Video $video)
    {
        $score = $video->score;
        $totalPoolKey = self::getTotalPoolKey();
        $zscore = $this->redis->zScore($totalPoolKey, $video->id);
        $num = $this->redis->zAdd($totalPoolKey, $score, $video->id);
        return $num;
    }

    public function removeVideo($videoId)
    {
        $totalPoolKey = self::getTotalPoolKey();
        $num = $this->redis->zRem($totalPoolKey, $videoId);
        return $num;
    }
}