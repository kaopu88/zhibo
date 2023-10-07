<?php

namespace app\mq\callbacks;

use bxkj_common\CoreSdk;
use bxkj_common\ClientInfo;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Task;
use bxkj_recommend\model\User;
use bxkj_recommend\PoolManager;
use PhpAmqpLib\Message\AMQPMessage;
use bxkj_recommend\model\Video;
use bxkj_recommend\model\VideoComment;
use think\Db;

class UserBehavior extends ConsumerCallback
{

    //处理消息
    public function process(AMQPMessage $msg)
    {
        $routing_key = $msg->delivery_info['routing_key'];
        $routing_key_arr = explode('.', $routing_key);
        $behavior = $routing_key_arr[2];
        $params = json_decode($msg->body, true);
        if (!empty($params) && !empty($behavior)) {
            $data = (isset($params['behavior']) && is_array($params['data'])) ? $params['data'] : $params;
            $funName = parse_name($behavior, 1, false) . 'Behavior';
            if (!method_exists($this, $funName)) return false;
            return call_user_func_array([$this, $funName], [$data]);
        }
    }

    //红包领取处理
    protected function redDetailBehavior($arr)
    {
        if (empty($arr['user_id']) || empty($arr['red_id']) || empty($arr['money'])) return false;
        $redPacket = Db::name('activity_red_packet')->where(['id' => $arr['red_id']])->find();
        if (empty($redPacket)) return false;
        $tradeNo = get_order_no('red_packet');
        $coreSdk = new CoreSdk();
        $pay = $coreSdk->incBean([
            'user_id' => $arr['user_id'],
            'trade_type' => 'redpacket',
            'trade_no' => $tradeNo,
            'total' => $arr['money'],
            'client_seri' => ClientInfo::encode()
        ]);
        if (empty($pay)) return false;
        $user = userMsg($arr['user_id'], 'nickname,avatar');
        $data = [
            'activity_title' => $redPacket['activity_title'],
            'user_id' => $arr['user_id'],
            'username' => $user['nickname'] ? $user['nickname'] : '',
            'avatar' => img_url($user['avatar'], '', 'avatar'),
            'money' => $arr['money'],
            'red_id' => $arr['red_id'],
            'room_id' => $arr['room_id'],
            'create_time' => time()
        ];
        $insertid = Db::name('activity_red_detail')->insertGetId($data);
        return $insertid;

    }

    //批量观看处理
    protected function batchWatchBehavior($arr)
    {
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
        return true;
    }

    //点赞视频
    protected function likeVideoBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['video_id'])) return false;
        $user = new User('user', $data['user_id']);
        $video = new Video($data['video_id']);
        $user->behavior->like($video);
        $user->training();
        $recUid = $video->getUserId();
        if ($recUid != $user->user_id) {
            $msg = new Message();
            $result = $msg->setReceiver($recUid)->setSender($user->getData())->sendLikeFilm([
                'film_title' => $video->describe ? $video->describe : '',
                'cover_url' => $video->cover_url ? $video->cover_url : $video->ai_url,
                'film_id' => $video->id
            ]);
        }
        $pool = new PoolManager();
        $pool->newpush($data['video_id']);
        return true;
    }

    //取消点赞视频
    protected function cancelLikeVideoBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['video_id'])) return false;
        $video = new Video($data['video_id']);
        $user = new User('user', $data['user_id']);
        $user->behavior->cancelLike($video);
        $user->training();
        $recUid = $video->getUserId();
        if ($recUid != $user->user_id) {
            $msg = new Message();
            $result = $msg->setReceiver($recUid)->setSender($user->getData())->cancelLikeFilm([
                'film_id' => $video->id
            ]);
        }
        $pool = new PoolManager();
        $pool->newpush($data['video_id']);
        return $result;
    }

    //@好友
    protected function atFriendBehavior($data)
    {
        if (empty($data['scene']) || empty($data['user_id']) || empty($data['friend_uids'])) return false;
        $friendUids = is_array($data['friend_uids']) ? $data['friend_uids'] : explode(',', $data['friend_uids']);
        $user = new User('user', $data['user_id']);
        foreach ($friendUids as $friendUid) {
            if ($friendUid != $data['user_id']) {
                $friendUser = new User('user', $friendUid);
                $user->behavior->at($data['scene'], $friendUser);
                $msg = new Message();
                $result = $msg->setReceiver($friendUser->getData())->setSender($user->getData())->sendAtFriend($data);
            }
        }
        $user->training();
        return true;
    }

    //赠送礼物
    protected function giftBehavior($data)
    {
        if (empty($data['id'])) return false;
        $log = Db::name('gift_log')->where(['id' => $data['id']])->find();
        if (empty($log)) return false;
        $user = new User('user', $log['user_id']);
        $user->behavior->gift($log);
        $user->training();
        $recUid = $log['to_uid'];
        if ($recUid != $log['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($recUid)->setSender($user->getData())->sendGift($log);
        }
        return $result;
    }

    //关注
    protected function followBehavior($data)
    {
        if (empty($data['to_uid']) || empty($data['user_id'])) return false;
        $toUser = new User('user', $data['to_uid']);
        $user = new User('user', $data['user_id']);
        $user->behavior->follow($toUser);
        $user->training();
        $msg = new Message();
        $result = $msg->setReceiver($toUser->getData())->setSender($user->getData())->sendFollow([
            'title' => '有新的粉丝关注了您!',
            'summary' => '点击查看',
            'url' => getJump('follow'),
        ]);

        //关注任务
        $taskMod = new Task();
        $data = [
            'user_id' => $data['user_id'],
            'task_type' => 'followFriends',
            'task_value' => 1,
            'status' => 0
        ];
        $taskMod->subTask($data);
        return $result;
    }

    //取消关注
    protected function cancelFollowBehavior($data)
    {
        if (empty($data['to_uid']) || empty($data['user_id'])) return false;
        $toUser = new User('user', $data['to_uid']);
        $user = new User('user', $data['user_id']);
        $user->behavior->cancelFollow($toUser);
        $user->training();
        $msg = new Message();
        $result = $msg->setReceiver($toUser->getData())->setSender($user->getData())->cancelFollow();
        return $result;
    }

    //开播
    protected function liveBehavior($data)
    {
        if (empty($data['room_id']) || empty($data['user_id'])) return false;
        $msg = new Message();
        $result = $msg->setSender($data['user_id'])->sendLive($data);

        //发送成功
        if (!empty($result['task_id'])) {
            //加入房间内数据
            $redis = RedisClient::getInstance();

            $redis->set('BG_LIVE:' . $data['room_id'] . ':message_task', $result['task_id']);
        }

        return $result;
    }

    //取消开播
    protected function cancelLive($data)
    {
        if (empty($data['room_id']) || empty($data['user_id'])) return false;
        $msg = new Message();
        $result = $msg->setSender($data['user_id'])->cancelLive($data);
        return $result;
    }

    //喜欢评论
    protected function likeCommentBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['comment_id'])) return false;
        $likeUid = $data['user_id'];//点赞人
        $comment = new VideoComment($data['comment_id']);
        $user = $comment->getUser();//评论的作者
        $video = $comment->getVideo();//评论的视频
        $likeUser = new User('user', $likeUid);
        $likeUser->behavior->likeComment($comment);
        $likeUser->training();
        $recUid = $user->user_id;
        if ($recUid != $likeUid) {
            $msg = new Message();
            $result = $msg->setReceiver($recUid)->setSender($likeUser->getData())->sendLikeComment([
                'film_id' => $video->id,
                'film_title' => $video->describe ? $video->describe : '',
                'cover_url' => $video->cover_url ? $video->cover_url : '',
                'comment_id' => $data['comment_id']
            ]);
        }
        return $result;
    }

    protected function cancelLikeCommentBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['comment_id'])) return false;
        $likeUid = $data['user_id'];//点赞人
        $comment = new VideoComment($data['comment_id']);
        $user = $comment->getUser();//评论的作者
        $likeUser = new User('user', $likeUid);
        $likeUser->behavior->cancelLikeComment($comment);
        $likeUser->training();
        $recUid = $user->user_id;
        if ($recUid != $likeUid) {
            $msg = new Message();
            $result = $msg->setReceiver($recUid)->setSender($likeUser->getData())->cancelLikeComment([
                'comment_id' => $data['comment_id']
            ]);
        }
        return $result;
    }

    //评论(评分不可撤销)
    protected function commentBehavior($data)
    {
        if (empty($data['id'])) return false;
        $comment = new VideoComment($data['id']);
        $user = $comment->getUser();//评论的作者
        $video = $comment->getVideo();
        $user->behavior->comment($comment);
        $user->training();
        $recUid = $video->getUserId();//视频作者UID
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
        $pool = new PoolManager();
        $pool->newpush($video->id);
        return true;
    }

    //回复(评分不可撤销)
    protected function replyBehavior($data)
    {
        if (empty($data['id'])) return false;
        $comment = new VideoComment($data['id']);
        $user = $comment->getUser();
        $video = $comment->getVideo();
        $user->behavior->reply($comment);
        $user->training();
        $recUid = $video->getUserId();
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
        $pool = new PoolManager();
        $pool->newpush($video->id);
        return true;
    }

    protected function commentDeleteBehavior($data)
    {
        if (empty($data['video_id']) || empty($data['del_num'])) return false;
        $i = mt_rand(0, 100);
        if ($i <= 40) {
            $video = new Video($data['video_id'], false);
            $video->commentDelete(['num' => $data['del_num']], false);
            if (!empty($data['reply_id'])) {
                $reply_count = Db::name('video_comment')->where(['reply_id' => $data['reply_id']])->count();
                Db::name('video_comment')->where(['id' => $data['reply_id']])->update(['reply_count' => $reply_count]);
            }
            if (!empty($data['parent_id']) && $data['reply_id'] != $data['parent_id']) {
                $reply_count = Db::name('video_comment')->where(['master_id' => $data['parent_id']])->count();
                Db::name('video_comment')->where(['id' => $data['parent_id']])->update(['reply_count' => $reply_count]);
            }
        } else {
            $video = new Video($data['video_id'], false);
            $video->commentDelete(['num' => $data['del_num']], true);
            if (!empty($data['reply_id'])) {
                Db::name('video_comment')->where(['id' => $data['reply_id']])->setDec('reply_count', abs($data['del_num']));
            }
            if (!empty($data['parent_id']) && $data['reply_id'] != $data['parent_id']) {
                Db::name('video_comment')->where(['id' => $data['parent_id']])->setDec('reply_count', abs($data['del_num']));
            }
        }
    }

    protected function blackBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['to_uid'])) return false;
        $user = new User('user', $data['user_id']);
        $beUser = new User('user', $data['to_uid']);
        $user->behavior->black($beUser);
        $user->training();
        return true;
    }

    protected function cancelBlackBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['to_uid'])) return false;
        $user = new User('user', $data['user_id']);
        $beUser = new User('user', $data['to_uid']);
        $user->behavior->cancelBlack($beUser);
        $user->training();
        return true;
    }

    protected function shareVideoBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['video_id'])) return false;
        $user = new User('user', $data['user_id']);
        $video = new Video($data['video_id']);
        $user->behavior->shareVideo($video);
        $user->training();

        //视频分享任务
        $taskMod = new Task();
        $data = [
            'user_id' => $data['user_id'],
            'task_type' => 'shareVideo',
            'task_value' => 1,
            'status' => 0
        ];
        $taskMod->subTask($data);
    }

    protected function shareUserBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['to_uid'])) return false;
        $user = new User('user', $data['user_id']);
        $idol = new User('user', $data['to_uid']);
        $user->behavior->shareUser($idol);
        $user->training();
    }

    protected function viewUserBehavior($data)
    {
        if (empty($data['user_id']) || empty($data['to_uid'])) return false;
        $user = new User('user', $data['user_id']);
        $idol = new User('user', $data['to_uid']);
        $user->behavior->viewUser($idol);
        $user->training();
    }

    protected function noticeBehavior($data)
    {
        $fansList = Db::name("follow")->field("user_id")->where(["follow_id" => $data['anchor_id']])->select();
        if ($fansList) {
            $anchor = Db::name("user")->where(["user_id" => $data['anchor_id'], 'delete_time' => null])->find();
            $message = new Message();
            foreach ($fansList as $value) {
                $message->setSender('', 'helper')->setReceiver($value['user_id'])->sendNotice([
                    'type' => 'follow_live',
                    'title' => '您关注的'. $anchor['nickname'] .'主播开播了!',
                    'summary' => '点击查看',
                    'url' => getJump('enter_room', ['room_id' => $data['room_id']]),
                ]);
            }
        }
    }
}
