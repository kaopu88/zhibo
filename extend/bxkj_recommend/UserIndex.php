<?php

namespace bxkj_recommend;


class UserIndex extends Base
{
    protected $aliasType;
    protected $aliasId;
    protected $userMark;
    protected $indexKey;
    protected $ids;
    protected $instock = 0;
    protected $fullLength;
    protected $need;
    protected $scores = [];
    protected $compositions;
    protected $reason = [];//推荐理由
    protected $indexExpire;//索引有效期
    protected $perTimes = [];

    public function __construct($aliasType = '', $aliasId = '')
    {
        parent::__construct();
        $this->indexExpire = ProConf::get('index_expire');
        $this->fullLength = ProConf::get('index_full_length');
        $this->compositions = ProConf::get('index_compositions');
        $this->aliasType = $aliasType;
        $this->aliasId = $aliasId;
        $this->userMark = "{$this->aliasType}:{$this->aliasId}";
        $this->indexKey = ProRedis::genKey("index:{$this->userMark}");
    }

    public function building()
    {
        $bdingKey = ProRedis::genKey("bding:{$this->userMark}");
        $isBuilding = $this->redis->exists($bdingKey);
        if ($isBuilding) return true;
        $index_slowest = ProConf::get('index_slowest');
        $index_delayed_release = ProConf::get('index_delayed_release');
        $index_per_sta = ProConf::get('index_per_sta');
        $this->redis->set($bdingKey, time(), $index_slowest);
        $this->instock = $this->redis->zCount($this->indexKey, '-inf', '+inf');//索引库存量
        $this->need = $this->fullLength - $this->instock;
        if ($this->need <= 0) {
            $this->redis->expire($bdingKey, $index_delayed_release);
            return true;
        }
        $this->perTimes['total'] = ['start' => msectime()];
        $this->ids = [];
        $this->perTimes['propaganda'] = ['start' => msectime(), 'query_num' => 0];
        $slen = $this->fetchFromPropaganda($this->need - count($this->ids));
        $this->perTimes['propaganda']['actual_num'] = $slen;
        $this->perTimes['propaganda']['end'] = msectime();
        $nowNeed = $this->need - $slen;
        $compositions = $this->compositions;
        if ($nowNeed > 0) {
            while (count($compositions) > 0) {
                $length2 = $this->need - count($this->ids);
                if ($length2 <= 0) break;
                $composition = array_shift($compositions);
                $name = $composition['name'];
                $pro = $composition['pro'];//预计比例
                $this->perTimes[$name] = ['start' => msectime(), 'query_num' => 0];
                $fun = 'fetchFrom' . parse_name($name, 1, true);
                $needLen = ceil($nowNeed * $pro);
                $slen = call_user_func_array([$this, $fun], [$needLen]);
                $this->perTimes[$name]['actual_num'] = $slen;
                $this->perTimes[$name]['end'] = msectime();
                if ($slen < $needLen) {
                    $spro = round($slen / $nowNeed, 4);//实际获取占比
                    $lack = round($pro - $spro, 4);
                    if ($lack > 0) Calc::rebalance($compositions, $lack, 'pro');//再分配
                }
            }
        }
        $this->save();
        $this->perTimes['total']['end'] = msectime();
        $this->perTimes['total']['actual_num'] = count($this->ids);
        if ($index_per_sta) $this->performance();
        $this->redis->expire($bdingKey, $index_delayed_release);
        return true;
    }

    //来自于宣发
    protected function fetchFromPropaganda($needLen)
    {
        $slen = 0;
        $pgdKey = ProRedis::genKey('pool:pgd');//这是一个公共宣发池子
        //分值是开始宣发时间
        $members = $this->redis->zRangeByScore($pgdKey, '-inf', time(), ['withscores' => true]);
        $members = $members ? $members : [];
        $viewedKey = ProRedis::genKey("viewed:{$this->userMark}:total");
        $index_pgd_period = ProConf::get('index_pgd_period');
        foreach ($members as $member => $start) {
            if ($slen >= $needLen) break;
            $this->perTimes['propaganda']['query_num']++;
            if ((time() - $start) > $index_pgd_period) {
                $this->redis->zRem($pgdKey, $member);
                continue;
            }
            if (in_array($member, $this->ids)) continue;
            $zscore2 = $this->redis->zScore($viewedKey, $member);
            if ($zscore2 > 0) {
                continue;
            }
            $this->ids[] = $id;
            $this->reason[(string)$id] = 'pgd';
            $slen++;
        }
        return $slen;
    }

    //来自于好友
    protected function fetchFromFriends($needLen)
    {
        $slen = 0;
        if ($needLen <= 0) return $slen;
        if ($this->aliasType != 'user') return $slen;
        $fNewV = ProRedis::genKey("fnv:{$this->aliasId}");
        $whileNum = 0;
        $viewedKey = ProRedis::genKey("viewed:{$this->userMark}:total");
        $index_while_max = ProConf::get('index_while_max');
        $index_fnewv_period = ProConf::get('index_fnewv_period');
        $int = mt_rand(0, 100);
        if ($int < 50) {
            $this->redis->zRemRangeByScore($fNewV, '-inf', time() - $index_fnewv_period);
        }
        while ($slen < $needLen) {
            if ($whileNum > $index_while_max) break;
            $offset = 0;
            $length = $needLen - $slen;
            $index = $this->redis->zRevRange($fNewV, $offset, $offset + $length - 1, true);
            if (empty($index)) break;
            foreach ($index as $id => $createTime) {
                $this->perTimes['friends']['query_num']++;
                $this->redis->zRem($fNewV, $id);
                if (in_array($id, $this->ids)) continue;
                $zscore = $this->redis->zScore($viewedKey, $id);
                if ($zscore > 0) {
                    continue;
                }
                $this->ids[] = $id;
                $this->reason[(string)$id] = 'friends';
                $slen++;
            }
            $whileNum++;
        }
        return $slen;
    }

    //来自于兴趣
    protected function fetchFromInterest($needLen)
    {
    }

    //来自于优质资源
    protected function fetchFromQuality($needLen)
    {
        $slen = 0;
        if ($needLen <= 0) return $slen;
        $totalKey = ProRedis::genKey('pool:total');
        $viewedKey = ProRedis::genKey("viewed:{$this->userMark}:total");
        $tmpKey = ProRedis::genKey("notwatch:{$this->userMark}");
        $this->redis->zUnion($tmpKey, [$viewedKey, $totalKey], [0, 1], 'MIN');
        $this->redis->zRemRangeByScore($tmpKey, 0, 0);
        $offset = 0;
        $whileNum = 0;
        $index_while_max = ProConf::get('index_while_max');
        while ($slen < $needLen) {
            if ($whileNum > $index_while_max) break;
            $length = $needLen - $slen;
            $index = $this->redis->zRevRange($tmpKey, $offset, $offset + $length - 1, true);
            if (empty($index)) break;
            foreach ($index as $id => $score) {
                $offset++;
                $this->perTimes['quality']['query_num']++;
                if (in_array($id, $this->ids)) continue;
                $this->ids[] = $id;
                $this->scores[(string)$id] = $score;
                $this->reason[(string)$id] = 'qly';
                $slen++;
            }
            $whileNum++;
        }
        $this->redis->del($tmpKey);
        return $slen;
    }

    protected function save()
    {
        $viewedKey = ProRedis::genKey("viewed:{$this->userMark}:total");
        $preViewedKey = ProRedis::genKey("preview:total");
        $totalKey = ProRedis::genKey('pool:total');
        $now = time();
        foreach ($this->ids as $id) {
            $score = $this->scores[(string)$id];
            if ($score === false || !isset($score)) {
                $score = $this->redis->zScore($totalKey, $id);
                $score = $score === false ? 1000 : $score;
                $this->scores[(string)$id] = $score;
            }
            $reason = isset($this->reason[(string)$id]) ? $this->reason[(string)$id] : 'unknow';
            $sort = $this->getSort(time(), $reason, $score);
            $this->redis->zAdd($this->indexKey, $sort, $id);
            $vrKey = ProRedis::genKey("vrel:{$id}");
            $this->redis->sAdd($vrKey, $this->userMark);
            $md5 = md5($this->indexKey . '' . $id);
            $reaKey = ProRedis::genKey("rea:{$md5}");
            $this->redis->set($reaKey, $reason, $this->indexExpire + 30);
            $this->redis->zAdd($viewedKey, $now, $id);
            $this->redis->zAdd($preViewedKey, $now, "{$this->userMark}||$id");
        }
        $int = mt_rand(0, 100);
        if ($int < 80) {
            $viewed_period = ProConf::get('viewed_period');
            $viewed_max_length = ProConf::get('viewed_max_length');
            $this->redis->repair($viewedKey, $viewed_max_length, $now - $viewed_period);
        }
        $this->redis->expire($this->indexKey, $this->indexExpire);
    }

    protected function getSort($now, $type, $videoScore)
    {
        $starttime = ProConf::get('index_sort_starttime');
        $interval = ProConf::get('index_sort_interval');
        $video_full_score = ProConf::get('video_full_score');
        $timeSort = $interval - (($now - $starttime) / $interval * $interval);
        $totalScore = (0.65 * $interval) + (0.12 * $video_full_score) + (0.36 * 10000);
        $videoSort = $videoScore;
        $maps = [
            'pgd' => 10000,
            'friends' => 7000,
            'int' => 5000,
            'qly' => 4000,
            'unknow' => 500
        ];
        $typeSort = (int)$maps[$type];
        $tmp = (0.65 * $timeSort) + (0.12 * $videoSort) + (0.36 * $typeSort);
        $sort = round($totalScore - $tmp);
        //特殊加成
        if ($type == 'pgd') {
            $sort -= (2 * 3600 * 0.65);
            $sort = $sort < 0 ? 0 : $sort;
        } else if ($type == 'friends') {
            $sort -= (0.5 * 3600 * 0.65);
            $sort = $sort < 0 ? 0 : $sort;
        }
        return $sort;
    }

    public static function remove($videoId)
    {
        $vrKey = ProRedis::genKey("vrel:{$videoId}");
        $redis = ProRedis::getInstance();
        $iterator = null;
        $redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        $total = 0;
        $index_remove_maxsscan = ProConf::get('index_remove_maxsscan');
        $index_remove_sscancount = ProConf::get('index_remove_sscancount');
        while ($arr_mems = $redis->sScan($vrKey, $iterator, '*', $index_remove_sscancount)) {
            foreach ($arr_mems as $userMark) {
                $indexKey = ProRedis::genKey("index:{$userMark}");
                $num = $redis->zRem($indexKey, $videoId);
                if ($num) {
                    $viewedKey = ProRedis::genKey("viewed:{$userMark}:total");
                    $redis->zRem($viewedKey, $videoId);
                }
                $redis->sRem($vrKey, $userMark);
                $total++;
            }
            if ($total >= $index_remove_maxsscan) break;
        }
        $redis->del($vrKey);
        return $total;
    }

    //记录性能参数
    protected function performance()
    {
        $init = mt_rand(0, 100);
        $totalQueryNum = 0;
        foreach ($this->perTimes as $name => $item) {
            if ($name != 'total') {
                $totalQueryNum += ((int)$item['query_num']);
                if ($init < 50) {
                    $this->performanceItem($name, $item);
                }
            }
        }
        $this->perTimes['total']['query_num'] = $totalQueryNum;
        if ($init < 80) {
            $this->performanceItem('total', $this->perTimes['total']);
        }
    }

    protected function performanceItem($name, $item)
    {
        $this->redis->incrBy(ProRedis::genKey("perf:index:{$name}:query_num"), (int)$item['query_num']);
        $this->redis->incrBy(ProRedis::genKey("perf:index:{$name}:actual_num"), (int)$item['actual_num']);
        $duration = $item['end'] - $item['start'];//ms
        if ((int)$item['query_num'] > 0) {
            $hitRate = round($item['actual_num'] / $item['query_num'], 6);
            $hitRateKey = ProRedis::genKey("perf:index:{$name}:minhitrate");
            $tmp = $this->redis->get($hitRateKey);
            if ($tmp === false || $hitRate < $tmp) $this->redis->set($hitRateKey, $hitRate);
        }
        if ($duration > 0) {
            $this->redis->incrBy(ProRedis::genKey("perf:index:{$name}:duration"), $duration);
            if ((int)$item['actual_num'] > 0) {
                $dduration = round($duration / $item['actual_num'], 6);
                $maxDDurationKey = ProRedis::genKey("perf:index:{$name}:maxdduration");//单位最大时长
                $tmp2 = $this->redis->get($maxDDurationKey);
                if ($dduration > $tmp2) $this->redis->set($maxDDurationKey, $dduration);
            }
        }
    }
}