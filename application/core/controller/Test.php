<?php

namespace app\core\controller;

use app\core\service\GiftLog;
use bxkj_common\Prophet;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use bxkj_recommend\behavior\LikeBehavior;
use bxkj_recommend\behavior\WatchBehavior;
use bxkj_recommend\Calc;
use bxkj_recommend\model\VideoComment;
use bxkj_recommend\PoolManager;
use bxkj_recommend\ProRedis;
use bxkj_recommend\UserIndex;
use bxkj_recommend\model\Video;
use think\Db;
use think\Session;

class Test extends Controller
{

    //验证
    public function add()
    {
        $pool = new PoolManager();
        $pool->push(new Video(66));
        echo 'add success';
        exit();
    }

    public function update()
    {
        $pool = new PoolManager();
        $res = $pool->update(66);
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
        $res = $pool->remove(66);
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
            ['video_id' => 66, 'alias_type' => 'user', 'alias_id' => 10173, 'start_time' => 1587878787, 'duration' => 6000],
        ];
        $groupList = [];
        foreach ($arr as $item) {
            $userMark = $item['alias_type'] . '_' . $item['alias_id'];
            if (!isset($groupList[$userMark])) $groupList[$userMark] = [];
            $groupList[$userMark][] = $item;
        }
        foreach ($groupList as $userMark => $group) {
            list($alias_type, $alias_id) = explode('_', $userMark);
            $user = new \bxkj_recommend\model\User($alias_type, $alias_id, false);
            $user->behavior->batchWatch($group);
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

    public function gift()
    {
        $new = new GiftLog();

        $rs = $new->give(
            [
                'user_id' => 10152,
                'to_uid' => 10151,
                'gift_id' => 202,

                'consume_order' => 'video_user_reward,bean',
                'pay_scene' => 'video',
                'leave_msg' => '礼物留言',
                'num' => 1,
                'video_id' => 13
            ]
        );

        var_dump($rs);
        die;

    }

    public function index()
    {
        $prophet = new Prophet('user', 10202);
        $videos = $prophet->fields('')->getList(0, 10);
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

    public function create_user()
    {
        $user = new \app\core\service\User();
        $regUser = $user->createByPhone([
            'phone' => '18855100878',
            'password' => 'a123456',
            'promoter_uid' => '10000165'
        ]);
        var_dump($regUser);
        exit();
    }


}
