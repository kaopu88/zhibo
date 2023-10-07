<?php

namespace app\api\service\video;

use bxkj_module\exception\ApiException;
use app\api\service\Video;
use think\Db;

class Favorites extends Video
{
    protected function getTableName()
    {
        return 'video_favorites';
    }


    //恢复收藏夹内的视频
    public function restoreFavorite($user_id)
    {
        $res = Db::name($this->getTableName())
            ->where(['user_id' => $user_id])
            ->select();

        return $res;
    }

    //移除收藏夹内的视频
    public function removeByFavorite($user_id, $video_id)
    {
        $res = Db::name($this->getTableName())
            ->where(['user_id' => $user_id, 'video_id' => $video_id])
            ->delete();

        return $res;
    }

    //添加收藏夹
    public function addByFavorite($user_id, $video_id)
    {
        $data = [
            'user_id' => $user_id,
            'video_id' => $video_id,
            'create_time' => time()
        ];

        try{
            $res = Db::name($this->getTableName())
                ->insert($data);
        } catch (ApiException $e) {
            throw $e;
        }

        return $res;
    }

    //收藏夹列表数据
    public function videoListByFavorite($user_id, $offset, $length)
    {

        $res = Db::name('video_favorites')
            ->alias('vf')
            ->join('video v','vf.video_id=v.id')
            ->where(['vf.user_id'=>$user_id])
            ->order('vf.create_time DESC')
            ->limit($offset, $length)
            ->select();

        return $res;
    }


    public function deleteFavoriteAll($video_id)
    {
        $rs =  Db::name($this->getTableName())->where(['video_id' => $video_id])->delete();

        if ($rs === false && self::$transNum)
        {
            $this->rollback();

            return $this->setError('删除出错');
        }

        return true;
    }

}