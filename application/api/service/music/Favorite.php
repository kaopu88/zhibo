<?php

namespace app\api\service\music;

use app\api\service\Music;
use think\Db;
use app\api\service\music\MusicData as MusicModel;
use app\api\service\Favorite as FavoriteInterface;


/**
 * 音乐收藏
 * Class Music
 * @package App\Domain\favorite
 */
class Favorite extends Music implements FavoriteInterface
{


    /**
     * 收藏夹列表
     */
    public function listByFavorite($user_id = USERID, $offset = 0, $length = 10)
    {
        $MusicModel = new MusicModel();

        $MusicModel->page(['offset'=>$offset, 'length'=>$length]);
        $res = $MusicModel->musicListByFavorite($user_id, $offset, $length);

        if (empty($res)) return [];

        $this->initialize($res);

        return $res;
    }


    /**
     * 添加进收藏夹
     * @desc 收藏自已喜欢的视频
     */
    public function add($music_id)
    {
        $key = 'collection:'.USERID.':music';

        $MusicModel = new MusicModel();

        $res = $MusicModel->addByFavorite(USERID, $music_id);

        if (!$res) return $this->setError('收藏失败');

        $this->redis->zAdd($key, time(), $music_id);

        return [
            'status' => 1,
            'msg' => '收藏成功',
        ];
    }


    /**
     * 从收藏夹移除
     * @mixed $music_id
     * @return array
     */
    public function remove($music_id)
    {
        $key = 'collection:'.USERID.':music';

        $MusicModel = new MusicModel();

        $MusicModel->removeByFavorite(USERID, $music_id);

        if (!is_array($music_id)) $music_id = [$music_id];

        foreach ($music_id as $id)
        {
            $this->redis->zrem($key, $id);
        }

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
    public function isFavorite($music_id)
    {
        if (empty(USERID)) return 0;

        $key = 'collection:'.USERID.':music';

        if (!$this->redis->exists($key))
        {
            //重建收藏缓存
            $MusicModel = new MusicModel();

            $favorites = $MusicModel->restoreFavorite(USERID);

            if (empty($favorites))
            {
                $this->redis->zAdd($key, 0, $music_id);
                return 0;
            }

            foreach ($favorites as $val)
            {
                $this->redis->zAdd($key, $val['create_time'], $val['music_id']);
            }
        }

        $score = $this->redis->zScore($key, $music_id);

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