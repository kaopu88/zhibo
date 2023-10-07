<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\User;
use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\Video;
use bxkj_recommend\model\VideoTag;

class ShareBehavior extends Behavior
{
    //分享视频
    public function shareVideo(Video $video)
    {
        $exists = $video->exists();
        if (!$exists) return $this->setError('视频不存在');
        $video->share($this->user, 1);
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->shareVideo(1);
            }
        }
        return true;
    }

    //取消分享视频
    public function cancelShareVideo(Video $video)
    {
        $exists = $video->exists();
        if (!$exists) return $this->setError('视频不存在');
        $video->share($this->user, -1);
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $uvTag->shareVideo(-1);
            }
        }
        return true;
    }

    public function shareUser(User $idol)
    {
        $idol->share($this->user, 1);
        if ($idol->user_id != $this->user->user_id) {
            $tag = new VideoTag(VideoTag::getUserTagKey($idol->user_id));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->shareUser(1);
        }
        $idol->updateChangeFields();
    }

    public function cancelShareUser(User $idol)
    {
        $idol->share($this->user, -1);
        if ($idol->user_id != $this->user->user_id) {
            $tag = new VideoTag(VideoTag::getUserTagKey($idol->user_id));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->shareUser(-1);
        }
        $idol->updateChangeFields();
    }


}