<?php

namespace app\api\service;

use app\common\service\Service;

use bxkj_module\exception\ApiException;
use app\api\service\comment\Like;
use bxkj_module\service\User;

/**
 * 评论
 * Class Comments
 * @package App\Domain
 */
class Comment extends Service
{
    protected static $commentPrefix = 'comment:';

    protected $Like = null;

    protected $where = [];

    protected $order = '';

    protected static $allow_field = [
        'comment_id', 'user_id', 'video_id', 'nickname', 'reply_count', 'avatar', 'reply_uid', 'reply_nickname', 'reply_avatar', 'content', 'like_count', 'is_anchor', 'friend_group', 'is_like', 'child_list', 'is_reply', 'publish_time', 'reply_count_str'];

    public function __construct()
    {
        parent::__construct();
    }


    protected function pictureFormat(array $pics=[])
    {
        if (empty($pics)) return '';

        $tmp = [];

        foreach ($pics as $picture)
        {
            $imgInfo = file_get_contents($picture.'?imageInfo');

            if (!empty($imgInfo))
            {
                $imgInfo = json_decode($imgInfo, true);

                if (floor($imgInfo['height'] / $imgInfo['width']) > 2 || floor($imgInfo['width'] / $imgInfo['height']) > 2)
                {
                    $format = '?imageView2/1/w/260/h/260';
                    $rate = 1;
                }
                else{
                    $format = '?imageView2/2/w/260';
                    $rate = round($imgInfo['width']/$imgInfo['height'], 2);
                }

                $picture_info = ['thumb'=>$picture.$format, 'image'=>$picture, 'rate'=>$rate];

                array_push($tmp, $picture_info);
            }
        }

        return empty($tmp) ? '' : json_encode($tmp);
    }


    /**
     * 初始化评论数据
     * @param $data
     */
    protected function initializeComment(&$data)
    {
        if (empty($data)) return;

        $allow_field = array_flip(self::$allow_field);

        $now = time();

        $user_info = [];

        if ($this->Like === null) $this->Like = new Like();

        $user_ids = array_unique(array_column($data, 'user_id'));

        $reply_ids = array_unique(array_column($data, 'reply_uid'));

        $all_uid = array_unique(array_merge($user_ids, $reply_ids));

        $userModel = new user();

        $users = $userModel->getUsers($all_uid);

        if (is_error($users)) throw new ApiException($users->message);

        while (is_array($users) && $users)
        {
            $user = array_shift($users);

            $user_info[$user['user_id']] = $user;
        }

        foreach ($data as $key=>&$val)
        {
            //视频发布者和发布者本人可见&& $val['user_id'] != $val['video_id']
            if ($val['is_sensitive'] && $val['user_id'] != USERID)
            {
                unset($data[$key]);
                continue;
            }

            isset($val['child_list']) ? $this->initializeComment($val['child_list']) : $val['child_list'] = [];

            $val['friend_group'] = [];

            $val['is_like'] = $this->Like->isLike($val['id']);

            $val['publish_time'] = $now-$val['create_time'] > 60 ? time_before($val['create_time'], '前') : '刚刚';

            $val['nickname'] = $user_info[$val['user_id']]['nickname'];

            $val['avatar'] = $user_info[$val['user_id']]['avatar'];

            $val['comment_id'] = (string)$val['id'];

            if (!empty($val['master_id']) && !empty($val['reply_id']))
            {
                $val['is_reply'] = $val['master_id'] == $val['reply_id'] ? 0 : 1;
            }
            else{
                $val['is_reply'] = 0;
            }

            //@好友信息
            if (!empty($val['friends'])) $val['friend_group'] = json_decode($val['friends'], true);

            $val['reply_nickname'] = empty($val['reply_uid']) ? '' :$user_info[$val['reply_uid']]['nickname'];

            $val['reply_avatar'] = empty($val['reply_uid']) ? '' :$user_info[$val['reply_uid']]['avatar'];

            if (isset($val['s_count']))
            {
                $val['reply_count_str'] = $val['reply_count'] = $val['reply_count'] >= $val['s_count'] ? $val['reply_count'] - $val['s_count'] : $val['reply_count'];
            }
            else{
                $val['reply_count_str'] = $val['reply_count'];
            }

            $this->formatData($val, ['reply_count_str', 'like_count']);
            $val['video_id']=(string)$val['video_id'];
            $val['user_id']=(string)$val['user_id'];
            $val['reply_count']=(string)$val['reply_count'];
            $val['reply_uid']=(string)$val['reply_uid'];

            $val = array_intersect_key($val, $allow_field);
        }

        $data = array_values($data);
    }



    /**
     *
     * @param $video_id
     * @param $comment_id
     * @param $length
     * @return array
     */
    public function commentModel($video_id, $comment_id=null, $length=10)
    {




        return [];
    }









}