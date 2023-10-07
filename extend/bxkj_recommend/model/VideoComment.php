<?php

namespace bxkj_recommend\model;

use bxkj_recommend\exception\Exception;
use bxkj_recommend\ProRedis;
use think\Db;

class VideoComment extends Model
{
    protected $id;
    protected $user;
    protected $video;
    protected $replyComment;

    public function __construct($id, $autoQuery = true)
    {
        parent::__construct();
        $this->id = $id;
        if ($autoQuery) {
            $queryRes = $this->query();
            if (!$queryRes) throw new Exception('video comment not exist', 7000);
        } else {
            $this->data = ['id' => $this->id];
        }
    }

    public function query()
    {
        $this->data = Db::name('video_comment')->where(['id' => $this->id])->find();
        return $this->data;
    }

    public function getUser()
    {
        $userId = $this->data['user_id'];
        if (empty($userId)) {
            $queryRes = $this->query();
            if (!$queryRes) return false;
            $userId = $this->data['user_id'];
        }
        if (!isset($this->user) || $this->user->user_id != $userId) {
            $this->user = new User('user', $userId);
        }
        return $this->user;
    }

    public function getVideo()
    {
        $videoId = $this->data['video_id'];
        if (empty($videoId)) {
            $queryRes = $this->query();
            if (!$queryRes) return false;
            $videoId = $this->data['video_id'];
        }
        if (!isset($this->video) || $this->video->id != $videoId) {
            $this->video = new Video($videoId);
        }
        return $this->video;
    }

    public function getReplyComment()
    {
        $replyId = $this->data['reply_id'];
        if (empty($replyId)) {
            $queryRes = $this->query();
            if (!$queryRes) return false;
            $replyId = $this->data['reply_id'];
        }
        if (!isset($this->replyComment) || $this->replyComment->id != $replyId) {
            $this->replyComment = new VideoComment($replyId);
        }
        return $this->replyComment;
    }

    public function getUserId()
    {
        if (empty($this->data['user_id'])) {
            $queryRes = $this->query();
        }
        return $this->data['user_id'];
    }

    public function isOwn(User $user)
    {
        $aliasType = $user->getAliasType();
        $aliasId = $user->getAliasId();
        if ($aliasType != 'user') return false;
        $userId = $this->getUserId();
        if ($aliasId != $userId) return false;
        return true;
    }

    public function like(User $fans, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('video_comment')->where(['id' => $this->data['id']])->setInc('like_count', abs($value));
        } else if ($value < 0) {
            $num = Db::name('video_comment')->where(['id' => $this->data['id']])->setDec('like_count', abs($value));
        }
        if ($num > 0) {
            //评论的作者
            $user = $this->getUser();
            $user->like($fans, $value);
            $user->updateChangeFields();
        }
    }

    public function reply(VideoComment $comment, $value)
    {
        if ($value > 0) {
            Db::name('video_comment')->where(['id' => $this->data['id']])->setInc('reply_count', abs($value));
        } else if ($value < 0) {
            Db::name('video_comment')->where(['id' => $this->data['id']])->setDec('reply_count', abs($value));
        }
    }
}