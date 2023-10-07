<?php


namespace app\api\service\video;


use app\common\service\Service;
use think\Db;

class Location extends Service
{
    // 根据位置Id获取相同位置的视频数据
    public function videosByLocation($location_id, $offset, $length)
    {
        return Db::name('video')
            ->alias('v')
            ->join('user u', 'v.user_id=u.user_id')
            ->where('v.location_id',$location_id)
            ->field('v.*, u.nickname, u.avatar')
            ->limit($offset,$length)
            ->select();
    }
}