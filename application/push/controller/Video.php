<?php

namespace app\push\controller;

use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\PoolManager;
use bxkj_recommend\ProRedis;
use think\Db;

class Video extends Api
{
    public function indexing()
    {
        exit();
        $this->persistent();
        $id = 129590;
        //$id = 0;

        /*$maps = [];
        $map2s = [];
        $tags = Db::name('video_tags')->where(['pid' => 2])->limit(20)->select();
        foreach ($tags as $tag) {
            $maps[] = $tag['id'];
            $map2s[] = $tag['name'];
        }*/

        /*Db::name('prophet_tags')->where([['id', 'neq', '']])->delete();
        $redis = ProRedis::getInstance();

        $iterator = null;
        $redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        $delKeyNum = 0;
        while ($arr_mems = $redis->scan($iterator, '*', 500)) {
            foreach ($arr_mems as $key) {
                if (!preg_match('/^viewed/', $key)) {
                    $delRes = $redis->del($key);
                    if ($delRes) $delKeyNum++;
                }
            }
        }
        var_dump($delKeyNum);
        exit();*/

        $offset = 0;
        $pool = new PoolManager();
        $total = 0;
        $failed = 0;

        $updateInit = [
            'switch_sum' => 0,
            'played_out_sum' => 0,
            'general_sum' => 0,
            'sco_share_sum' => 0,
            'sco_down_sum' => 0,
            'sco_zan_sum' => 0,
            'sco_comment_sum' => 0,
            'watch_duration' => 0,
            'watch_sum' => 0,
            'score' => 0
        ];
        Db::name('video')->where([['id', 'neq', 0]])->update($updateInit);

        while (true) {
            $result = Db::name('video')->limit($offset, 600)->where([['id', 'gt', $id]])->select();
            if (empty($result)) break;
            $offset += count($result);
            foreach ($result as $item) {
                /*$tagLen = mt_rand(1, 3);
                $tags = [];
                $tag_names = [];
                for ($ti = 0; $ti < $tagLen; $ti++) {
                    $mIndex = mt_rand(0, count($maps) - 1);
                    $tags[] = $maps[$mIndex];
                    $tag_names[] = $map2s[$mIndex];
                }
                $tags = implode(',', array_unique($tags));
                $tag_names = implode(',', array_unique($tag_names));
                $update = [
                    'tags' => $tags,
                    'tag_names' => $tag_names
                ];
                Db::name('video')->where(['id' => $item['id']])->update($update);
                Db::name('video_unpublished')->where(['id' => $item['id']])->update($update);
                $item = array_merge($item, $update);*/

                $update2 = $updateInit;

                $res2 = Db::name('video')->where(['id' => $item['id']])->update($update2);
                if ($res2) $item = array_merge($item, $update2);

                try {
                    $pool->push(new \bxkj_recommend\model\Video($item));
                } catch (Exception $exception) {
                    $failed++;
                    continue;
                }
                $total++;
                usleep(1000);
            }
        }
        echo "total:{$total}<br/>";
        echo "failed:{$failed}<br/>";
    }

    public function copy()
    {

        $proConfig = [
            'type' => 'mysql',
            'hostname' => '',
            'database' => '',
            'username' => '',
            'password' => '',
            'debug' => false,
            'hostport' => 3306,
            'prefix' => '',
            'charset' => '',
        ];
        //同步标签
        /*Service::startTrans();
        Db::name('video_tags')->where([['id', 'neq', '0']])->delete();
        $tags = Db::connect($proConfig)->name('video_tags')->select();
        foreach ($tags as $tag) {
            Db::name('video_tags')->insert($tag);
        }
        Service::commit();*/

        $users = Db::name('user')->field('user_id')->select();
        $userIds = array_column($users, 'user_id');

        $gid = 2000;
        $videos = Db::connect($proConfig)->name('video')->limit(1500, 500)->order('id desc')->select();
        $max = count($userIds) - 1;
        $total = 0;
        foreach ($videos as $video) {
            Service::startTrans();
            $uVideo = Db::connect($proConfig)->name('video_unpublished')->where(['id' => $video['id']])->find();
            if (empty($uVideo)) {
                Service::rollback();
                continue;
            }
            $userId = $userIds[mt_rand(0, $max)];
            $video['id'] = $gid;
            $video['describe'] = 'CP190530' . $video['describe'];
            $video['user_id'] = $userId;
            $uVideo['id'] = $gid;
            $uVideo['describe'] = 'CP190530' . $uVideo['describe'];
            $uVideo['user_id'] = $userId;

            if (!empty($video['location_id'])) {
                $location = Db::connect($proConfig)->name('location')->where(['id' => $video['location_id']])->find();
                if ($location) {
                    unset($location['id']);
                    $locationId = Db::name('location')->insertGetId($location);
                    if ($locationId) {
                        $video['location_id'] = $locationId;
                        $uVideo['location_id'] = $locationId;
                    }
                }
            }

            if (!empty($video['music_id'])) {
                $music = Db::connect($proConfig)->name('music')->where(['id' => $video['music_id']])->find();
                if ($music) {
                    unset($music['id']);
                    $music['user_id'] = $userId;
                    $musicId = Db::name('music')->insertGetId($music);
                    if ($musicId) {
                        $video['music_id'] = $musicId;
                        $uVideo['music_id'] = $musicId;
                    }
                }
            }
            $res = Db::name('video')->insert($video);
            if (!$res) {
                Service::rollback();
                continue;
            }
            Db::name('video_unpublished')->insertGetId($uVideo);
            Service::commit();
            $filmNum = Db::name('video')->where(['user_id' => $userId])->count();
            Db::name('user')->where(['user_id' => $userId])->update(['film_num' => $filmNum]);
            $total++;
            $gid++;
        }

        echo 'success ' . $total;


    }

    public function clear()
    {
        exit();
        if (RUNTIME_ENVIROMENT != 'testing') {
            echo 'not testing';
            exit();
        }
        $redis = RedisClient::getInstance();
        $keys = $redis->keys('BG_FILM:ADMIRE*');
        $keys = $keys ? $keys : [];
        foreach ($keys as $key) {
            $redis->del($key);
        }
        $keys = $redis->keys('fans:*');
        $keys = $keys ? $keys : [];
        foreach ($keys as $key) {
            $redis->del($key);
        }
        $keys = $redis->keys('follow:*');
        $keys = $keys ? $keys : [];
        foreach ($keys as $key) {
            $redis->del($key);
        }
        $users = Db::name('user')->field('user_id')->select();
        foreach ($users as $user) {
            $film_num = Db::name('video')->where(['user_id' => $user['user_id']])->count();
            $userData = [
                'film_num' => $film_num,
                'like_num' => 0,
                'fans_num' => 0,
                'follow_num' => 0,
                'collection_num' => 0,
                'download_num' => 0
            ];
            Db::name('user')->where(['user_id' => $user['user_id']])->update($userData);
        }
        Db::name('video_comment')->where([['id', 'neq', 0]])->delete();
        Db::name('follow')->where([['id', 'neq', 0]])->delete();
        Db::name('video')->where([['id', 'neq', 0]])->update(['zan_sum' => 0, 'comment_sum' => 0, 'play_sum' => 0, 'watch_sum' => 0, 'watch_duration' => 0, 'collection_sum' => 0, 'down_sum' => 0, 'share_sum' => 0]);
        echo 'ok';
        exit();
    }
}
