<?php

namespace app\mq\callbacks;

use PhpAmqpLib\Message\AMQPMessage;
use bxkj_recommend\IndexRecycling;
use bxkj_recommend\PoolRecycling;
use bxkj_recommend\ProRedis;

class Recycling extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $routing_key = $msg->delivery_info['routing_key'];
        $routing_key_arr = explode('.', $routing_key);
        $type = $routing_key_arr[2];
        $data = json_decode($msg->body, true);
        if (empty($data) || empty($type)) return false;
        $funName = parse_name($type, 1, false) . 'Handler';
        if (!method_exists($this, $funName)) return false;
        return call_user_func_array([$this, $funName], [$data]);
    }

    //回收过期的索引
    protected function indexHandler($data)
    {
        $startTime = msectime();
        $indexRecycling = new IndexRecycling();
        $total = $indexRecycling->recycling();
        $endTime = msectime();
        $redis = ProRedis::getInstance();
        $perfKey = ProRedis::genKey("perf:recycling_index:total");
        $redis->incrBy($perfKey, $total);
        $duration = $endTime - $startTime;
        if ($duration > 0) {
            $perfKey2 = ProRedis::genKey("perf:recycling_index:duration");
            $duration2 = $redis->get($perfKey2);
            $redis->set($perfKey2, max($duration2, $duration));
        }
        return true;
    }

    //回收过期的池子视频
    protected function poolHandler($data)
    {
        $startTime = msectime();
        $poolRecycling = new PoolRecycling();
        $total = $poolRecycling->recycling();
        $endTime = msectime();
        $duration = $endTime - $startTime;
        $perfKey = ProRedis::genKey("perf:recycling_pool:total");
        $redis = ProRedis::getInstance();
        $redis->incrBy($perfKey, $total);
        if ($duration > 0) {
            $perfKey2 = ProRedis::genKey("perf:recycling_pool:duration");
            $duration2 = $redis->get($perfKey2);
            $redis->set($perfKey2, max($duration2, $duration));
        }
        return true;
    }

}
