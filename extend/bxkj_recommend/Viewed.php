<?php

namespace bxkj_recommend;

use bxkj_recommend\model\User;

class Viewed extends Base
{
    protected $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function insert($tag, $videoId)
    {
        $userMark = $this->user->getUserMark();
        $viewedKey = ProRedis::genKey("viewed:{$userMark}:{$tag}");
        $preViewedKey = ProRedis::genKey("preview:total");
        $this->redis->zAdd($viewedKey, time(), $videoId);
        $this->redis->zRem($preViewedKey, "{$userMark}||$videoId");
        $int = mt_rand(0, 100);
        if ($int < 30) {
            $viewed_period = ProConf::get('viewed_period');
            $viewed_max_length = ProConf::get('viewed_max_length');
            $this->redis->repair($viewedKey, $viewed_max_length, time() - $viewed_period);
        }
    }
}