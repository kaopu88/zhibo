<?php

namespace bxkj_recommend\model;

use bxkj_recommend\exception\Exception;

class UserVideoTag extends Model
{
    protected $user;
    protected $videoTag;
    protected $key;
    protected $userMark;
    protected $detail;

    public function __construct(User &$user, VideoTag $videoTag)
    {
        parent::__construct();
        $this->user = &$user;
        $this->videoTag = $videoTag;
        $this->userMark = $this->user->getUserMark();
        $tagId = $this->videoTag->id;
        $this->key = "uvtag:{$this->userMark}:{$tagId}";
        $this->data = is_array($this->data) ? $this->data : [];
        $this->data['key'] = $this->key;
    }

    public function getTagId()
    {
        return $this->videoTag->id;
    }

    //视频点赞
    public function like($value = 1)
    {
        $res = $this->redis->hIncrBy($this->key, 'like_num', $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function shareVideo($value = 1)
    {
        $res = $this->redis->hIncrBy($this->key, 'share_v_num', $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function shareUser($value = 1)
    {
        $res = $this->redis->hIncrBy($this->key, 'share_u_num', $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    //评论点赞
    public function likeComment($value)
    {
        $res = $this->redis->hIncrBy($this->key, 'like_comment_num', $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    //评论点赞关联视频
    public function likeCommentRel($value)
    {
        $res = $this->redis->hIncrBy($this->key, 'like_comment_rel_num', $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    //观看
    public function watch($startTime, $state, $duration, $stateValue = 1)
    {
        //记录的单位是ms
        $res = $this->redis->hIncrBy($this->key, 'duration', $duration);
        $res2 = $this->redis->hIncrBy($this->key, "{$state}_num", $stateValue);
        if ($res || $res2) $this->user->pushChangeTag($this);
    }

    //关注
    public function follow($value)
    {
        $res = $this->redis->hIncrBy($this->key, 'follow_num', $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    //@
    public function at($scene, $value)
    {
        $scene = strtolower($scene);
        $res = $this->redis->hIncrBy($this->key, "at_{$scene}_num", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function gift($scene, $value)
    {
        $scene = strtolower($scene);
        $res = $this->redis->hIncrBy($this->key, "gift_{$scene}", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    //评论数量
    public function comment($value)
    {
        $res = $this->redis->hIncrBy($this->key, "comment_num", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function reply($value)
    {
        $res = $this->redis->hIncrBy($this->key, "reply_num", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function replyRel($value)
    {
        $res = $this->redis->hIncrBy($this->key, "reply_rel_num", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function black($value)
    {
        $res = $this->redis->hIncrBy($this->key, "black_num", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function view($value)
    {
        $res = $this->redis->hIncrBy($this->key, "view_num", $value);
        if ($res) $this->user->pushChangeTag($this);
    }

    public function getDetail()
    {
        return $this->detail;
    }

    public function evaluate()
    {
        $total = 0;
        $tagType = $this->videoTag->getType();
        $this->detail = $this->redis->hGetAll($this->key);
        if ($tagType == '用户') {
            return (new UserTagScore($this))->evaluate();
        } else {
            return (new CommonTagScore($this))->evaluate();
        }
        return $total;
    }
}