<?php

namespace app\api\service\comment;

use app\api\service\Comment;
use bxkj_common\RabbitMqChannel;
use think\Db;
use bxkj_common\RedisClient;

class Like extends Comment
{

    //给评论点赞
    public function like($id, $user_id=USERID)
    {
        $comment_info = Db::name('video_comment')->field('video_id, user_id, like_count')->where(id, $id)->find();

        if (empty($comment_info)) return $this->setError('无此评论');

        $redis = RedisClient::getInstance();

        if ($redis->zScore('blacklist:' . $comment_info['user_id'], $user_id)) return $this->setError('点赞失败');

        $comment_info['like_count']++;

        $this->formatData($comment_info['like_count']);

        $redis->sadd(self::$commentPrefix . $id, $user_id);

        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.like_comment', [
            'user_id' => USERID,
            'comment_id' => $id
        ]);

        return ['status'=>1, 'total'=>$comment_info['like_count']];
    }


    //取消点赞
    public function unLike($id)
    {
        $comment_info = Db::name('video_comment')->field('video_id, user_id, like_count')->where(id, $id)->find();

        if (empty($comment_info)) return $this->setError('无此评论');

        $comment_info['like_count'] > 0 && $comment_info['like_count']--;

        $this->formatData($comment_info['like_count']);

        $redis = RedisClient::getInstance();

        $redis->srem(self::$commentPrefix . $id, USERID);

        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.cancel_like_comment', [
            'user_id' => USERID,
            'comment_id' => $id
        ]);

        return ['status'=>0, 'total'=>$comment_info['like_count']];
    }


    //是否已点赞
    public function isLike($id, $user_id=USERID)
    {
        $redis = RedisClient::getInstance();

        $res = $redis->sismember(self::$commentPrefix . $id, $user_id);

        return (int)($res == 1);
    }

}