<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;

class RedisCache extends Service
{
    protected $redis;
    protected $prefix;
    protected $redisKey;
    public static $redisCacheKeys = [
        'cache' => [
            'auth' => '权限',
            'table' => '表结构',
            'tree' => '类目',
            'region_tree' => '地址库',
            'ad' => '广告数据',
            'recommend' => '推荐数据',
            'apple_product_types' => 'apple内购类型$'
        ],
        'user' => []
    ];

    public function __construct($prefix)
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
        $this->prefix = $prefix;
        $this->redisKey = "clear_redis:{$this->prefix}";
    }

    public function start($type)
    {
        $redisCacheKeys = self::$redisCacheKeys[$this->prefix];
        if (!empty($type)) {
            if ($type == 'other') return $this->setError('请使用一键清空');
            if (!isset($redisCacheKeys[$type])) return $this->setError('key不支持');
        }
        $scan = $this->redis->hMGet($this->redisKey, ['status', 'scan_time']);
        if ($scan['status'] == 'start') return $this->setError('正在清空，请稍后');
        if (($scan['scan_time'] + 180) < time()) return $this->setError('请重新检测');
        $this->reset();
        $this->redis->hSet($this->redisKey, 'status', 'start');
        $data = ['type' => $type ? $type : ''];
        $data['prefix'] = $this->prefix;
        $data['sign'] = generate_sign($data);
        $httpClient = new HttpClient();
        $url = url('setting/clear_redis_callback', '', true, true);
        $httpClient->post($url, $data, 2);
        return true;
    }

    public function clear($type)
    {
        $redisCacheKeys = self::$redisCacheKeys[$this->prefix];
        if (!empty($type)) {
            if ($type == 'other') {
                $this->redis->hSet($this->redisKey, 'status', 'stop');
                $this->reset();
                return $this->setError('请使用一键清空');
            }
            if (!isset($redisCacheKeys[$type])) {
                $this->redis->hSet($this->redisKey, 'status', 'stop');
                $this->reset();
                return $this->setError('key不支持');
            }
        }
        if (empty($type)) {
            $key = "{$this->prefix}:*";
        } else {
            $typeName = self::$redisCacheKeys[$this->prefix][$type];
            $hasEnd = preg_match('/\$$/', $typeName);
            $key = "{$this->prefix}:{$type}" . ($hasEnd ? '' : ':*');
        }
        $status = $this->redis->hGet($this->redisKey, 'status');
        if ($status != 'start') {
            $this->redis->hSet($this->redisKey, 'status', 'stop');
            $this->reset();
            return $this->setError('status error');
        }
        $total = null;
        $prefix = $this->prefix;
        $this->eachRedisKeys($key, $total, function ($redis, $keys) use ($prefix, $type) {
            $num = call_user_func_array([$redis, 'del'], $keys);
            $redis->hIncrBy("clear_redis:{$prefix}", 'cleared_total', $num);
            if (empty($type)) {
                //需要匹配key确定类型
                $map = [];
                $redisCacheKeys = self::$redisCacheKeys[$prefix];
                foreach ($keys as $key2) {
                    $keyArr = explode(':', $key2);
                    $type2 = isset($redisCacheKeys[$keyArr[1]]) ? $keyArr[1] : 'other';
                    if (!isset($map[$type2])) $map[$type2] = 0;
                    $map[$type2]++;
                }
                foreach ($map as $t => $n) {
                    $redis->hIncrBy("clear_redis:{$prefix}", 'cleared_' . $t, $n);
                }
            } else {
                $redis->hIncrBy("clear_redis:{$prefix}", 'cleared_' . $type, $num);
            }
        });
        $this->redis->hSet($this->redisKey, 'status', 'stop');
        $this->reset();
        return true;
    }

    protected function reset()
    {
        $redisCacheKeys = self::$redisCacheKeys[$this->prefix];
        foreach ($redisCacheKeys as $rk => $rv) {
            $this->redis->hSet($this->redisKey, 'cleared_' . $rk, 0);
        }
        $this->redis->hSet($this->redisKey, 'cleared_other', 0);
        $this->redis->hSet($this->redisKey, 'cleared_total', 0);
    }

    //扫描
    public function scan()
    {
        $result = [];
        $total = null;
        $known = 0;
        $knownCleared = 0;
        $status = $this->redis->hGet($this->redisKey, 'status');
        if ($status == 'start') {
            $total = (int)$this->redis->hGet($this->redisKey, 'has_total');
        } else {
            $this->eachRedisKeys("{$this->prefix}:*", $total);//总量
            $this->redis->hSet($this->redisKey, 'has_total', (int)$total);
            $this->redis->hSet($this->redisKey, 'scan_time', time());
        }
        $redisCacheKeys = self::$redisCacheKeys[$this->prefix];
        foreach ($redisCacheKeys as $key => $name) {
            $num = null;
            if ($status == 'start') {
                $num = (int)$this->redis->hGet($this->redisKey, 'has_' . $key);
            } else {
                $hasEnd = preg_match('/\$$/', $name);
                if ($hasEnd) $name = preg_replace('/\$$/', '', $name);
                $this->eachRedisKeys("{$this->prefix}:{$key}" . ($hasEnd ? '' : ':*'), $num);
                $this->redis->hSet($this->redisKey, 'has_' . $key, (int)$num);
            }
            $known += $num;
            $cleared = (int)$this->redis->hGet($this->redisKey, 'cleared_' . $key);
            $knownCleared += $cleared;
            $result[] = [
                'name' => $name,
                'total' => $num,
                'key' => $key,
                'cleared' => $cleared
            ];
        }
        $clearedTotal = (int)$this->redis->hGet($this->redisKey, 'cleared_total');
        $result[] = [
            'name' => empty($redisCacheKeys) ? '全部' : '其它',
            'total' => $total - $known,
            'key' => 'other',
            'cleared' => $clearedTotal - $knownCleared
        ];
        return ['details' => $result, 'has_total' => $total, 'cleared_total' => $clearedTotal, 'status' => $status ? $status : 'stop'];
    }

    protected function eachRedisKeys($key, &$total, $callback = null)
    {
        $redis = $this->redis;
        $iterator = null;
        $total = 0;
        $count = 200;
        $keys = $redis->scan($iterator, $key, $count);
        $total += ($keys ? count($keys) : 0);
        if (isset($callback) && !empty($keys)) call_user_func_array($callback, [$redis, $keys]);
        while ($keys !== false) {
            $keys = $redis->scan($iterator, $key, $count);
            $total += ($keys ? count($keys) : 0);
            if (isset($callback) && !empty($keys)) call_user_func_array($callback, [$redis, $keys]);
        }
        return $total;
    }
}