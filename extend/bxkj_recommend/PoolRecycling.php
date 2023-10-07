<?php

namespace bxkj_recommend;

use bxkj_common\RabbitMqChannel;

class PoolRecycling extends Base
{
    protected $createtimeKey;
    protected $recyclingPeriod;
    protected $maxQuantity;
    protected $tmpKey;
    protected $total;
    protected $rabbitChannel;
    protected $setExpire;

    public function __construct()
    {
        parent::__construct();
        $this->createtimeKey = ProRedis::genKey("createtime:total");
        $this->recyclingPeriod = ProConf::get('recycling_period');
        $this->maxQuantity = ProConf::get('recycling_max_quantity');
        $this->tmpKey = "tmp:rec_ids:" . uniqid() . get_ucode();
        $this->total = 0;
        $this->setExpire = false;
    }

    public function recycling()
    {
        $this->recyclingPeriod();
        $this->recyclingQuantity();
        if ($this->total > 0) {
            $this->rabbitChannel = new RabbitMqChannel();
            $iterator = null;
            $this->redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
            while ($arr_mems = $this->redis->zScan($this->tmpKey, $iterator, '*', 100)) {
                $this->batchHandler($arr_mems);
            }
            $this->redis->del($this->tmpKey);
            $this->rabbitChannel->close();
        }
        return $this->total;
    }

    protected function recyclingPeriod()
    {
        $offset = 0;
        $length = 500;
        $maxTime = time() - $this->recyclingPeriod;
        $num = 0;
        do {
            ProRedis::nxLock('poolrecycling');
            $indexList = $this->redis->zRangeByScore($this->createtimeKey, 0, $maxTime, ['withscores' => true, 'limit' => [$offset, $length]]);
            ProRedis::nxUnlock('poolrecycling');
            $indexList = $indexList ? $indexList : [];
            $offset += count($indexList);
            $this->batchAdd($indexList, $maxTime);
            $num += count($indexList);
            usleep(1000);
        } while (!empty($indexList));
        return $num;
    }

    protected function recyclingQuantity()
    {
        $offset = 0;
        $length = 500;
        $removeNum = 0;
        $count = $this->redis->zCount($this->createtimeKey, '-inf', '+inf');
        $diff = $count - $this->maxQuantity;
        while ($diff > 0 && $diff > $removeNum) {
            $length2 = (($diff - $removeNum) > $length) ? $length : $diff - $removeNum;
            $indexList = $this->redis->zRange($this->createtimeKey, $offset, $offset + $length2 - 1, true);
            $indexList = $indexList ? $indexList : [];
            $offset += count($indexList);
            $this->batchAdd($indexList);
            $removeNum += count($indexList);
            $count = $this->redis->zCount($this->createtimeKey, '-inf', '+inf');
            $diff = $count - $this->maxQuantity;
            usleep(1000);
        }
        return $removeNum;
    }

    protected function batchAdd($indexList, $maxTime = null)
    {
        $addIds = [];
        foreach ($indexList as $videoId => $createTime) {
            if (!isset($maxTime) || (isset($maxTime) && $createTime <= $maxTime)) {
                $addIds[] = $createTime;
                $addIds[] = $videoId;
                $this->total++;
                if (count($addIds) >= 60) {
                    array_unshift($addIds, $this->tmpKey);
                    call_user_func_array([$this->redis, 'zAdd'], $addIds);
                    $addIds = [];
                }
            }
        }
        if (!empty($addIds)) {
            array_unshift($addIds, $this->tmpKey);
            call_user_func_array([$this->redis, 'zAdd'], $addIds);
            $addIds = [];
        }
        if (!empty($indexList) && !$this->setExpire) {
            $this->redis->expire($this->tmpKey, 3600 * 6);
            $this->setExpire = true;
        }
    }

    protected function batchHandler($videos)
    {
        $ids = [];
        foreach ($videos as $videoId => $createTime) {
            $ids[] = $videoId;
            if (count($ids) >= 50) {
                $this->rabbitChannel->exchange('main')->send('video.update.recycling', ['ids' => implode(',', $ids)]);
                array_unshift($ids, $this->tmpKey);
                call_user_func_array([$this->redis, 'zRem'], $ids);
                $ids = [];
                usleep(1000);
            }
        }
        if (!empty($ids)) {
            $this->rabbitChannel->exchange('main')->send('video.update.recycling', ['ids' => implode(',', $ids)]);
            array_unshift($ids, $this->tmpKey);
            call_user_func_array([$this->redis, 'zRem'], $ids);
            $ids = [];
        }
    }

}