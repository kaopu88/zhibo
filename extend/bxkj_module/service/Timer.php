<?php

namespace bxkj_module\service;

use bxkj_common\RedisClient;

class Timer extends Service
{
    public function add($inputData)
    {
        $trigger_time = $inputData['trigger_time'];
        $cycle = !empty($inputData['cycle']) ? $inputData['cycle'] : 0;
        $interval = $inputData['interval'];
        $data = is_array($inputData['data']) ? json_encode($inputData['data']) : trim($inputData['data']);
        $row = [];
        $method = $inputData['method'] ? strtolower($inputData['method']) : 'get';
        if (!validate_regex($trigger_time, 'integer') || empty($inputData['url']) || $trigger_time < 0 || (isset($cycle) && $cycle < -1)) {
            return $this->setError('缺少必要参数或者参数不正确');
        }
        if (!in_array($method, ['get', 'post'])) return $this->setError('回调类型不正确');
        if (!preg_match('/^(http|https)\:\/\/.+/', $inputData['url'])) {
            return $this->setError('URL无效');
        }
        if ($cycle != 0) {
            if ((int)$interval < 5) return $this->setError('间隔时间不能小于5s');
            $row['interval'] = $interval;
        }
        $key = sha1(uniqid() . get_ucode(8));
        $row['key'] = $key;
        $row['method'] = $method;
        $row['url'] = $inputData['url'];
        $row['trigger_time'] = $trigger_time;
        $row['first_trigger_time'] = $trigger_time;
        $row['trigger_num'] = 0;
        $row['cycle'] = $cycle;
        $row['data'] = $data ? $data : '';
        $row['add_time'] = time();
        $redis = RedisClient::getInstance();
        $key2 = "timer:task:{$key}";
        if ($redis->hMset($key2, $row)) {
            $redis->zAdd("timer:line", (int)$trigger_time, $key);
        }
        return $key;
    }

    public function edit($inputData)
    {
        $key = $inputData['key'];
        if (empty($key)) return $this->setError('KEY不能为空');
        $key2 = "timer:task:{$key}";
        $redis = RedisClient::getInstance();
        if (!$redis->exists($key2)) return $this->setError('定时器不存在');
        $trigger_time = $inputData['trigger_time'];
        $cycle = $inputData['cycle'];
        $interval = $inputData['interval'];
        $data = is_array($inputData['data']) ? json_encode($inputData['data']) : trim($inputData['data']);
        $row = [];
        $method = strtolower($inputData['method']);
        if (!validate_regex($trigger_time, 'integer') || empty($inputData['url']) || $trigger_time < 0 || (isset($cycle) && $cycle < -1)) {
            return $this->setError('缺少必要参数或者参数不正确');
        }
        if (!in_array($method, ['get', 'post'])) return $this->setError('回调类型不正确');
        if (!preg_match('/^(http|https)\:\/\/.+/', $inputData['url'])) {
            return $this->setError('URL无效');
        }
        if ($cycle != 0) {
            if ((int)$interval < 5) return $this->setError('间隔时间不能小于5s');
            $row['interval'] = $interval;
        }
        $row['key'] = $key;
        $row['method'] = $method;
        $row['url'] = $inputData['url'];
        $row['trigger_time'] = $trigger_time;
        $row['first_trigger_time'] = $trigger_time;
        $row['trigger_num'] = 0;
        $row['cycle'] = $cycle;
        $row['data'] = $data ? $data : '';
        $row['add_time'] = time();
        if ($redis->hMset($key2, $row)) {
            $redis->zAdd("timer:line", (int)$trigger_time, $key);
        }
        return $key;
    }

    public function getTotal()
    {
        $redis = RedisClient::getInstance();
        return $redis->getSZTotal('timer:line');
    }

    public function getList($get, $offset = 0, $length = 10)
    {
        $redis = RedisClient::getInstance();
        $order = empty($get['sort']) ? 'asc' : strtolower($get['sort']);
        if ($get['keyword'] != '') {
            $index = [];
            $score = $redis->zScore('timer:line', $get['keyword']);
            if ($score !== false) {
                $index[] = [
                    'trigger_time' => $score,
                    'key' => $get['keyword']
                ];
            }
        } else {
            $index = $redis->getSZList('timer:line', $order, $offset, $length, [
                'score' => 'trigger_time',
                'member' => 'key'
            ]);
        }
        $now = time();
        foreach ($index as &$item) {
            $task = $redis->hGetAll("timer:task:{$item['key']}");
            if ($task) $item = array_merge($item, $task);
            $diff = $item['trigger_time'] - $now;
            $item['diff'] = $diff;
            $item['trigger_str'] = $diff > 0 ? time_str($diff) : '已过期';
        }
        return $index;
    }


    public function remove($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $redis = RedisClient::getInstance();
        $total = 0;
        foreach ($ids as $key) {
            if (empty($key)) continue;
            $key2 = "timer:task:{$key}";
            if ($redis->zRem("timer:line", $key)) {
                if ($redis->del($key2)) {
                    $total++;
                }
            }
        }
        return $total;
    }

    public function getInfo($key)
    {
        $redis = RedisClient::getInstance();
        $task = $redis->hGetAll("timer:task:{$key}");
        if ($task) {
            $score = $redis->zScore('timer:line', $task['key']);
            $task['trigger_time'] = date('Y-m-d H:i:s', $score);
        }
        return $task;
    }

}