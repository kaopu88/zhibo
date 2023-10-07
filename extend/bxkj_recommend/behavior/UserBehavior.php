<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\User;
use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\VideoTag;

class UserBehavior extends Behavior
{
    //关注
    public function follow(User $idol)
    {
        $num = $idol->fans($this->user, 1);
        if ($num) {
            $this->user->follow($idol, 1);
            $idol->updateChangeFields();
        }
        $tag = new VideoTag(VideoTag::getUserTagKey($idol->user_id));
        $uvTag = new UserVideoTag($this->user, $tag);
        $uvTag->follow(1);
    }

    //取消关注
    public function cancelFollow(User $idol)
    {
        $num = $idol->fans($this->user, -1);
        if ($num) {
            $this->user->follow($idol, -1);
            $idol->updateChangeFields();
        }
        $tag = new VideoTag(VideoTag::getUserTagKey($idol->user_id));
        $uvTag = new UserVideoTag($this->user, $tag);
        $uvTag->follow(-1);
    }

    //拉黑
    public function black(User $person)
    {
        $this->user->black($person, 1);
        $tag = new VideoTag(VideoTag::getUserTagKey($person->user_id));
        $uvTag = new UserVideoTag($this->user, $tag);
        $uvTag->black(1);
    }

    //取消拉黑
    public function cancelBlack(User $person)
    {
        $this->user->black($person, -1);
        $tag = new VideoTag(VideoTag::getUserTagKey($person->user_id));
        $uvTag = new UserVideoTag($this->user, $tag);
        $uvTag->black(-1);
    }

    //查看用户
    public function viewUser(User $person)
    {
        $person->beView($this->user, 1);
        $tag = new VideoTag(VideoTag::getUserTagKey($person->user_id));
        $uvTag = new UserVideoTag($this->user, $tag);
        $uvTag->view(1);
        $person->updateChangeFields();
    }

}