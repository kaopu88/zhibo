<?php

namespace app\push\controller;

use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_recommend\ProRedis;
use think\Db;

class VideoUpdater extends Api
{
    public function start()
    {
        $type = input('type');
        if (empty($type)) return json_error('type not exists');
        $videoUpdater = new \bxkj_recommend\VideoUpdater();
        $config = $videoUpdater->getFreConfigByType($type);
        if (empty($config)) return json_error('type not exists');
        $ingKey = \bxkj_recommend\VideoUpdater::getKey($type, 'ing');
        $unKey = \bxkj_recommend\VideoUpdater::getKey($type, 'un');
        $stateKey = \bxkj_recommend\VideoUpdater::getKey($type, 'state');
        $redis = ProRedis::getInstance();
        $startKey = \bxkj_recommend\VideoUpdater::getKey($type, 'start');
        $now = time();
        if ($redis->exists($ingKey)) {
            $len = $redis->lLen($unKey);
            if ($len > 0) {
                $state = $redis->exists($stateKey);
                //工作进程已经崩溃
                if (!$state) {
                    do {
                        $id = $redis->rpoplpush($unKey, $ingKey);
                        if (mt_rand(0, 100) > 50) usleep(1000);
                    } while ($id !== false);
                    $redis->rename($ingKey, $unKey);
                    sleep(1);
                } else {
                    return json_error('type processing');
                }
            } else {
                $redis->rename($ingKey, $unKey);
                sleep(1);
            }
        }
        $process = $config['process'] < 1 ? 1 : $config['process'];
        $redis->set($startKey, $now);
        $redis->del(\bxkj_recommend\VideoUpdater::getKey($type, 'finished'));
        $rabbitChannel = new RabbitMqChannel(['prophet.vupdater']);
        $total = 0;
        for ($i = 0; $i < $process; $i++) {
            $rabbitChannel->send('prophet.vupdater.start', ['type' => $type, 'index' => $i], 2, 600000);
            usleep(500000);//500毫秒
            $total++;
        }
        $rabbitChannel->close();
        return json_success($total, 'start success');
    }

    public function clear_rejection()
    {
        $lastTime = time() - (10 * 24 * 3600);
        $where = [
            ['create_time', 'lt', $lastTime],
            ['audit_status', 'eq', '3']
        ];
        $videos = Db::name('video_unpublished')->field('id')->where($where)->limit(100)->select();
        $videos = $videos ? $videos : [];
        $rabbitChannel = new RabbitMqChannel(['video.delete']);
        $total = 0;
        foreach ($videos as $video) {
            Db::name('video_unpublished')->where(['id' => $video['id']])->update(['delete_time' => time()]);
            $rabbitChannel->send('video.delete', ['id' => $video['id']]);
            $total++;
        }
        $rabbitChannel->close();
        return json_success($total, 'success');
    }

}
