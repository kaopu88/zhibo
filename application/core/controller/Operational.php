<?php

namespace app\core\controller;

use bxkj_common\RedisClient;
use think\Db;
use think\Request;

class Operational extends Controller
{

    /**
     *
     * 1、虚拟运营数据的增加的前提是基于已经审核通过的视频；
     * 2、脚本每10分钟执行一次；
     * 3、只处理1周内的视频
     * 4、播放量=320~520之间的随机值；
     * 5、点赞量=播放量的70%~40%；
     * 6、收藏量=播放量的10%~5%；；
     * 7、分享量=播放量的9%~2%；；
     *
     */
    public function virtualOperationalData(Request $request)
    {
        $params = $request->param();

        $length = isset($params['length']) ? $params['length'] : 20;

        $now = time();

        $I7time = strtotime(date("Y-m-d 18:00:00"));

        $o0time = strtotime(date("Y-m-d 23:59:59"));

        if ($now>$I7time && $now < $o0time) return;

        $week = strtotime(date("Y-m-d",strtotime("-7 day")));

        $redis = RedisClient::getInstance();

        $last_id = $redis->get('cache:virtualOperational');

        $where = [['create_time','egt', $week]];

        !empty($last_id) && $where[] = ['id', 'lt', $last_id];

        $rs = Db::name('video')
            ->field('id, user_id, create_time')
            ->where($where)
            ->order('id desc')
            ->limit($length)
            ->select();

        if (empty($rs))
        {
            $rs = Db::name('video')
                ->field('id, user_id, create_time')
                ->where('create_time','egt', $week)
                ->order('id desc')
                ->limit($length)
                ->select();
        }

        $video_update = [];

        $user_update = [];

        $num = 1;

        //每10分钟处理一次的基值
        $base_value = [
            [10, 30],
            [15, 35],
            [20, 40],
            [25, 50],
            [22, 35],
            [13, 32],
            [5, 15],
        ];

        foreach ($rs as $value)
        {
            $diff_time = $now - $value['create_time'];

            while ($num < 7)
            {
                if ($diff_time < (86400*$num))
                {
                    $rand = $base_value[$num-1];
                    break;
                }
                $num++;
            }

            if (!isset($rand)) continue;

            $play = mt_rand($rand[0], $rand[1]);

            $zan = floor($play*mt_rand(15, 30)/100);

            $zan < 1 && $zan = 1;

            $video_tmp = [
                'id' => $value['id'],
                'play_sum2' => Db::raw('play_sum2+'.(int)$play),
                'zan_sum2' => Db::raw('zan_sum2+'.(int)$zan),
            ];

            $user_tmp = [
                'user_id' => $value['user_id'],
                'like_num2' => Db::raw('like_num2+'.(int)$zan),
            ];

            array_push($video_update, $video_tmp);
            array_push($user_update, $user_tmp);
        }

        if (empty($video_update) || empty($user_update)) return;

        $User = new \app\core\model\User();
        $User->saveAll($user_update);

        $Video = new \app\core\model\Video();
        $Video->saveAll($video_update);

        $lasts = end($rs);

        $redis->set('cache:virtualOperational', $lasts['id']);
    }

}
