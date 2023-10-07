<?php

namespace app\api\service\video;

use bxkj_module\service\User;
use think\Db;
use app\common\service\Service;
use app\api\service\Favorite as FavoriteInterface;
use app\api\service\Video;
use app\api\service\video\Favorites as FavoritesModel;

/**
 * 收藏视频
 * Class Video
 * @package App\Domain\favorite
 */
class Favorite extends Video implements FavoriteInterface
{

    /**
     * 收藏夹列表
     */
    public function listByFavorite($user_id = USERID, $offset = 0, $length = 10)
    {
        $FavoritesModel = new FavoritesModel();

        $res = $FavoritesModel->videoListByFavorite($user_id, $offset, $length);

        $res = $this->parseVideo($res, [], $user_id);

        return $res;
    }


    /**
     * 添加进收藏夹
     * @desc 收藏自已喜欢的视频
     */
    public function add($video_id)
    {
        $key = 'collection:'.USERID.':video';

        $FavoritesModel = new FavoritesModel();

        $video_info = Db::name('video')->where('id', $video_id)->find();

        if (empty($video_info)) return $this->setError('该视频还未审核');

        $this->startTrans();

        $res = $FavoritesModel->addByFavorite(USERID, $video_id);

        if (!$res)
        {
            $this->rollback();
            return $this->setError('收藏失败');
        }

        $userModel = new user();

//        $userModel->updateData($video_info['user_id'], ['points' => 2]);

        $userModel->updateData(USERID, ['collection_num' => 1]);

        $up =Db::name('video')->where('id', $video_id)->setInc('collection_sum');

        if (!$up)
        {
            $this->rollback();
            return $this->setError('收藏失败');
        }

        $this->redis->zAdd($key, time(), $video_id);

        $this->commit();

        return [
            'status' => 1,
            'msg' => '收藏成功',
        ];
    }


    /**
     * 从收藏夹移除
     * @return array
     */
    public function remove($video_id)
    {
        $key = 'collection:'.USERID.':video';

        $MusicModel = new FavoritesModel();

        $MusicModel->removeByFavorite(USERID, $video_id);

        if (!is_array($video_id)) $video_id = [$video_id];

        foreach ($video_id as $id)
        {
            $this->redis->zrem($key, $id);
        }

        $sum = count($video_id);

        $user = new \app\common\service\User();
        $user->updateData(USERID,['collection_num' => -$sum]);

        return [
            'status' => 0,
            'msg' => '移除成功',
        ];
    }


    /**
     * 是否存在于收藏夹
     * @param $type
     * @param $itemId
     * @return int
     */
    public function isFavorite($video_id)
    {
        if (empty(USERID)) return 0;

        $key = 'collection:'.USERID.':video';

        if (!$this->redis->exists($key))
        {
            //重建收藏缓存
            $VideoModel = new FavoritesModel();

            $favorites = $VideoModel->restoreFavorite(USERID);

            if (empty($favorites))
            {
                $this->redis->zAdd($key, 0, $video_id);
                return 0;
            }

            foreach ($favorites as $val)
            {
                $this->redis->zAdd($key, $val['create_time'], $val['video_id']);
            }
        }

        $score = $this->redis->zScore($key, $video_id);

        return empty($score) ? 0 : 1;
    }


    /**
     * 删除一个或多个收藏
     * @param array $items
     * @return array
     */
    public function delete(array $items)
    {
        return $this->remove($items);
    }

}