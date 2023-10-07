<?php

namespace app\api\service\comment;

use app\api\service\Comment;
use think\Db;

class Lists extends Comment
{
    /**
     * 获取评论
     * @param $video_id
     * @param int $offset
     * @param int $length
     * @param int $master_id
     * @return array
     */
    public function commentList($video_id, $offset = 0, $length = 15, $last_id = null, $master_id = 0, $select_id = 0)
    {
        if (empty($video_id)) return [];

        $this->where = [
            ['video_id', '=', $video_id],
            ['create_time', '<', time()],
        ];

        $this->where2 = [
            'master_id' => $master_id,
        ];

        if ($select_id > 0) {
            $this->where[] = ['id', '=', $select_id];
        }

        $this->order = 'is_top desc, like_count desc, create_time desc';

        //获取评论
         $comments = [];
        if ($select_id == 0) {
            $comments = $this->getComment($offset, $length);
        }

        if ($offset == 0 && $select_id > 0) {
            $comment_top[] = $this->getOneComment($video_id, $select_id);

            if (!empty($comment_top)) {
                $comments = array_merge($comment_top, $comments);
            }
        }

        if (empty($comments)) return [];

        //一级评论时
        if ($master_id == 0) {
            foreach ($comments as &$comment) {
                //有回复时
                if (!empty($comment['reply_count'])) {
                    //获取当前评论下敏感评论个数
                    $sensitive_count = Db::name('video_comment')
                        ->where([
                            ['video_id', '=', $video_id],
                            ['master_id', '=', $comment['id']],
                            ['is_sensitive', '=', 1],
                            ['user_id', '<>', USERID],
                        ])
                        ->count();

                    //获取当前评论下敏感评论个数
                    $reply_count = Db::name('video_comment')
                        ->where(['video_id' => $video_id, 'master_id' => $comment['id']])
                        ->count();

                    $comment['s_count'] = $sensitive_count;

                    $comment['reply_count'] = $reply_count;

                    $this->where2['master_id'] = $comment['id'];

                    $sub_comment = $this->getComment(0, 1);

                    if ($sub_comment[0]['is_sensitive'] == 1 && $sub_comment[0]['user_id'] != USERID && $sensitive_count > 1) {
                        $this->where2['is_sensitive'] = 0;
                        $sub_comment = $this->getComment(0, 1);
                    }

                    $comment['child_list'] = $sub_comment;
                }
            }
        }

        $this->initializeComment($comments);

        return $comments;
    }


    /**
     * 获取评论
     * @param int $offset
     * @param int $length
     * @return array
     */
    protected function getComment($offset = 0, $length = 15)
    {
        $comments = Db::name('video_comment')
            ->where($this->where)
            ->where($this->where2)
            ->order($this->order)
            ->limit($offset, $length)
            ->select();

        return empty($comments) ? [] : $comments;
    }


    /**
     * 获取评论
     * @param $id
     * @param int $isrepl
     */
    public function getOneComment($video_id, $id)
    {

        $comment = Db::name('video_comment')
            ->field('*,id as comment_id')
            ->where(['video_id' => $video_id, 'id' => $id])
            ->find();
        if ($comment['master_id'] > 0) {
            $comment = Db::name('video_comment')
                ->field('*,id as comment_id')
                ->where(['video_id' => $video_id, 'id' => $comment['master_id']])
                ->find();
        }

        return $comment;
    }


    /**
     * 获取评论(备份)
     * @param $video_id
     * @param int $offset
     * @param int $length
     * @param int $master_id
     * @return array
     */
    public function commentList_bak($video_id, $offset = 0, $length = 15, $master_id = 0)
    {
        if ($offset == 0) {
            //获取主播的评论
            $anchor_comment = $this->setAnchorWhere($video_id, $master_id)->getComment(0, 1);

            $exc_id = empty($anchor_comment) ? null : $anchor_comment['id'];

            !empty($anchor_comment) && $length--;

            //获取点赞前三的评论
            $top_comment = $this->setTopThreeWhere($video_id, $exc_id, $master_id)->getComment(0, 3);

            $exc_id_all = empty($top_comment) ? [] : array_column($top_comment, 'id');

            !empty($top_comment) && $length -= count($top_comment);
        } else {
            $exc_id_all = '?';
            $anchor_comment = [];
            $top_comment = [];
        }

        //获取其它的评论
        $lists = $this->setTimeWhere($video_id, $exc_id_all, $master_id)->getComment($offset, $length);

        $comments = array_merge($anchor_comment, $top_comment, $lists);

        if (empty($comments)) return [];

        foreach ($comments as &$comment) {
            //有回复并且获取的是一级评论时
            if (!empty($comment['reply_count']) && $master_id == 0) {
                $sub_comment = $this->setAnchorWhere($video_id, $comment['id'])->getComment(0, 1);

                if (empty($sub_comment)) $sub_comment = $this->setTopThreeWhere($video_id, null, $comment['id'])->getComment(0, 1);

                $comment['child_list'] = $sub_comment;
            }
        }

        $this->initializeComment($comments);

        return $comments;
    }


    /**
     * 获取作者的单条评论
     * @param $video_id
     * @param int $reply_id
     * @return object
     */
    protected function setAnchorWhere($video_id, $master_id = 0)
    {
        //获取视频作者的评论点赞最多的一条
        $this->where = [
            'video_id' => $video_id,
            'is_anchor' => 1,
            'master_id' => $master_id,
        ];

        $this->order = 'like_count, reply_count desc';

        return $this;
    }


    /**
     * 获取获赞前三条的评论
     * @param $video_id
     * @param null $exc_id
     * @param null $reply_id
     * @param int $offset
     * @param int $length
     * @return object
     */
    protected function setTopThreeWhere($video_id, $exc_id = null, $master_id = 0)
    {
        //获取点赞前三评论
        $this->where = [
            'video_id' => $video_id,
            'master_id' => $master_id,
        ];

        !empty($exc_id) && $this->where['id <> ?'] = $exc_id;

        $this->order = 'like_count, create_time desc';

        return $this;
    }


    /**
     * 按时间获剩下的评论(未使用)
     * @param $video_id
     * @param $offset
     * @param $length
     * @param array $not_id
     * @param null $reply_id
     * @return object
     */
    protected function setTimeWhere($video_id, $exc_id = [], $master_id = 0)
    {
        //剩下的按时间排序获取评论
        $this->where = [
            'video_id' => $video_id,
            'master_id' => $master_id,
        ];

        !empty($exc_id) && $this->where['not id'] = $exc_id;

        $this->order = 'create_time desc';

        return $this;
    }
}