<?php

namespace app\api\service\video;

use bxkj_common\HttpClient;
use app\api\service\Video;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\User;
use think\Db;

/**
 * 视频点赞
 * Class Like
 * @package App\Domain\video
 */
class Like extends Video
{
    //视频点赞记录
    public function getLikeList($user_id, $offset, $length)
    {
        $prefix = config('database.prefix');

        $sql = "SELECT b.*,u.nickname,u.avatar FROM {$prefix}video_like a INNER JOIN {$prefix}video b on a.target_id=b.id INNER JOIN {$prefix}user u ON b.user_id=u.user_id WHERE a.user_id=? ORDER BY a.create_time desc LIMIT ?, ?";

        return Db::query($sql, [$user_id, $offset, $length]);
    }


    public function deleteLikeAll($video_id)
    {
        $rs = Db::name('video_like')->where(['target_id' => $video_id])->delete();

        if ($rs === false && self::$transNum)
        {
            $this->rollback();

            return $this->setError('删除出错');
        }

        return true;
    }

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
        $video_info = Db::name('video')->where('id',$itemId)->find();

        if (empty($video_info)) return $this->setError('视频已删除');

        $total_zan = $video_info['zan_sum']+$video_info['zan_sum2'];

        if ($this->isLike($itemId))
        {
            $this->formatData($total_zan);

            return ['total' => $total_zan];
        }

        $insert_rs = Db::name('video_like')->insert([
            'target_id' => $itemId,
            'user_id' => USERID,
            'create_time' => time(),
        ]);

        if (!$insert_rs) return $this->setError('点赞失败');

        $this->redis->sadd(self::$filmPrefix . self::$admireTag . $itemId, USERID);

        $userModel = new user();

        $userModel->updateUser($video_info['user_id'], ['like_num' => 1]);

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
     * @return \App\Common\BuguCommon\BaseError|array|\bxkj_common\BaseError
     */
    public function unLike($itemId)
    {
        $video_info = Db::name('video')->field('id, user_id, cover_url, zan_sum, zan_sum2')->where('id',$itemId)->find();

        if (empty($video_info)) return $this->setError('视频已删除');

        $total_zan = $video_info['zan_sum']+$video_info['zan_sum2'];

        if (!$this->isLike($itemId))
        {
            $this->formatData($total_zan);

            return ['total' => $total_zan];
        }

        $insert_rs = Db::name('video_like')->where(['target_id' => $itemId, 'user_id' => USERID])->delete();

        if ($insert_rs === false) return $this->setError('取消点赞失败');

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

    //视频下的所有点赞记录
    public function likeListByVideo($user_id, $offset, $length)
    {
        $videos = $this->getLikeList($user_id, $offset, $length);

        return $this->initializeFilm($videos);
    }

}