<?php

namespace bxkj_recommend;

use bxkj_recommend\exception\Exception;
use bxkj_recommend\model\Video;

class VideoUpdater extends Base
{
    protected $frequencyConfig;
    protected $pool;

    public function __construct()
    {
        parent::__construct();
        $this->frequencyConfig = ProConf::get('vupdater_frequency');
        $this->pool = new PoolManager();
    }

    public static function getKey($type, $status = 'un')
    {
        return ProRedis::genKey("vupdater:{$type}:{$status}");
    }

    //通过视频模型获取更新周期配置
    public function getFreConfigByVideo(Video $video)
    {
        $duration = max(time() - $video->create_time, 0);
        $config = $this->getFreConfigByDuration($duration);
        if (!empty($config)) return $config;
        return ['type' => 'his'];
    }

    //通过视频时长获取更新周期配置
    public function getFreConfigByDuration($duration)
    {
        $config = null;
        foreach ($this->frequencyConfig as $item) {
            $range = $item['range'];
            if (Calc::validateRange($duration, $range)) {
                $config = $item;
                break;
            }
        }
        return $config;
    }

    //通过更新器类型获取更新周期配置
    public function getFreConfigByType($type)
    {
        foreach ($this->frequencyConfig as $item) {
            if ($item['type'] == $type) return $item;
        }
        return null;
    }


    //开始更新
    public function start($inputData)
    {
        $type = trim($inputData['type']);
        $config = $this->getFreConfigByType($type);
        if (empty($config)) return false;
        $usleep = $config['usleep'] < 5000 ? 5000 : $config['usleep'];
        $total = 0;
        $doNum = 0;
        do {
            $this->redis->set(self::getKey($type, 'state'), $doNum, 10);
            $doNum++;
            $videoId = $this->rpoplpushVideoId($type);
            if (!empty($videoId)) {
                $res = $this->handler($type, $videoId);
                if ($res) $total++;
            }
            if ($usleep > 0) usleep($usleep);
        } while ($videoId !== false);
        return $total;
    }

    //弹出一个需要更新的视频ID
    protected function rpoplpushVideoId($type)
    {
        $unKey = self::getKey($type, 'un');
        $ingKey = self::getKey($type, 'ing');
        $finishedKey = self::getKey($type, 'finished');
        $videoId = $this->redis->rpoplpush($unKey, $ingKey);
        if ($videoId === false) {
            $this->redis->set($finishedKey, time());
        }
        return $videoId;
    }

    protected function handler($type, $videoId)
    {
        $unKey = self::getKey($type, 'un');
        $ingKey = self::getKey($type, 'ing');
        $now = time();
        $vKey = Video::getDetailKey($videoId);
        $update_time = $this->redis->hGet($vKey, 'update_time');//上次更新时间
        if ($update_time === false) {
            $num = $this->redis->lRem($ingKey, $videoId, 0);
            if (!$num) $num = $this->redis->lRem($unKey, $videoId, 0);
            return false;
        }
        $app_debug = config('app.app_debug');
        if (($now - $update_time) < 300 && !$app_debug) return false;
        try {
            $video = new Video($videoId);
        } catch (Exception $exception) {
            $code = $exception->getCode();
            if ($code == 1000) {
                $this->pool->remove($videoId);
            }
            $num = $this->redis->lRem($ingKey, $videoId, 0);
            if (!$num) $num = $this->redis->lRem($unKey, $videoId, 0);
            return false;
        }
        $config = $this->getFreConfigByVideo($video);
        $newType = $config['type'];
        $this->pool->update($video);
        if ($newType != $type) {
            $num = $this->redis->lRem($ingKey, $videoId, 0);
            if (!$num) $num = $this->redis->lRem($unKey, $videoId, 0);
            if ($num) {
                $newIngKey = self::getKey($newType, 'ing');
                $newUnKey = self::getKey($newType, 'un');
                $ingRes = $this->redis->lPushx($newIngKey, $videoId);
                if (!$ingRes) $this->redis->lPush($newUnKey, $videoId);
                $this->redis->hSet($vKey, 'updater', $newType);
            }
        }
        return true;
    }

    //注册新视频
    public function reg(Video $video)
    {
        $config = $this->getFreConfigByVideo($video);
        $unKey = self::getKey($config['type'], 'un');
        $vKey = Video::getDetailKey($video->id);
        $this->redis->hSet($vKey, 'updater', $config['type']);
        return $this->redis->lPush($unKey, $video->id);
    }

    //移除
    public function remove($videoId)
    {
        $vKey = Video::getDetailKey($videoId);
        $type = $this->redis->hGet($vKey, 'updater');
        if ($type) {
            $unKey = self::getKey($type, 'un');
            $ingKey = self::getKey($type, 'ing');
            $num = $this->redis->lRem($ingKey, $videoId, 0);
            if (!$num) $num = $this->redis->lRem($unKey, $videoId, 0);
            return $num;
        }
        return false;
    }


}