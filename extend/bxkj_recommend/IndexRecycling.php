<?php

namespace bxkj_recommend;
class IndexRecycling extends Base
{
    public function recycling()
    {
        $preViewedKey = ProRedis::genKey("preview:total");
        $preTempViewedKey = ProRedis::genKey("preview:temp:" . uniqid() . get_ucode());
        $indexExpire = ProConf::get('index_expire');
        $setExpire = false;
        $offset = 0;
        $length = 500;
        $length2 = 500;
        $maxTime = time() - ($indexExpire + 300);
        $total = 0;
        do {
            ProRedis::nxLock('indexrecycling');
            $indexList = $this->redis->zRangeByScore($preViewedKey, 0, $maxTime, ['withscores' => true, 'limit' => [$offset, $length]]);
            ProRedis::nxUnlock('indexrecycling');
            $indexList = $indexList ? $indexList : [];
            foreach ($indexList as $value => $score) {
                list($userMark, $videoId) = explode('||', $value);
                $indexKey = ProRedis::genKey("index:{$userMark}");
                $viewedKey = ProRedis::genKey("viewed:{$userMark}:total");
                $indexScore = $this->redis->zScore($indexKey, $videoId);
                if ($indexScore === false) {
                    $this->redis->zRem($viewedKey, $videoId);
                    $total++;
                } else {
                    $this->redis->zAdd($preTempViewedKey, $score, $value);
                    if (!$setExpire) {
                        $this->redis->expire($preTempViewedKey, 6 * 3600);
                        $setExpire = true;
                    }
                }
                $this->redis->zRem($preViewedKey, $value);
                usleep(1000);
            }
        } while (!empty($indexList));
        sleep(10);
        $exists = $this->redis->exists($preTempViewedKey);
        if ($exists) {
            $offset2 = 0;
            do {
                $indexList2 = $this->redis->zRangeByScore($preTempViewedKey, '-inf', '+inf', ['withscores' => true, 'limit' => [$offset2, $length2]]);
                $indexList2 = $indexList2 ? $indexList2 : [];
                foreach ($indexList2 as $value2 => $score2) {
                    $offset2++;
                    $this->redis->zAdd($preViewedKey, $score2, $value2);
                }
                usleep(15000);
            } while (!empty($indexList2));
            $this->redis->del($preTempViewedKey);
        }
        return $total;
    }

}