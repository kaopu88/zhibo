<?php


namespace app\service;

class KpiRedis
{
    /*
     * agent:all:millet:d:20180212 不区分一级二级整个平台的代理商排名 millet是收入谷子
     * agent:一级代理商ID:millet:d:20180212
     * anchor:all:millet:d:20180822 不区分代理商，整个平台的主播排名
     * anchor:代理商ID:millet:d:20180822
     * promoter:all:cons:d:20180821 不区分代理商，整个平台的推广员排名
     * promoter:代理商ID:cons:d:20180821
     */

    protected $redis;
    protected $now;
    protected $day;
    protected $fNum;
    protected $month;
    protected $year;
    protected $userType;
    protected $userId;
    protected $indicator;
    protected $agentId = 'all';
    protected $prefix = 'kpi';
    protected $probability = 0.1;//有10%的几率更新

    public function __construct($now = null, $redisConfig = [])
    {
        global $redis;
        $redisConfig = array_merge([
            'prefix' => 'kpi',
            'probability' => 0.05,
            'redis' => null
        ], is_array($redisConfig) ? $redisConfig : []);
        $this->prefix = $redisConfig['prefix'];
        $this->probability = $redisConfig['probability'];
        $this->redis = isset($redisConfig['redis']) ? $redisConfig['redis'] : $redis;
        $this->now = isset($now) ? $now : time();
        $this->day = date('Ymd', $this->now);
        $this->month = date('Ym', $this->now);
        $this->year = date('Y', $this->now);
        $this->fNum = DateTools::getFortNum($this->now);
    }

    public function setUser($userType, $userId)
    {
        $this->userType = $userType;
        $this->userId = $userId;
        return $this;
    }

    public function setIndicator($indicator)
    {
        $this->indicator = $indicator;
        return $this;
    }

    public function setAgent($agentId = 'all')
    {
        $this->agentId = $agentId;
        return $this;
    }

    public function getDayKey($day = null)
    {
        $day = isset($day) ? $day : $this->day;
        return "{$this->prefix}:{$this->userType}:{$this->agentId}:{$this->indicator}:d:{$day}";
    }

    public function getFortKey($fnum = null)
    {
        $fnum = isset($fnum) ? $fnum : $this->fNum;
        return "{$this->prefix}:{$this->userType}:{$this->agentId}:{$this->indicator}:f:{$fnum}";
    }

    public function getMonthKey($month = null)
    {
        $month = isset($month) ? $month : $this->month;
        return "{$this->prefix}:{$this->userType}:{$this->agentId}:{$this->indicator}:m:{$month}";
    }

    public function getYearKey($year)
    {
        $year = isset($year) ? $year : $this->year;
        return "{$this->prefix}:{$this->userType}:{$this->agentId}:{$this->indicator}:y:{$year}";
    }

    protected function getPrefix($unit = null)
    {
        //指针的意思是上次统计的位置
        $md5 = md5("{$this->agentId}:{$this->indicator}");
        $prefix = "{$this->prefix}:pointer:{$this->userType}:{$this->userId}:{$md5}:";//指针前缀
        return $prefix . (isset($unit) ? $unit : '');
    }

    public function dayInc($num = 0)
    {
        if (empty($this->userType) || empty($this->agentId) || empty($this->userId) || empty($this->indicator)) {
            return false;
        }
        if ($num == 0) return true;
        $prefix = $this->getPrefix();
        //更新日数据
        $dKey = $this->getDayKey();
        $res = $this->redis->zIncrBy($dKey, $num, $this->userId);//这是增量
        $dayPointer = $this->redis->get($prefix . 'd');//日指针
        if ($res) {
            if ($this->day != $dayPointer) {
                if (!empty($dayPointer)) {
                    $this->pointerHandler($dayPointer);
                }
                $this->redis->set($prefix . 'd', $this->day);
            } else {
                $randNum = mt_rand(0, 100);
                $probabilityVal = round($this->probability * 100);
                if ($randNum <= $probabilityVal) {
                    $this->refresh($dayPointer);
                }
            }
        }
        return $res;
    }

    //刷新当前指针数据
    public function refresh($dayPointer = null)
    {
        if (empty($this->userType) || empty($this->agentId) || empty($this->userId) || empty($this->indicator)) {
            return false;
        }
        if (!isset($dayPointer)) {
            $prefix = $this->getPrefix();
            $dayPointer = $this->redis->get($prefix . 'd');//日指针
        }
        if (!empty($dayPointer)) {
            $this->pointerHandler($dayPointer);
        }
        return true;
    }

    protected function pointerHandler($dayPointer)
    {
        //上个指针日
        $matches = [];
        preg_match('/^(\d{4})(\d{2})(\d{2})$/', $dayPointer, $matches);
        $year = $matches[1];
        $month = $matches[2];
        $time = mktime(0, 0, 0, $month, $matches[3], $year);
        $fnum = DateTools::getFortNum($time);
        $monthDays = DateTools::getMonthNodes('d', $time, 'Ymd');//一个月内的所有天集合
        $fDays = DateTools::getFortNodesByNum('d', $fnum, 'Ymd');
        $monthTotal = 0;
        $fTotal = 0;

        //上个指针日所在月的总计
        foreach ($monthDays as $day) {
            //$day小于等于今天才有数据,未来怎么会有数据呢
            if ($day <= $this->day) {
                $tmpDKey = $this->getDayKey($day);
                $tmp = $this->redis->zScore($tmpDKey, $this->userId);
                $tmp = $tmp ? $tmp : 0;
                $monthTotal += $tmp;
                if (in_array($day, $fDays)) {
                    $fTotal += $tmp;
                }
            }
        }
        $diffNodes = array_diff($fDays, $monthDays);//不在月日集合内
        if (!empty($diffNodes)) {
            foreach ($diffNodes as $day) {
                if ($day <= $this->day) {
                    $tmpDKey = $this->getDayKey($day);
                    $tmp = $this->redis->zScore($tmpDKey, $this->userId);
                    $fTotal += ($tmp ? $tmp : 0);
                }
            }
        }
        $mKey = $this->getMonthKey($year . $month);
        $this->redis->zAdd($mKey, $monthTotal, $this->userId);
        $fKey = $this->getFortKey($fnum);
        $this->redis->zAdd($fKey, $fTotal, $this->userId);

        //统计年度数据
        $yearTotal = 0;
        for ($i = 0; $i <= 12; $i++) {
            $thatMonth = $year . str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($thatMonth <= ((int)$this->month)) {
                $tmpMKey = $this->getMonthKey($thatMonth);
                $tmp = $this->redis->zScore($tmpMKey, $this->userId);
                $yearTotal += ($tmp ? $tmp : 0);
            }
        }

        $yKey = $this->getYearKey($year);
        $this->redis->zAdd($yKey, $yearTotal, $this->userId);
    }


}