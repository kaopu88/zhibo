<?php

namespace app\mq\callbacks;

use PhpAmqpLib\Message\AMQPMessage;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\IndexRecycling;
use bxkj_recommend\PoolManager;
use bxkj_recommend\PoolRecycling;
use bxkj_recommend\ProRedis;
use bxkj_recommend\UserIndex;
use bxkj_recommend\VideoUpdater;

class Prophet extends ConsumerCallback
{
    //更新器开始更新
    public function vupdater(AMQPMessage $msg)
    {
        $routing_key = $msg->delivery_info['routing_key'];
        $routing_key_arr = explode('.', $routing_key);
        $act = $routing_key_arr[2];
        $data = json_decode($msg->body, true);
        if (empty($data)) return false;
        $this->log->info('videoUpdater is act :' . $act);
        $videoUpdater = new VideoUpdater();
        if (!method_exists($videoUpdater, $act)) {
            $this->log->info('videoUpdater not act ' . $act);
            return false;
        }
        return call_user_func_array([$videoUpdater, $act], [$data]);
    }

    //为用户建立索引
    public function building(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (empty($data)) return $this->failed($msg, true);
        list($aliasType, $aliasId) = explode(":", $data['user_mark']);
        if (empty($aliasType) || empty($aliasId)) return $this->failed($msg, true);
        $userIndex = new UserIndex($aliasType, $aliasId);
        $res = $userIndex->building();
        if (!$res) return $this->failed($msg, true);
        $this->ack($msg);
    }
}
