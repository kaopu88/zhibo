<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\User;
use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\VideoTag;

class AtBehavior extends Behavior
{
    //@
    public function at($scene, User $friend)
    {
        if ($this->user->user_id != $friend->user_id) {
            $tag = new VideoTag(VideoTag::getUserTagKey($friend->user_id));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->at($scene, 1);
        }
        return true;
    }
}