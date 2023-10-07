<?php

namespace app\mq\controller;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\GiftLog;
use bxkj_module\service\Message;
use bxkj_recommend\behavior\GiftBehavior;
use bxkj_recommend\Calc;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\IndexRecycling;
use bxkj_recommend\model\User;
use bxkj_recommend\model\Video;
use bxkj_recommend\model\VideoComment;
use bxkj_recommend\PoolManager;
use bxkj_recommend\PoolRecycling;
use bxkj_recommend\ProConf;
use bxkj_recommend\ProRedis;
use bxkj_recommend\UserIndex;
use bxkj_recommend\VideoUpdater;
use think\Controller;
use think\Db;

class Prophet extends Controller
{
    //验证
    public function add()
    {
        $pool = new PoolManager();
        $pool->push(new Video(104));
        echo 'add success';
        exit();
    }

    //更新器
    public function updater()
    {
        $videoUpdater = new VideoUpdater();
        $videoUpdater->start(['type' => 'week', 'index' => 0]);
        echo 'updater success';
        exit();
    }

    public function update()
    {
        $pool = new PoolManager();
        $res = $pool->update(new Video(92));
        if (!$res) {
            echo $pool->getError()->message;
            exit();
        }
        echo 'update success';
        exit();
    }

    public function remove()
    {
        $pool = new PoolManager();
        $res = $pool->remove(92);
        if (!$res) {
            echo $pool->getError()->message;
            exit();
        }
        echo 'remove success';
        exit();
    }

    public function batch_watch()
    {
        $arr = [
            ['video_id' => 2517, 'alias_type' => 'user', 'alias_id' => 10000444, 'start_time' => 150000, 'max_duration' => 1 * 1000, 'duration' => 1 * 1000],
        ];

        $user = [
            'alias_type' => 'user',
            'alias_id' => 10000444
        ];
        foreach ($arr as $item) {
            $tmp = [
                'video_id' => $item['video_id'],
                'start_time' => $item['start_time'],
                'max_duration' => isset($item['max_duration']) ? $item['max_duration'] : $item['duration'],
                'duration' => isset($item['sum_duration']) ? $item['sum_duration'] : $item['duration']
            ];
            $tmp = array_merge($tmp, $user);
            if (!empty($tmp['video_id']) && !empty($tmp['start_time']) && !empty($tmp['max_duration']) && !empty($tmp['duration'])) {
                $tmp['create_time'] = time();
                Db::name('watch_history')->insert($tmp);
            }
        }

        $groupList = [];
        foreach ($arr as $item) {
            if (!empty($item['alias_type']) && !empty($item['alias_id']) && !empty($item['video_id']) && !empty($item['duration'])) {
                $userMark = $item['alias_type'] . ':' . $item['alias_id'];
                if (!isset($groupList[$userMark])) $groupList[$userMark] = [];
                $groupList[$userMark][] = $item;
            }
        }


        foreach ($groupList as $userMark => $group) {
            list($alias_type, $alias_id) = explode(':', $userMark);
            $user = new User($alias_type, $alias_id, false);
            $user->behavior->batchWatch($group);
            $user->training();
        }
        echo 'batch_watch success';
        exit();
    }

    public function like()
    {
        $user = new \bxkj_recommend\model\User('user', 10173, false);
        $user->behavior->like(new Video(66, false));
        $user->training();
    }

    public function index()
    {
        $prophet = new \bxkj_common\Prophet('user', 10202);
        $videos = $prophet->getList(0, 10);
        var_dump($videos);
        exit();
    }

    public function building()
    {
        $userIndex = new UserIndex('user', 10202);
        $userIndex->building();
    }


    public function reply()
    {
        $data['id'] = 276;
        if (empty($data['id'])) return false;
        $comment = new VideoComment($data['id']);
        $user = $comment->getUser();
        $video = $comment->getVideo();
        $user->behavior->reply($comment);
        $user->training();
        $recUid = $video->getUserId();
        var_dump($recUid);
        exit();
        if ($recUid != $user->user_id) {
            $msg = new Message();
            $msg->setReceiver($recUid)->setSender($user->getData())->sendReply([
                'summary' => $comment->content ? $comment->content : '',
                'film_id' => $video->id,
                'film_title' => $video->describe ? $video->describe : '',
                'cover_url' => $video->cover_url ? $video->cover_url : '',
                'comment_id' => $comment->id,
                'to_comment_id' => $comment->reply_id
            ]);
        }
        return true;
    }

    public function comment()
    {
        $data['id'] = 293;
        if (empty($data['id'])) return false;
        $comment = new VideoComment($data['id']);
        $user = $comment->getUser();
        $video = $comment->getVideo();
        $user->behavior->comment($comment);
        $user->training();
        $recUid = $video->getUserId();
        if ($recUid != $user->user_id) {
            $msg = new Message();
            $msg->setReceiver($recUid)->setSender($user->getData())->sendComment([
                'summary' => $comment->content ? $comment->content : '',
                'film_id' => $video->id,
                'film_title' => $video->describe ? $video->describe : '',
                'cover_url' => $video->cover_url ? $video->cover_url : '',
                'comment_id' => $data['id']
            ]);
        }
        return true;
    }

    public function score()
    {
        $video = [
            'id' => 1000,
            'user_id' => 10105,
            'zan_sum' => 200,
            'share_sum' => 20,
            'watch_sum' => 5000,
            'down_sum' => 5,
            'watch_duration' => 500,
            'played_out_sum' => 300,
            'switch_sum' => 30,
            'rating' => 70,
            'sco_comment_sum' => 12,
            'duration' => 15,
            'create_time' => mktime(10, 0, 0, 5, 25)
        ];
        $videoM = new Video($video);
        $score = $videoM->evaluate()->score;
        var_dump($score);
    }

    public function lpushx()
    {
        $redis = ProRedis::getInstance();
        $res = $redis->lPushx('test:ccc', 1);
        var_dump($res);
    }

    public function recycling_index()
    {
        $indexRecycling = new IndexRecycling();
        $total = $indexRecycling->recycling();
        var_dump($total);
    }

    public function recycling_pool()
    {
        $poolRecycling = new PoolRecycling();
        $total = $poolRecycling->recycling();
        var_dump($total);
    }

    public function test()
    {


        $gift = new GiftLog();
        $result = $gift->give([
            'user_id' => '10000165',
            'to_uid' => 10000024,
            'gift_id' => 204,
            'pay_scene' => 'video',
            'num' => 1,
            'video_id' => 2436
        ]);
        return json_error($gift->getError());
    }

}
