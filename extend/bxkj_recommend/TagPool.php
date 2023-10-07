<?php

namespace bxkj_recommend;

use bxkj_recommend\model\Video;
use bxkj_recommend\model\VideoTag;

class TagPool extends Base
{
    protected $videoTag;

    public function __construct(VideoTag $videoTag)
    {
        parent::__construct();
        $this->videoTag = $videoTag;
    }

    public static function getTagPoolKey($id)
    {
        return ProRedis::genKey("pool:tag:{$id}");
    }

    //添加视频
    public function pushVideo(Video $video)
    {
        $score = $video->score;
        $poolKey = self::getTagPoolKey($this->videoTag->id);
        $quantity = ProConf::get('pool_max_quantity');
        if ($quantity > 0) {
            $thr = ProConf::get('pool_thr');
            $this->redis->repairRedundancy($poolKey, $quantity, $thr);
        }
        $num = $this->redis->zAdd($poolKey, $score, $video->id);
        return $num;
    }

    //更新视频
    public function updateVideo(Video $video)
    {
        $score = $video->score;
        $poolKey = self::getTagPoolKey($this->videoTag->id);
        $zscore = $this->redis->zScore($poolKey, $video->id);
        $num = $this->redis->zAdd($poolKey, $score, $video->id);
        return $num;
    }

    //移除视频
    public function removeVideo($videoId)
    {
        $poolKey = self::getTagPoolKey($this->videoTag->id);
        $this->redis->zRem($poolKey, $videoId);
    }

}