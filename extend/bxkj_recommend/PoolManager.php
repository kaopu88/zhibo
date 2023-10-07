<?php

namespace bxkj_recommend;

use bxkj_common\RedisClient;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\model\User;
use bxkj_recommend\model\Video;
use bxkj_recommend\model\VideoTag;
use bxkj_recommend\VideoUpdater;
use think\Db;

class PoolManager extends Base
{
    protected static $weightnum = 100000000; //权重放大倍数
    protected static $starttime = 1651075200;

    //添加视频
    public function push(Video $video)
    {
        //互关可见暂时未实现
        $visible = $video->visible;
        if ($visible == 1) return true;
        $score = $video->evaluate()->score;
        $tags = $video->getTags();
        $tagArr = [];
        foreach ($tags as $tag) {
            $tag->create();//创建标签
            $tagArr[] = $tag->name;
            $pool = new TagPool($tag);
            $pool->pushVideo($video);
        }
        $totalPool = new TotalPool();
        $totalPool->pushVideo($video);
        $this->redis->zAdd(ProRedis::genKey("timeline:total"), time(), $video->id);//加入时间
        $this->redis->zAdd(ProRedis::genKey("createtime:total"), $video->create_time, $video->id);//创作时间
        $vKey = Video::getDetailKey($video->id);
        $this->redis->hMset($vKey, [
            'id' => $video->id,
            'user_id' => $video->user_id,
            'score' => $video->score,
            'tag_names' => implode(',', $tagArr),
            'create_time' => $video->create_time,
            'duration' => $video->duration,
            'update_time' => time()
        ]);
        //注册到更新器中
        $videoUpdater = new VideoUpdater();
        $videoUpdater->reg($video);
        $video->save('score');
        $this->distributionFriends($video, 'add', 1500);
        return true;
    }

    //分发给好友
    protected function distributionFriends($video, $type, $maxLength)
    {
        if (is_array($video)) {
            $userId = $video['user_id'];
            $vid = $video['id'];
            $createTime = $video['create_time'];
        } else {
            $userId = $video->user_id;
            $vid = $video->id;
            $createTime = $video->create_time;
        }
        $fansKey = ProRedis::genKey("fans:{$userId}");
        $redis = RedisClient::getInstance();
        $fTotal = 0;
        $offset = 0;
        $index_fnewv_period = ProConf::get('index_fnewv_period');
        $helper_id = ProConf::get('helper_id');
        while ($fTotal < $maxLength) {
            $length = ($maxLength - $fTotal) > 100 ? 100 : $maxLength - $fTotal;
            $result = $redis->zRevRange($fansKey, $offset, $offset + $length - 1);
            if (empty($result)) break;
            foreach ($result as $fansUid) {
                $offset++;
                //小助手忽略
                if ($fansUid == $helper_id) continue;
                $fNewV = ProRedis::genKey("fnv:{$fansUid}");
                if ($type == 'add') {
                    $this->redis->zAdd($fNewV, $createTime, $vid);
                    $this->redis->expire($fNewV, $index_fnewv_period);//7天内没有任何好友发布新视频则删除
                } else {
                    $this->redis->zRem($fNewV, $vid);
                }
                $fTotal++;
            }
            usleep(150);
        }
    }

    //移除视频
    public function remove($videoId)
    {
        $vKey = Video::getDetailKey($videoId);
        $tagNames = $this->redis->hGet($vKey, 'tag_names');
        $userId = $this->redis->hGet($vKey, 'user_id');
        $tagNameArr = $tagNames ? explode(',', $tagNames) : [];
        foreach ($tagNameArr as $tagName) {
            try {
                $tag = new VideoTag($tagName);
                $pool = new TagPool($tag);
                $pool->removeVideo($videoId);
            } catch (Exception $exception) {
                continue;
            }
        }
        $totalPool = new TotalPool();
        $totalPool->removeVideo($videoId);
        //$this->redis->zRem(ProRedis::genKey("timeline:total"), $videoId);//永久记录不需要删除
        $this->redis->zRem(ProRedis::genKey("createtime:total"), $videoId);
        $videoUpdater = new \bxkj_recommend\VideoUpdater();
        $videoUpdater->remove($videoId);
        UserIndex::remove($videoId);
        $this->distributionFriends(['id' => $videoId, 'user_id' => $userId], 'remove', 1500);
        //his len
        $hisLenKey = ProRedis::genKey("vupdater:his:ing");
        $hisLenKey2 = ProRedis::genKey("vupdater:his:un");
        $hisRem = $this->redis->lRem($hisLenKey, $videoId);
        if (!$hisRem) {
            $this->redis->lRem($hisLenKey2, $videoId);
        }
        $watKey2 = ProRedis::genKey("watch:iuser:{$videoId}");
        $mapKey = ProRedis::genKey("watch:maps:{$videoId}");
        $watKey3 = ProRedis::genKey("wat_played_out:iuser:{$videoId}");
        $watKey4 = ProRedis::genKey("wat_switch:iuser:{$videoId}");
        $watKey5 = ProRedis::genKey("wat_general:iuser:{$videoId}");
        $this->redis->del($vKey, $watKey2, $mapKey, $watKey3, $watKey4, $watKey5);
        return true;
    }

    //更新视频（主要更新评分）
    public function update(Video $video)
    {
        $now = time();
        $videoId = $video->id;
        $vKey = Video::getDetailKey($videoId);
        $score = $video->evaluate()->score;
        $tags = $video->getTags();
        foreach ($tags as $tag) {
            $pool = new TagPool($tag);
            $pool->updateVideo($video);
        }
        $this->redis->hMset($vKey, [
            'score' => $video->score,
            'update_time' => time()
        ]);
        $totalPool = new TotalPool();
        $totalPool->updateVideo($video);
        $video->save('score');
        return $video;
    }

    //新排序方式
    public function newpush($videoId)
    {
        $info = Db::name('sys_config')->where(['mark'=>'video'])->value('value');
        $configAll = json_decode($info,true);
        $config = $configAll['vod']['audit_config'];
        $video = Db::name('video')->where(['id' => $videoId])->find();
        if (empty($video)) return false;
        $weight = $video['weight'] * ($config['recomment_weight'] ?: 0); //权重
        $copyRight = empty($video['copy_right']) ? 2 : $video['copy_right']; //标识
        $copyRightWeight = $copyRight * ($config['identification_weight'] ?: 0);
        $rating = $video['rating']; //评分
        $zanSum = $video['zan_sum'] * ($config['zan_weight'] ?: 0); //赞数量
        $commentSum = $video['comment_sum'] * ($config['comment_weight'] ?: 0); //评论数量
        $shareSum = $video['share_sum'] * ($config['share_weight'] ?: 0); //赞数量
        $exposure = (($video['create_time'] - self::$starttime) > 0 ? ($video['create_time'] - self::$starttime)  : 0) + ($config['exposure'] ?: 0);

        $totalWeight = $weight + $copyRightWeight + $rating + $zanSum + $commentSum + $shareSum + $exposure;
        $pushKey = "video:newpushtotal";
        $this->redis->zAdd($pushKey, $totalWeight , $videoId);
    }

    public function removeNew($videoId)
    {
        $pushKey = "video:newpushtotal";
        $this->redis->zRem($pushKey, $videoId);
    }
}