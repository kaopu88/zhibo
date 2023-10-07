<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\VideoComment;
use bxkj_recommend\model\VideoTag;

class CommentBehavior extends Behavior
{
    //喜欢评论
    public function likeComment(VideoComment $comment)
    {
        $author = $comment->getUser();
        $video = $comment->getVideo();
        $comment->like($this->user, 1);
        if (!$comment->isOwn($this->user)) {
            $tag = new VideoTag(VideoTag::getUserTagKey($author->user_id));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->likeComment(1);
        }
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->likeCommentRel(1);
            }
        }
        return true;
    }

    public function cancelLikeComment(VideoComment $comment)
    {
        $author = $comment->getUser();
        $video = $comment->getVideo();
        $comment->like($this->user, -1);
        if (!$comment->isOwn($this->user)) {
            $tag = new VideoTag(VideoTag::getUserTagKey($author->user_id));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->likeComment(-1);
        }
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->likeCommentRel(-1);
            }
        }
        return true;
    }

    public function comment(VideoComment $comment)
    {
        $video = $comment->getVideo();
        $video->comment($comment, 1);
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->comment(1);
            }
        }
        return true;
    }

    public function reply(VideoComment $comment)
    {
        $video = $comment->getVideo();
        $video->reply($comment, 1);
        $replyComment = $comment->getReplyComment();
        if ($replyComment) {
            $replyComment->reply($comment, 1);
        }
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->replyRel(1);
            }
        }
        if (!$replyComment->isOwn($this->user)) {
            $tag = new VideoTag(VideoTag::getUserTagKey($replyComment->getUserId()));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->reply(1);
        }
    }

    public function delete($commentData)
    {
    }
}