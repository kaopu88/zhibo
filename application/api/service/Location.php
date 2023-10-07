<?php


namespace app\api\service;


use bxkj_module\exception\ApiException;
use app\common\service\Service;
use think\Db;

class Location extends Service
{
    //恢复收藏夹数据
    public function restoreFavorite($user_id)
    {
        $res = Db::name('location_favorites')->where(['user_id' => $user_id])->select();
        return $res;
    }

    //移除收藏的位置
    public function removeByFavorite($user_id, $location_id)
    {
        $res = Db::name('location_favorites')
            ->where(['user_id' => $user_id, 'location_id' => $location_id])
            ->delete();

        return $res;
    }


    //位置添加到收藏夹内
    public function addByFavorite($user_id, $location_id, $cover)
    {
        $data = [
            'user_id' => $user_id,
            'location_id' => $location_id,
            'create_time' => time(),
            'cover' => $cover,
        ];

        try{
            $res = Db::name('location_favorites')
                ->insert($data);
        } catch (ApiException $e) {
            throw $e;
        }

        return $res;
    }


    //收藏夹内的位置列表
    public function locationListByFavorite($user_id, $offset, $length)
    {
        $prefix = config('database.prefix');

        $sql = "SELECT l.street_address, l.name, l.lat, l.lng,l.id, l.id location_id, l.city_id, l.province_id, lf.cover FROM {$prefix}location_favorites lf INNER JOIN {$prefix}location l ON lf.location_id=l.id WHERE lf.user_id=? ORDER BY lf.create_time DESC LIMIT ?, ?";

        return Db::query($sql, [$user_id, $offset, $length]);
    }
}