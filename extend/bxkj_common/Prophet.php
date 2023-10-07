<?php

namespace bxkj_common;

use bxkj_recommend\ProConf;
use think\Db;
use think\facade\Config;

class Prophet
{
    protected static $redis;
    protected static $videos = [];
    protected $aliasType;
    protected $aliasId;
    protected $userMark;
    protected $fields;
    protected $indexKey;
    protected $sessionKey;
    protected $pOffsetKey;
    protected $session;
    protected $lossIds = [];
    protected $rabbitChannel;
    protected $instockEarly;//库存预警值
    protected $indexExpire;//索引有效期 s
    protected $shortExpire;//短时记忆有效期
    protected $userId;
    protected $meid;
    protected $adPoolKey;//广告池的key
    protected $ai = true;

    public function __construct($userId = '', $meid = '')
    {
        $this->userId = $userId;
        $this->meid = $meid ? $meid : '';
        if (!empty($userId)) {
            $this->aliasType = 'user';
            $this->aliasId = $userId;
        } else {
            $this->aliasType = 'meid';
            $this->aliasId = $meid;
        }
        $this->instockEarly = ProConf::get('instock_early');
        $this->indexExpire = ProConf::get('index_expire');
        $this->shortExpire = ProConf::get('index_short_expire');
        $this->userMark = "{$this->aliasType}:{$this->aliasId}";
        if (!isset(self::$redis)) self::getRedisClient();
        $this->indexKey = self::genKey("index:{$this->userMark}");
        $this->sessionKey = self::genKey("session:{$this->userMark}");
        $this->pOffsetKey = self::genKey("poffset:{$this->userMark}");
        $this->adPoolKey = self::genKey("pool:ad_list{$this->userMark}");
        $this->session = self::$redis->hGetAll($this->sessionKey);
        if (false) self::$redis = new \Redis();
        if (empty($this->session)) {
            $this->session = [];
            if ($this->session) self::$redis->hMset($this->sessionKey, $this->session);
        }
    }

    public static function getRedisClient()
    {
        $redisConfig = Config::pull('pro_redis');
        $host = $redisConfig['host'];
        $port = $redisConfig['port'];
        $db = $redisConfig['db'];
        $auth = $redisConfig['auth'];
        $timeout = isset($redisConfig['timeout']) ? $redisConfig['timeout'] : 5;
        if (!isset(self::$redis)) {
            self::$redis = new \Redis();
            //相同地址不同库 connect和pconnect冲突
            if ($redisConfig['persistence']) {
                self::$redis->pconnect($host, $port, (int)$timeout, 'PR');
            } else {
                self::$redis->connect($host, $port, (int)$timeout);
            }
            if (!empty($auth)) self::$redis->auth($auth);
            self::$redis->select($db);
        }
        return self::$redis;
    }

    public static function genKey($name)
    {
        $key = $name;
        return $key;
    }

    public function fields($fields)
    {
        $arr = [];
        if ($fields) {
            $arr = is_string($fields) ? str_to_fields($fields) : $fields;
            if (!empty($arr) && !in_array('id', $arr)) $arr[] = 'id';
        }
        $this->fields = $arr;
        return $this;
    }

    public function getList($offset = 0, $length = 10)
    {   
        $ids = [];
        $scores = [];//分值映射表
        if ($this->ai) {
            $indexRes = $this->getFromIndex($length);
            $ids = array_merge($ids, $indexRes['ids']);
            $scores = array_merge($scores, $indexRes['scores']);
            //索引生产不及时导致视频不足则从总池子里面取视频补充
            $missing = $length - count($ids);
            if ($missing > 0) {
                $poolRes = $this->getFromPool($missing);
                $ids = array_merge($ids, $poolRes['ids']);
                $scores = array_merge($scores, $poolRes['scores']);
            }
        }
        $videos = $this->getVideos($ids);
        $sMemKey = self::genKey("s_mem:{$this->userMark}");
        foreach ($videos as $video) {
            self::$redis->setBit($sMemKey, (int)$video['id'], 1);//短时记忆（体积超小速度极快）
        }
        self::$redis->expire($this->sessionKey, $this->indexExpire);//会话2小时
        self::$redis->expire($sMemKey, $this->shortExpire);//短暂记忆10分钟
        $randLen = $length - count($ids);
        if ($randLen > 0) {
            $randVideos = $this->getRandVideos($randLen);
            $videos = array_merge($videos, $randVideos);
        }
        //清除已丢失的视频
        if (!empty($this->lossIds)) {
            if ($this->initRabbitChannel()) {
                foreach ($this->lossIds as $lossId) {
                    $this->rabbitChannel->exchange('main')->send('video.update.offline', ['id' => $lossId, 'scene' => 'loss']);
                }
            }
        }
        if ($this->ai) {
            //补充用户索引
            $instock = self::$redis->zCount($this->indexKey, '-inf', '+inf');//索引库存量
            if ($instock < $this->instockEarly) {
                $bdingKey = self::genKey("bding:{$this->userMark}");
                $building = self::$redis->exists($bdingKey);
                if (!$building) {
                    if ($this->initRabbitChannel()) {
                        $this->rabbitChannel->exchange('main')->send('prophet.building', ['user_mark' => $this->userMark]);
                    }
                }
            }
            if ($instock > 0) {
                self::$redis->expire($this->indexKey, $this->indexExpire);//用户索引有效期2个小时
            }
        }
        //释放资源
        if ($this->rabbitChannel) {
            $this->rabbitChannel->close();
            $this->rabbitChannel = null;
        }
        self::$videos = [];
        if ($this->ai) $this->statistics($videos);
        return $videos;
    }

    protected function getFromIndex($length)
    {
        $ids = [];
        $scores = [];
        //最先进入优先（分值是进入时间）
        $result = self::$redis->zRange($this->indexKey, 0, $length - 1, true);
        $result = $result ? $result : [];
        $preViewedKey = self::genKey("preview:total");
        foreach ($result as $id => $score) {
            $ids[] = $id;
            //清除视频与用户之间的关联
            $vrKey = self::genKey("vrel:{$id}");
            self::$redis->sRem($vrKey, $this->userMark);
            $scores[(string)$id] = $score;
            self::$redis->zRem($this->indexKey, $id);
            $md5 = md5($this->indexKey . '' . $id);
            $reaKey = self::genKey("rea:{$md5}");
            self::$redis->del($reaKey);
            self::$redis->zRem($preViewedKey, "{$this->userMark}||$id");
        }
        return ['ids' => $ids, 'scores' => $scores];
    }

    //从优质视频池子中直接获取
    protected function getFromPool($length)
    {
        $ids = [];
        $scores = [];
        $poolOffset = $this->getPoolOffset();
        $poolKey = self::genKey("pool:total");
        $tmpLen = $length - count($ids);
        $tmpOffset = $poolOffset;
        $tryNum = 0;
        $index_fetch_retrymax = ProConf::get('index_fetch_retrymax');
        while (count($ids) < $length) {
            if ($tryNum > $index_fetch_retrymax) break;
            $tmpIds = [];
            $result = self::$redis->zRevRange($poolKey, $tmpOffset, $tmpOffset + $tmpLen - 1, true);
            if (empty($result)) break;
            $tmpOffset += count($result);
            foreach ($result as $id => $score) {
                $scores[(string)$id] = $score;
                $tmpIds[] = $id;
            }
            $this->shortMemoryFilter($tmpIds);
            $lossIds = $this->lossFilter($tmpIds);
            if ($lossIds) $this->lossIds = array_merge($this->lossIds, $lossIds);
            $ids = array_merge($ids, $tmpIds);
            $tmpLen = $length - count($ids);
            $tryNum++;
            usleep(1000);
        }
        $incr = $tmpOffset - $poolOffset;
        if ($incr != 0) {
            self::$redis->incrBy($this->pOffsetKey, $incr);
        }
        return ['ids' => $ids, 'scores' => $scores];
    }

    protected function getPoolOffset()
    {
        $pOffset = self::$redis->get($this->pOffsetKey);
        $index_fetch_offsetmax = ProConf::get('index_fetch_offsetmax');
        $index_fetch_offsetttl = ProConf::get('index_fetch_offsetttl');
        if (empty($pOffset) || $pOffset > $index_fetch_offsetmax) {
            $pOffset = mt_rand(0, 1500);
            self::$redis->set($this->pOffsetKey, $pOffset, $index_fetch_offsetttl);
        }
        return $pOffset;
    }

    //短时记忆过滤
    protected function shortMemoryFilter(&$ids)
    {
        $memIds = [];
        $ids = array_unique($ids);
        $key = self::genKey("s_mem:{$this->userMark}");
        foreach ($ids as $i => $id) {
            $bit = self::$redis->getBit($key, $id);
            if ($bit > 0) {
                $memIds[] = $id;
                unset($ids[$i]);
            }
        }
        return $memIds;
    }

    protected function lossFilter(&$ids)
    {
        $lossIds = [];
        $ids = array_unique($ids);
        if (!empty($ids)) {
            $videoList = $this->getVideos($ids);
            $videoIds = array_column($videoList, 'id');
            $diffIds = array_diff($ids, $videoIds);
            foreach ($diffIds as $diffId) {
                $i = array_search($diffId, $ids);
                if ($i !== false) {
                    $lossIds[] = $ids[$i];
                    unset($ids[$i]);
                }
            }
        }
        return $lossIds;
    }

    //最低保证
    protected function getRandVideos($length)
    {
        $videos = Db::name('video')->where(['is_ad' => 0])->field($this->fields)->orderRand()->limit($length)->select();
        return $videos ? $videos : [];
    }

    public function getVideos($ids)
    {
        $videos = [];
        $unknowIds = [];
        foreach ($ids as $id) {
            if (!isset(self::$videos[(string)$id])) $unknowIds[] = $id;
        }
        if (!empty($unknowIds)) {
            $videoList = Db::name('video')->where(['is_ad' => 0])->field($this->fields)->whereIn('id', $unknowIds)->limit(count($unknowIds))->select();
            $videoList = $videoList ? $videoList : [];
            foreach ($unknowIds as $unknowId) {
                $v = false;
                foreach ($videoList as $item) {
                    if ($item['id'] == $unknowId) {
                        $v = $item;
                        break;
                    }
                }
                self::$videos[(string)$unknowId] = $v;
            }
        }
        foreach ($ids as $id) {
            if (!empty(self::$videos[(string)$id])) {
                $videos[] = self::$videos[(string)$id];
            }
        }
        return $videos;
    }

    protected function initRabbitChannel()
    {
        if (!$this->rabbitChannel) {
            try {
                $this->rabbitChannel = new RabbitMqChannel();
            } catch (\Exception $exception) {
                $this->ai = false;
                return false;
            }
        }
        return $this->rabbitChannel;
    }

    //统计
    protected function statistics($videos)
    {
        $vids = array_column($videos, 'id');
        if (!empty($vids)) $this->statConsSets($vids);
        $this->statAudSets();
        $this->statOnline();
    }

    //总消费视频量（不计重复视频）
    protected function statConsSets($vids)
    {
        $hour = date('YmdH');
        $day = date('Ymd');
        $hourKey = "stat:cons_sets:h:{$hour}";
        $dayKey = "stat:cons_sets:d:{$day}";
        $hRes = self::$redis->pfAdd($hourKey, $vids);
        if (!$hRes) return true;
        self::$redis->pfAdd($dayKey, $vids);
    }

    //用户统计
    protected function statAudSets()
    {
        if ($this->aliasType == 'user') {
            $this->statAudSetsItem('user', $this->aliasId);//独立用户数
        }
        $this->statAudSetsItem('meid', $this->meid);//独立设备量
    }

    protected function statAudSetsItem($type, $value)
    {
        $hour = date('YmdH');
        $day = date('Ymd');
        $month = date('Ym');
        $hourKey = "stat:aud_sets:{$type}:h:{$hour}";
        $dayKey = "stat:aud_sets:{$type}:d:{$day}";
        $monthKey = "stat:aud_sets:{$type}:m:{$month}";
        $hisKey = "stat:aud_sets:{$type}:his";
        $hRes = self::$redis->pfAdd($hourKey, [$value]);
        if (!$hRes) return true;
        $dRes = self::$redis->pfAdd($dayKey, [$value]);
        if (!$dRes) return true;
        $mRes = self::$redis->pfAdd($monthKey, [$value]);
        if (!$mRes) return true;
        self::$redis->pfAdd($hisKey, [$value]);
    }

    //在线人数
    protected function statOnline()
    {
        $int = mt_rand(0, 100);
        $key = "stat:online:{$this->aliasType}";
        self::$redis->zAdd($key, time(), $this->userMark);
        if ($int < 45) {
            self::$redis->zRemRangeByScore($key, 0, time() - (30 * 86400));
        }
    }

    /**
     * 获取广告视频
     * @param $num 数量
     * @param $expire 缓存时间
     */
    public function getAdList($num = 1, $expire = 600)
    {
        $vids = json_decode(self::$redis->get($this->adPoolKey));
        $advideos = Db::name('video')->where('is_ad', '>', 0)->field($this->fields)->orderRand()->limit($num)->select();

        if (empty($advideos)) return false;
        if (!empty($advideos) && !empty($vids) && is_array($vids)) {
            foreach ($advideos as $key => $value) {
                if (in_array($value['id'], $vids)) {
                    unset($advideos[$key]);
                }
            }
        }

        $adId = array_column($advideos, 'id');
        if (is_array($vids)) $adId = array_merge(array_column($advideos, 'id'), $vids);
        if (!empty($advideos)) self::$redis->set($this->adPoolKey, json_encode($adId), $expire);
        if ($this->ai) $this->statistics($advideos);
        return $advideos ? $advideos : [];
    }

    public function getGoodsList($page = 1, $pageSize =10, $goodsType =0, $cateId = 0, $num = 10)
    {
        $goodsVideos = Db::name('video')->where('goods_id', '>', 0)->where(['cate_id' => $cateId, 'goods_type' => $goodsType])->field($this->fields)->limit(($page- 1) * $pageSize, $page * $pageSize)->select();
        return $goodsVideos ? $goodsVideos : [];
    }

    /**
     * @param $array
     * 对数组进行加时间
     */
    protected function addTime($array)
    {
        if (empty($array) || !is_array($array)) return false;
        $tmp = [];
        foreach ($array as $key => $value) {
            $tmp[] = ['vid' => $value, 'time' => time()];
        }
        return $tmp;
    }
}