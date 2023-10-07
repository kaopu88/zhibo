<?php

namespace app\mq\callbacks;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\TencentcloudVod;
use bxkj_module\service\UserRedis;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\PoolManager;
use bxkj_recommend\model\Video;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use think\Db;

class VideoUpdate extends ConsumerCallback
{

    //视频更新处理
    public function process(AMQPMessage $msg)
    {
        $routing_key = $msg->delivery_info['routing_key'];
        $routing_key_arr = explode('.', $routing_key);
        $type = $routing_key_arr[2];
        $params = json_decode($msg->body, true);
        if (!empty($params) && !empty($type)) {
            $data = isset($params['type']) && is_array($params['data']) ? $params['data'] : $params;
            $funName = parse_name($type, 1, false) . 'Handler';
            if (method_exists($this, $funName)) {
                $res = call_user_func_array([$this, $funName], [$data]);
                if (!$res) return $this->failed($msg, true);
            }
        }
        $this->ack($msg);
    }

    //下架
    public function offlineHandler($data)
    {
        $this->del($data);
        return true;
    }

    //回收
    public function recyclingHandler($data)
    {
        $idsStr = $data['ids'];
        if (!empty($idsStr)) {
            $ids = explode(',', $idsStr);
            $pool = new PoolManager();
            foreach ($ids as $id) {
                if (empty($id)) continue;
                try {
                    $pool->remove($id);
                } catch (\Exception $exception) {
                    $this->log->info('VideoUpdate recycling error ' . $exception->getMessage());
                    continue;
                }
            }
        }
        return true;
    }

    public function refreshHandler($data)
    {
        $video = $this->del($data);
        $rabbitMq = new RabbitMqChannel(['video.create_publish']);
        $rabbitMq->exchange('main')->sendOnce('video.create.publish', ['id' => $data['id']]);
        return true;
    }

    protected function del($data)
    {
        try {
            $pool = new PoolManager();
            $pool->remove($data['id']);
            $pool->removeNew($data['id']);
        } catch (\Exception $exception) {
            $this->log->info('VideoUpdate remove error');
        }
        $video = Db::name('video')->where(['id' => $data['id']])->find();
        $num = Db::name('video')->where(['id' => $data['id']])->delete();
        Db::name('video_unpublished')->where(['id' => $data['id']])->update(['status' => '0']);
        if ($num && $video) {
            $filmNum = Db::name('video')->where(['user_id' => $video['user_id']])->count();
            $userUpdate = ['film_num' => $filmNum];
            Db::name('user')->where(['user_id' => $video['user_id']])->update($userUpdate);
            UserRedis::updateData($video['user_id'], $userUpdate);
            return $video;
        } else {
            return null;
        }
    }

}
