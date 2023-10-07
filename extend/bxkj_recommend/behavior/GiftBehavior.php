<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\VideoTag;


class GiftBehavior extends Behavior
{
    //赠送礼物
    public function gift($log)
    {
        $toUid = $log['to_uid'];
        if ($this->user->user_id != $toUid) {
            $scene = $log['scene'] ? $log['scene'] : 'unkown';
            $total = $log['num'] * $log['conv_millet'];
            $tag = new VideoTag(VideoTag::getUserTagKey($toUid));
            $uvTag = new UserVideoTag($this->user, $tag);
            $uvTag->gift($scene, $total * 1);
        }
    }
}