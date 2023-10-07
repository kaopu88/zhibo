<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\Video;

class LikeBehavior extends Behavior
{
    //喜欢
    public function like(Video $video)
    {
        $exists = $video->exists();
        if (!$exists) return $this->setError('视频不存在');
        $video->like($this->user, 1);
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->like(1);
            }
        }
        return true;
    }

    //不喜欢
    public function unlike(Video $video)
    {

    }

    //取消喜欢
    public function cancelLike(Video $video)
    {
        $exists = $video->exists();
        if (!$exists) return $this->setError('视频不存在');
        $video->like($this->user, -1);
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->like(-1);
            }
        }
        return true;
    }
}