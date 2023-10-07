<?php

namespace app\service;



class ActiveRedis
{
    protected $redis;
    protected $userId;
    protected $now;
    protected $anchorId;
    protected $anchorType = 'anchor';
    protected $agentMark = '';

    public function __construct($userId, $anchorType, $anchorId, $agentMark = '', $now = null)
    {
        global $redis;
        $this->redis = $redis;
        $this->userId = $userId;
        $this->anchorType = $anchorType;
        $this->anchorId = $anchorId;
        $this->agentMark = $agentMark;
        $this->now = isset($now) ? $now : time();
    }

    //活跃度
    public function active($num = 1)
    {
        $allKpiRedis = new KpiRedis($this->now);
        $allKpiRedis->setUser($this->anchorType, $this->anchorId)->setAgent('all')->setIndicator('active');
        $agentKpiRedis = null;
        if (!empty($this->agentMark)) {
            $agentKpiRedis = new KpiRedis($this->now);
            $agentKpiRedis->setUser($this->anchorType, $this->anchorId)->setAgent($this->agentMark)->setIndicator('active');
        }
        $day = date('Ymd', $this->now);
        $countedMark = "{$this->anchorType}:{$this->anchorId}:d:{$day}";
        if (!$this->isCounted($countedMark)) {
            $this->redis->zIncrBy($allKpiRedis->getDayKey(), $num, $this->anchorId);
            if ($agentKpiRedis) $this->redis->zIncrBy($agentKpiRedis->getDayKey(), $num, $this->anchorId);
            //周活跃
            if (!$this->fortActiveCounted()) {
                $this->redis->zIncrBy($allKpiRedis->getFortKey(), $num, $this->anchorId);
                if ($agentKpiRedis) $this->redis->zIncrBy($agentKpiRedis->getFortKey(), $num, $this->anchorId);
            }
            //月活跃
            if (!$this->monthActiveCounted()) {
                $this->redis->zIncrBy($allKpiRedis->getMonthKey(), $num, $this->anchorId);
                if ($agentKpiRedis) $this->redis->zIncrBy($agentKpiRedis->getMonthKey(), $num, $this->anchorId);
            }
            $key = $this->getCountedKey($countedMark);
            $this->redis->sAdd($key, $this->userId);
        }
    }

    //半月是否已经计入活跃
    private function fortActiveCounted()
    {
        $fnum = DateTools::getDoubleWeekNum($this->now);
        $days = DateTools::getDoubleWeekNodesByNum('d', $fnum, 'Ymd');
        return $this->daysActiveCounted($days);
    }

    //本月是否已经计入活跃
    private function monthActiveCounted()
    {
        $days = DateTools::getMonthNodes('d', $this->now, 'Ymd');
        return $this->daysActiveCounted($days);
    }

    private function daysActiveCounted($days)
    {
        foreach ($days as $day) {
            $countedMark = "{$this->anchorType}:{$this->anchorId}:d:{$day}";
            if ($this->isCounted($countedMark)) return true;
        }
        return false;
    }

    public function isCounted($mark)
    {
        $key = $this->getCountedKey($mark);
        $exists = $this->redis->sIsMember($key, $this->userId);
        return $exists ? true : false;
    }

    protected function getCountedKey($mark)
    {
        $key = "counted_users:live:{$mark}";
        return $key;
    }

}