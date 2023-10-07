<?php


namespace app\api\service\comment;


use app\api\service\Comment;
use bxkj_common\RabbitMqChannel;
use think\Db;

class Del extends Comment
{
    //删除评论
    public function delete($id, $user_id)
    {
        $comment_count = 0;

        $where = ['id'=>$id, 'user_id'=>$user_id];

        $comment = Db::name('video_comment')->where($where)->find();

        if (empty($comment)) return $this->setError('删除评论错误~');

        $res = Db::name('video_comment')->where($where)->delete();

        if ($res === false) return $this->setError('删除评论错误');

        $this->redis->del(self::$commentPrefix.$id); //该评论下的点赞记录删除

        if (empty($comment['reply_id']))
        {
            $video = Db::name('video')->where(['id'=>$comment['video_id']])->find();

            $video['comment_sum'] > 0 && $comment_count = --$video['comment_sum'];
        }
        else{
            $parentComment = Db::name('video_comment')->where(['id'=>$comment['master_id']])->find();

            $parentComment['reply_count'] > 0 && $comment_count = --$parentComment['reply_count'];
        }

        $rabbitChannel = new RabbitMqChannel(['user.behavior']);

        $rabbitChannel->exchange('main')->sendOnce('user.behavior.comment_delete', [
            'user_id' => USERID,
            'video_id' => $comment['video_id'],
            'del_num' => (int)$res,
            'parent_id' => isset($parentComment) ? $parentComment['id'] : 0,
            'reply_id' => empty($comment['reply_id']) ? 0 : $comment['reply_id'],
            'comment_id' => $id
        ]);
        $total = Db::name('video_comment')->where(['video_id'=> $comment['video_id']])->count();
        return ['reply_comment_count' => $comment_count, 'total' => $total];
    }



    //删除整个目标下的所有评论
    public function deleteAll($video_id)
    {
        $commentModel = new VideoComment();

        $allComments = Db::name('video_comment')->where(['video_id'=>$video_id])->column('id');

        if (!empty($allComments))
        {
            $delRes = Db::name('video_comment')->where(['video_id'=>$video_id])->delete();

            if($delRes === false) return false;

            if ($delRes !== 0)
            {
                foreach ($allComments as $val)
                {
                    $this->redis->del(self::$commentPrefix.$val['id']); //该评论下的点赞记录删除
                }
            }
        }

        return true;
    }
}