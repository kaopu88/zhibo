<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\model\User;

/**
 * Class BehaviorBridge
 * @package bxkj_recommend\behavior
 * @method \bxkj_recommend\behavior\LikeBehavior like(\bxkj_recommend\model\Video $video)
 * @method \bxkj_recommend\behavior\LikeBehavior cancelLike(\bxkj_recommend\model\Video $video)
 * @method \bxkj_recommend\behavior\LikeBehavior unlike(\bxkj_recommend\model\Video $video)
 * @method \bxkj_recommend\behavior\WatchBehavior batchWatch($group)
 * @method \bxkj_recommend\behavior\WatchBehavior watch(\bxkj_recommend\model\Video $video, $watchData)
 * @method \bxkj_recommend\behavior\AtBehavior at($scene, \bxkj_recommend\model\User $friend)
 * @method \bxkj_recommend\behavior\UserBehavior follow(\bxkj_recommend\model\User $idol)
 * @method \bxkj_recommend\behavior\UserBehavior cancelFollow(\bxkj_recommend\model\User $idol)
 * @method \bxkj_recommend\behavior\UserBehavior black(\bxkj_recommend\model\User $person)
 * @method \bxkj_recommend\behavior\UserBehavior cancelBlack(\bxkj_recommend\model\User $person)
 * @method \bxkj_recommend\behavior\CommentBehavior likeComment(\bxkj_recommend\model\VideoComment $comment)
 * @method \bxkj_recommend\behavior\CommentBehavior cancelLikeComment(\bxkj_recommend\model\VideoComment $comment)
 * @method \bxkj_recommend\behavior\CommentBehavior comment(\bxkj_recommend\model\VideoComment $comment)
 * @method \bxkj_recommend\behavior\CommentBehavior reply(\bxkj_recommend\model\VideoComment $comment)
 * @method \bxkj_recommend\behavior\GiftBehavior gift($log)
 * @method \bxkj_recommend\behavior\ShareBehavior shareVideo(\bxkj_recommend\model\Video $video)
 * @method \bxkj_recommend\behavior\ShareBehavior shareUser(\bxkj_recommend\model\User $user)
 * @method \bxkj_recommend\behavior\UserBehavior viewUser(\bxkj_recommend\model\User $idol)
 */
class BehaviorBridge
{
    protected $user;

    public function __construct(User &$user)
    {
        $this->user = &$user;
    }

    public function __call($name, $arguments)
    {
        $arr = [
            'like' => 'like,unlike,cancelLike',
            'watch' => 'batchWatch,watch',
            'at' => 'at',
            'user' => 'follow,cancelFollow,black,cancelBlack,viewUser',
            'comment' => 'likeComment,cancelLikeComment,comment,reply',
            'gift' => 'gift',
            'share' => 'shareVideo,shareUser,cancelShareVideo,cancelShareUser'
        ];
        foreach ($arr as $key => $value) {
            $values = str_to_fields($value);
            if (in_array($name, $values)) {
                $className = '\\bxkj_recommend\behavior\\' . parse_name($key, 1, true) . 'Behavior';
                return call_user_func_array([new $className($this->user), $name], $arguments);
            }
        }
        return false;
    }
}