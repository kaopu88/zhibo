<?php

namespace app\api\service;

use app\common\service\User;
use bxkj_common\RabbitMqChannel;
use think\Db;

class Like extends Video
{
    //判断用户是否对影片点过赞
    public function isLike($item_id, $USERTAG = null)
    {
        $USERTAG = isset($USERTAG) ? $USERTAG : USERID;
        $res = $this->redis->sismember(self::$filmPrefix . self::$admireTag . $item_id, $USERTAG);

        return (int)$res;
    }

    //给影片点赞
    public function like($itemId)
    {
        $video_info = Db::name('video')->where(['id'=>$itemId])->find();

        if (empty($video_info)) return $this->setError('视频已删除');

        $total_zan = $video_info['zan_sum']+$video_info['zan_sum2'];

        if ($this->isLike($itemId))
        {
            if($total_zan < 1)  $total_zan = 1;
            $this->formatData($total_zan);
            return ['total' => $total_zan, 'status'=>1];
        }

        $data = [
            'target_id' => $itemId,
            'user_id' => USERID,
            'create_time' => time(),
        ];

        $VideoLike = Db::name('video_like')->insert($data);

        if (!$VideoLike) return $this->setError('点赞失败');

        $this->redis->sadd(self::$filmPrefix . self::$admireTag . $itemId, USERID);

        $total_zan++;

        $this->formatData($total_zan);

        //喜欢视频
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);

        $rabbitChannel->exchange('main')->sendOnce('user.behavior.like_video', [
            'behavior' => 'like_video',
            'data' => [
                'user_id' => USERID,
                'video_id' => $itemId
            ]
        ]);

        return ['total' => $total_zan, 'status'=>1];
    }


    /**
     * 取消影片的赞
     * 取消不一定需要真实的赞数增减(更改)
     * @param $itemId
     * @return array|bool
     */
    public function unLike($itemId)
    {
        $video_info = Db::name('video')->where(['id'=>$itemId])->field('id, user_id, cover_url, zan_sum, zan_sum2')->find();

        if (empty($video_info)) return $this->setError('视频已删除');

        $total_zan = $video_info['zan_sum']+$video_info['zan_sum2'];

        if (!$this->isLike($itemId))
        {
            $this->formatData($total_zan);

            return ['total' => $total_zan, 'status'=>0];
        }

        $where = [
            'target_id' => $itemId,
            'user_id' => USERID,
        ];

        $VideoLike = Db::name('video_like')->where($where)->delete();

        if ($VideoLike === false) return $this->setError('取消点赞失败');

        $this->redis->srem(self::$filmPrefix . self::$admireTag . $itemId, USERID);

        $total_zan > 0 && $total_zan--;

        //取消喜欢视频
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.cancel_like_video', [
            'behavior' => 'cancel_like_video',
            'data' => [
                'user_id' => USERID,
                'video_id' => $itemId
            ]
        ]);

        $this->formatData($total_zan);

        return ['total' => $total_zan, 'status'=>0];
    }
}