<?php

namespace bxkj_module\service;

use bxkj_push\AomyPush;
use bxkj_module\exception\ApiException;
use bxkj_common\RedisClient;
use bxkj_module\push\AppPush;
use think\Db;

class Message extends Service
{
    protected $sender;
    protected $receiver;
    protected $receiverGroup;

    //发送用户
    public function setSender($sender, $type = 'user')
    {
        if ($type == 'helper') {
            $sender = config('app.app_setting.helper_id');
            $type = 'user';
        }
        if (empty($sender)) throw new ApiException('发送用户不存在');
        if (!is_array($sender)) {
            if ($type == 'user') {
                $sender = Db::name('user')->where(array('user_id' => $sender, 'delete_time' => null))->find();
                if (empty($sender)) throw new ApiException('发送用户不存在');
            } else {
            }
        }
        $this->sender = $sender;
        return $this;
    }

    //接收用户
    public function setReceiver($receiver, $groupId = '')
    {
        if (empty($receiver) && empty($groupId)) throw new ApiException('接收用户不存在');
        if (!empty($groupId)) {
            if (!in_array($groupId, ['all'])) throw new ApiException('用户组不存在');
            $this->receiverGroup = $groupId;
        } else {
            if (!is_array($receiver)) {
                $receiver = Db::name('user')->where(array('user_id' => $receiver, 'delete_time' => null))->find();
            }
            if (empty($receiver)) throw new ApiException('接收用户不存在');
            $this->receiver = $receiver;
        }
        return $this;
    }

    //赞视频
    public function sendLikeFilm($filmData)
    {
        $data['cat_type'] = 'like';
        $data['type'] = 'like_film';
        $data['title'] = $this->sender['nickname'] . '赞了你的作品';
        $data['summary'] = '';
        $data['content'] = array(
            'film_title' => $filmData['film_title'],
            'cover_url' => $filmData['cover_url']
        );
        $data['content_id'] = $filmData['film_id'];
        $msg = $this->insertMsg($data);
        return $this->commonWrite('like_push', $msg);
    }

    //取消赞视频
    public function cancelLikeFilm($filmData)
    {
        return $this->commonCancel([
            'type' => 'like_film',
            'user_id' => $this->receiver['user_id'],
            'send_uid' => $this->sender['user_id'],
            'content_id' => $filmData['film_id']
        ]);
    }

    //赞评论
    public function sendLikeComment($filmData)
    {
        $data['cat_type'] = 'like';
        $data['type'] = 'like_comment';
        $data['title'] = $this->sender['nickname'] . '赞了你的评论';
        $data['summary'] = '';
        $data['content'] = array(
            'film_id' => $filmData['film_id'],
            'film_title' => $filmData['film_title'],
            'cover_url' => $filmData['cover_url'],
        );
        $data['content_id'] = $filmData['comment_id'];
        $msg = $this->insertMsg($data);
        return $this->commonWrite('like_push', $msg);
    }


    public function cancelLikeComment($filmData)
    {
        return $this->commonCancel([
            'type' => 'like_comment',
            'user_id' => $this->receiver['user_id'],
            'send_uid' => $this->sender['user_id'],
            'content_id' => $filmData['comment_id']
        ]);
    }

    //评论作品
    public function sendComment($inputData)
    {
        $data['cat_type'] = 'comment';
        $data['type'] = 'comment';
        $data['title'] = $this->sender['nickname'] . '评论了你的作品';
        $data['summary'] = $inputData['summary'] ? $inputData['summary'] : '';
        $data['content'] = array(
            'film_id' => $inputData['film_id'],
            'film_title' => $inputData['film_title'],
            'cover_url' => $inputData['cover_url'],
        );
        $data['content_id'] = $inputData['comment_id'];
        $msg = $this->insertMsg($data);
        return $this->commonWrite('comment_push', $msg);
    }

    //取消评论
    public function cancelComment($inputData)
    {
        return $this->commonCancel([
            'type' => 'comment',
            'user_id' => $this->receiver['user_id'],
            'send_uid' => $this->sender['user_id'],
            'content_id' => $inputData['comment_id']
        ]);
    }

    //回复了评论
    public function sendReply($inputData)
    {
        $data['cat_type'] = 'comment';
        $data['type'] = 'reply';
        $data['title'] = $this->sender['nickname'] . '回复了你的评论';
        $data['summary'] = $inputData['summary'] ? $inputData['summary'] : '';
        $data['content'] = array(
            'film_id' => $inputData['film_id'],
            'film_title' => $inputData['film_title'],
            'cover_url' => $inputData['cover_url'],
            'to_comment_id' => $inputData['to_comment_id']
        );
        $data['content_id'] = $inputData['comment_id'];
        $msg = $this->insertMsg($data);
        return $this->commonWrite('comment_push', $msg);
    }

    public function sendAtFriend($inputData)
    {
        $data['cat_type'] = 'at';
        $data['type'] = 'at_' . $inputData['scene'];//publish_film
        if ($inputData['scene'] == 'publish_film') {
            $data['title'] = $this->sender['nickname'] . '在发布新作品时@了你';
            $data['summary'] = $inputData['film_title'] ? $inputData['film_title'] : '';
            $data['content'] = array(
                'film_id' => $inputData['film_id'],
                'film_title' => $inputData['film_title'],
                'cover_url' => $inputData['cover_url'],

            );
            $data['content_id'] = $inputData['film_id'];
        } else if ($inputData['scene'] == 'gift_reply') {
            $data['title'] = $this->sender['nickname'] . '回复中@了你';
            $data['summary'] = $inputData['reply_msg'];
            $data['content'] = array(
                'reply_msg' => $inputData['reply_msg'],
                'log_id' => $inputData['log_id']
            );
            $data['content_id'] = $inputData['log_id'];
        } else if ($inputData['scene'] == 'comment') {
            $data['title'] = $this->sender['nickname'] . '评论中@了你';
            $comment_id = $inputData['comment_id'];
            $comment = Db::name('video_comment')->where(['id' => $comment_id])->find();
            $comment = $comment ? $comment : [];
            $data['summary'] = $comment['content'] ? $comment['content'] : '';
            $data['content'] = array(
                'comment_id' => $comment_id,
                'content' => $comment['content'] ? $comment['content'] : ''
            );
            $data['content_id'] = $comment_id;
        } else if ($inputData['scene'] == 'friend_push_dynamic') {
            switch ($inputData['friend_type']) {
                case 6:
                    $data['title'] = $this->sender['nickname'] . '对你发布了表白';
                    break;
                case 3 :
                    $data['title'] = $this->sender['nickname'] . '在发布圈子动态时@了你';
                    break;
                default:
                    $data['title'] = $this->sender['nickname'] . '在发布新动态时@了你';
            }
            $data['summary'] = $inputData['dynamic_title'] ? $inputData['dynamic_title'] : '';
            $data['content'] = array(
                'dynamic_id' => $inputData['dynamic_id'],
                'dynamic_title' => $inputData['dynamic_title'],
                'cover_url' => $inputData['cover_url'],
                'url' => $inputData['url'],
            );
            $data['content_id'] = $inputData['dynamic_id'];
        } else {
            return $this->setError('scene不正确');
        }
        return $msg = $this->insertMsg($data);
        return $this->commonWrite('at_push', $msg);
    }

    public function cancelReply($inputData)
    {
        return $this->commonCancel([
            'type' => 'reply',
            'user_id' => $this->receiver['user_id'],
            'send_uid' => $this->sender['user_id'],
            'content_id' => $inputData['comment_id']
        ]);
    }

    //关注了用户
    public function sendFollow($inputData)
    {
        $data['cat_type'] = 'follow';
        $data['type'] = 'follow';
        $data['title'] = $this->sender['nickname'] . '关注了你';
        $data['summary'] = '';
        $data['content'] = array();
        $data['content_id'] = $this->sender['user_id'];
        $msg = $this->insertMsg($data);
        if (!empty($inputData)) {
            $msgData = array(
                'title' => $inputData['title'],
                'text' => $inputData['summary'],
                'after_open' => 'go_app',
                'custom' => array(
                    'header' => 'url',
                    'url' => $inputData['url']
                )
            );
            if ($this->isOnPush("follow_push")) {
                $AomyPush = new AomyPush();
                $AomyPush->setUser(array('user_id' => $this->receiver['user_id']))->allTo($msgData);
            }
        }
        return $this->commonWrite('follow_push', $msg);
    }

    public function cancelFollow()
    {
        return $this->commonCancel([
            'type' => 'follow',
            'user_id' => $this->receiver['user_id'],
            'send_uid' => $this->sender['user_id'],
            'content_id' => $this->sender['user_id']
        ]);
    }

    public function sendLive($inputData)
    {
        $msgData = [];
        $msgData['title'] = $this->sender['nickname'] . '正在直播';
        $msgData['text'] = '快去围观~';
        $msgData['avatar'] = $this->sender['avatar'];
        $msgData['room_id'] = $inputData['room_id'];
        $msgData['user_id'] = $this->sender['user_id'];
        $push = new AppPush();
        $taskId = $push->writeSection('live', $msgData);
        if ($taskId) {
            $msgData['task_id'] = $taskId;
        }
        return $msgData;
    }

    public function sendGift($inputData)
    {
        $gift_id = $inputData['gift_id'];
        $gift = Db::name('gift')->field('id,cid,name,picture_url')->where(['id' => $gift_id])->find();
        if (empty($gift)) {
            $gift = [
                'name' => '礼物'
            ];
        }
        if (!empty($inputData['video_id'])) {
            $data['cat_type'] = 'reward';
            $data['type'] = 'reward_gift';
            $data['title'] = '收到礼物' . $gift['name'];
            $data['summary'] = $inputData['leave_msg'] ? "留言：{$inputData['leave_msg']}" : ($this->sender['nickname'] . '给您赠送了礼物');
            $data['content'] = array(
                'log_id' => $inputData['id'],
                'gift_id' => $inputData['gift_id'],
                'video_id' => $inputData['video_id']
            );
            $data['content_id'] = $inputData['id'];
        } else {
            return false;
        }
        $msg = $this->insertMsg($data);
        return $this->commonWrite('gift', $msg);
    }

    //系统通知
    public function sendNotice($inputData, $isPush = true)
    {
        $data = [];
        $data['cat_type'] = 'system';
        $data['type'] = 'notice';
        $data['title'] = $inputData['title'] ? $inputData['title'] : '系统通知';
        $data['content'] = [
            'text' => $inputData['text'] ? $inputData['text'] : '',
            'url' => $inputData['url'] ? $inputData['url'] : ''
        ];
        $data['summary'] = $inputData['summary'] ? $inputData['summary'] : '请查看详情';
        $msg = $this->insertMsg($data);
        if ($msg) {
            if ($isPush) $this->commonWrite('*', $msg, 'system', false);
            $userId = $this->receiver['user_id'];
            $key = "messages:notice_sets:{$userId}";
            $redis = RedisClient::getInstance();
            $redis->zAdd($key, time(), "notice:{$msg['id']}");
            $AomyPush = new AomyPush();
            $msgData = array(
                'title' => $inputData['title'],
                'text' => $inputData['summary'],
                'after_open' => 'go_app',
                'custom' => array(
                    'header' => 'url',
                    'url' => $inputData['url']
                )
            );

            $typeName = "*";
            if ($inputData['type'] == 'follow_new') {
                $typeName = "follow_new_push";
            }
            if ($inputData['type'] == 'follow_live') {
                $typeName = "follow_live_push";
            }
            if ($this->isOnPush($typeName)) {
                $AomyPush->setUser(array('user_id' => $this->receiver['user_id']))->allTo($msgData);
            }
        }
        return true;
    }

    public function sendPush($inputData, $isPush = true)
    {
        $msg = [
            'group_id' => $this->receiverGroup,
            'title' => $inputData['title'],
            'url' => $inputData['url'],
            'content' => $inputData['text'],
            'summary' => $inputData['summary'],
            'read_total' => 0,
            'create_time' => time()
        ];
        $redis = RedisClient::getInstance();
        if ($inputData['directly'] != '1') {
            $id = Db::name('system_message')->insertGetId($msg);
            if (!$id) return false;
            $msg['id'] = $id;
            $key = "messages:push_sets:{$this->receiverGroup}";
            $redis->zAdd($key, time(), "push:{$msg['id']}");
        } else {
            $msg['id'] = '';
        }
        $msg['cat_type'] = 'system';
        $msg['type'] = 'push';
        $msg['send_uid'] = $this->sender['user_id'];
        $msg['send_avatar'] = $this->sender['avatar'];
        $msg['send_nickname'] = $this->sender['nickname'];
        $push = new AppPush();
        $taskId = $push->writeSection('system', $msg);
        if ($taskId && $msg['id']) {
            Db::name('system_message')->where(['id' => $msg['id']])->update(['task_id' => $taskId]);
        }
        if ($inputData['directly'] == '1') {
            $AomyPush = new AomyPush();
            if (empty($inputData['url'])) {
                $msgData = array(
                    'title' => $inputData['title'],
                    'text' => $inputData['text'],
                    'after_open' => 'go_app',
                );
            } else {
                $msgData = array(
                    'title' => $inputData['title'],
                    'text' => $inputData['summary'],
                    'after_open' => 'go_app',
                    'custom' => array(
                        'header' => 'url',
                        'url' => $inputData['url']
                    )
                );
            }
            $AomyPush->setUser("all")->allBroadcast($msgData);
        }
        return $taskId;
    }

    public function cancelLive($inputData)
    {
        return 1;
    }

    protected function isOnPush($name)
    {
        if ($name == '*') return true;
        $userSetting = new UserSetting();
        $switch = $userSetting->setting($this->receiver['user_id'], $name);
        return $switch == '1';
    }

    protected function insertMsg($data)
    {
        $data['user_id'] = $this->receiver['user_id'];
        $data['read_status'] = '0';
        $data['send_uid'] = $this->sender['user_id'];
        $data['send_nickname'] = $this->sender['nickname'];
        $data['send_avatar'] = $this->sender['avatar'];
        $data['content'] = json_encode($data['content'] ? $data['content'] : '');
        $data['create_time'] = time();
        $msgId = Db::name('message')->insertGetId($data);
        if (!$msgId) throw new ApiException('消息创建失败');
        $data['id'] = $msgId;
        //   向APP推送新消息提醒
        $typeName = "*";
        if ($data['type'] == 'like_film') {
            $typeName = "like_push";
        }
        if ($data['type'] == 'comment') {
            $typeName = "comment_push";
        }
        if ($data['cat_type'] == 'at') {
            $typeName = "at_push";
        }
        $msgData = array(
            'title' => $data['title'],
            'text' => $data['summary'],
            'after_open' => 'go_app',
            'custom' => array(
                'header' => 'url',
                'url' => $data['url']
            )
        );
        if ($this->isOnPush($typeName)) {
            $AomyPush = new AomyPush();
            $res = $AomyPush->setUser(array('user_id' => $this->receiver['user_id']))->allTo($msgData);
        }
        return $data;
    }

    protected function insertFriendMsg($data)
    {
        //  $data['user_id']       = $data['user_id'];
        $data['user_id'] = $this->receiver['user_id'];
        $data['read_status'] = '0';
        $data['send_uid'] = USERID;
        $data['send_nickname'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['nickname'];
        $data['send_avatar'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['avatar'];
        $data['content'] = json_encode($data['content'] ? $data['content'] : '');
        $data['create_time'] = time();
        $msgId = Db::name('message')->insertGetId($data);
        if (!$msgId) throw new ApiException('消息创建失败');
        $data['id'] = $msgId;
        //   向APP推送新消息提醒
        $AomyPush = new AomyPush();
        $res = $AomyPush->setUser(array('user_id' => $this->receiver['user_id']))->allTo(array(
            'display_type' => 'message',
            'after_open' => 'go_app',
            'custom' => array(
                'header' => 'unread',
                'unread_total' => 1
            )
        ));
        return $data;
    }


    protected function commonWrite($typeName, $msg, $producerName = 'user_behavior', $isMerge = true)
    {
        $typeName2 = ($typeName == 'gift') ? 'at_push' : $typeName;
        //消息推送
        if ($this->isOnPush($typeName2)) {
            $push = new AppPush();
            $taskId = $push->write($producerName, $msg, $isMerge);
            if ($taskId && $msg['id']) {
                $num = Db::name('message')->where('id', $msg['id'])->update(['task_id' => $taskId]);
                if ($num) $msg['task_id'] = $taskId;
            }
        }
        return $msg;
    }

    protected function commonCancel($where)
    {
        $ids = [];
        $messages = Db::name('message')->where($where)->select();
        if ($messages) {
            $push = new AppPush();
            foreach ($messages as $message) {
                $ids[] = $message['id'];
                if (!empty($message['task_id'])) {
                    $push->cancel($message['task_id'], $message['id']);
                }
            }
        }
        $num = 0;
        if ($ids) {
            $num = Db::name('message')->whereIn('id', $ids)->delete();
        }
        return $num;
    }

    //赞交友消息
    public function sendLikefriendMsg($msgData)
    {

        $data['cat_type'] = 'like';
        $data['type'] = 'like_friend_msg';
        $data['title'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['nickname'] . '赞了你的动态';
        $data['summary'] = '';
        $data['user_id'] = $msgData['user_id'];
        $data['content'] = array(
            'msg_id' => $msgData['msg_id'],
            'msg_title' => $msgData['msg_title'],
            'cover_url' => $msgData['cover_url'],
            'url' => LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $msgData['msg_id'],
        );
        $data['content_id'] = $msgData['comment_id'];
        $msg = $this->insertFriendMsg($data);
        return $this->commonWrite('like_push', $msg);
    }


    //赞交友评论

    public function sendLikefriendEvaluate($commentData)
    {
        $data['cat_type'] = 'like';
        $data['type'] = 'like_friend_evaluate';
        $data['title'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['nickname'] . '赞了你的留言';
        $data['summary'] = '';
        $data['user_id'] = $commentData['user_id'];
        $data['content'] = array(
            'msg_id' => $commentData['msg_id'],
            'comment_id' => $commentData['comment_id'],
            'evaluate_id' => $commentData['evaluate_id'],
            'comment_id' => $commentData['comment_id'],
            'fcomment_title' => $commentData['fcomment_title'],
            'cover_url' => $commentData['cover_url'],
            'url' => LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $commentData['msg_id'],
        );
        $data['content_id'] = $commentData['evaluate_id'];
        $msg = $this->insertFriendMsg($data);
        return $this->commonWrite('like_push', $msg);
    }

    //赞交友评论
    public function sendLikefriendComment($commentData)
    {
        $data['cat_type'] = 'like';
        $data['type'] = 'like_friend_comment';
        $data['title'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['nickname'] . '赞了你的评论';
        $data['summary'] = '';
        $data['user_id'] = $commentData['user_id'];
        $data['content'] = array(
            'msg_id' => $commentData['msg_id'],
            'comment_id' => $commentData['comment_id'],
            'fcomment_title' => $commentData['fcomment_title'],
            'fcomment_url' => $commentData['cover_url'],
            'url' => LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $commentData['msg_id'],

        );
        $data['content_id'] = $commentData['comment_id'];
        $msg = $this->insertFriendMsg($data);
        return $this->commonWrite('like_push', $msg);
    }


    //评论作品
    public function sendFriendComment($inputData)
    {
        $data['cat_type'] = 'comment';
        $data['type'] = 'comment_friend_msg';
        $data['title'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['nickname'] . '评论了你的作品';
        $data['summary'] = $inputData['summary'] ? $inputData['summary'] : '';
        $data['user_id'] = $inputData['user_id'];
        $data['content'] = array(
            'msg_id' => $inputData['msg_id'],
            'msg_title' => $inputData['fcomment_title'],
            'comment_id' => $inputData['comment_id'],
            'cover_url' => $inputData['cover_url'],
            'url' => LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $inputData['msg_id'],
        );
        $data['content_id'] = $inputData['comment_id'];
        $msg = $this->insertFriendMsg($data);
        return $this->commonWrite('comment_push', $msg);
    }

    //对评论进行评论
    public function sendFriendCommentEvaluate($inputData)
    {
        $data['cat_type'] = 'comment';
        $data['type'] = 'comment_friend_msg_comment';
        $data['title'] = userMsg(USERID, 'user_id,avatar,nickname,gender')['nickname'] . '评论了你的评论';
        $data['summary'] = $inputData['summary'] ? $inputData['summary'] : '';
        $data['user_id'] = $inputData['user_id'];
        $data['content'] = array(
            'msg_id' => $inputData['msg_id'],
            'comment_id' => $inputData['comment_id'],
            'comment_evaluate_id' => $inputData['comment_evaluate_id'],
            'fcomment_title' => $inputData['fcomment_title'],
            'comment_evaluate_title' => $inputData['comment_evaluate_title'],
            'touid' => $inputData['touid'] ? $inputData['touid'] : 0,
            'cover_url' => $inputData['cover_url'],
            'url' => LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $inputData['msg_id'],
        );
        $data['content_id'] = $inputData['comment_evaluate_id'];
        $msg = $this->insertFriendMsg($data);
        return $this->commonWrite('comment_push', $msg);
    }

}