<?php

namespace bxkj_module\service;

use bxkj_common\Console;
use bxkj_module\exception\ApiException;
use think\Db;

class UserBehavior extends Service
{
    protected $userId;
    protected $user;

    public function __construct($userId)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->user = Db::name('user')->where(array('user_id' => $this->userId, 'delete_time' => null))->find();
        if (empty($this->user)) throw new ApiException('用户不存在');
    }

    //喜欢视频
    public function likeFilm($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->sendLikeFilm($inputData);
            return $result;
        }
        return true;
    }

    //取消喜欢视频
    public function cancelLikeFilm($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->cancelLikeFilm($inputData);
            return $result;
        }
        return true;
    }

    //喜欢评论
    public function likeComment($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->sendLikeComment($inputData);
            return $result;
        }
        return true;
    }

    //取消喜欢评论
    public function cancelLikeComment($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->cancelLikeComment($inputData);
            return $result;
        }
        return true;
    }

    //评论作品
    public function comment($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->sendComment($inputData);
            return $result;
        }
        return true;
    }

    public function cancelComment($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->cancelComment($inputData);
            return $result;
        }
        return true;
    }

    //回复作品
    public function reply($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->sendReply($inputData);
            return $result;
        }
        return true;
    }

    public function cancelReply($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->cancelReply($inputData);
            return $result;
        }
        return true;
    }

    //关注用户
    public function follow($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->sendFollow();
            return $result;
        }
        return true;
    }

    //取消关注用户
    public function cancelFollow($inputData)
    {
        if ($inputData['user_id'] != $this->user['user_id']) {
            $msg = new Message();
            $result = $msg->setReceiver($inputData['user_id'])->setSender($this->user)->cancelFollow();
            return $result;
        }
        return true;
    }

    //@Ta
    public function atFriend($inputData)
    {
        $friendUids = is_array($inputData['friend_uids']) ? $inputData['friend_uids'] : explode(',', $inputData['friend_uids']);
        foreach ($friendUids as $friendUid) {
            if ($friendUid != $this->user['user_id']) {
                $msg = new Message();
                $result = $msg->setReceiver($friendUid)->setSender($this->user)->sendAtFriend($inputData);
            }
        }
        return true;
    }

    //送礼物行为
    public function gift()
    {
    }

    //开播行为
    public function live($inputData)
    {
        $msg = new Message();
        $result = $msg->setSender($this->user)->sendLive($inputData);
        return $result;
    }

    //取消开播行为
    public function cancelLive($inputData)
    {
        $msg = new Message();
        $result = $msg->setSender($this->user)->cancelLive($inputData);
        return $result;
    }
}