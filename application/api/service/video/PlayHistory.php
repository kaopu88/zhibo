<?php


namespace app\api\service\video;


use app\common\service\DsSession;
use app\api\service\Video;
use bxkj_common\RedisClient;
use think\Db;

class PlayHistory extends Video
{
    //浏览记录(只展示用户信息)
    public function historyBrowseRecords($itemId, $offset, $length)
    {
        $where = [
            'a.item_id' => $itemId
        ];

        $list = Db::name('video_play_history')
            ->alias('a')
            ->leftJoin('user b', 'a.user_id=b.user_id')
            ->where($where)
            ->limit($offset,$length)
            ->select();

        $total = Db::name('video_play_history')
            ->alias('a')
            ->leftJoin('user b', 'a.user_id=b.user_id')
            ->where($where)
            ->count();

        return array('list'=>$list,'total'=>$total);
    }

    //写入观看历史
    public function insertPlayHistory($id, $duration)
    {
        $publish_user_id = Db::name('video')->where('id', $id)->value('user_id');

        DsSession::set('video_view_time.'.$publish_user_id, time());

        $res = Db::name('video')->where('id', $id)->setInc('play_sum');
        if (!empty(APP_MEID)) {
            $watchkey = "video:watch:user:" . APP_MEID;
            $redis = RedisClient::getInstance();
            $redis->sAdd($watchkey, $id); //说明已经播放过了
            $redis->expire($watchkey, 86400);
        }
        return $res;
    }
}