<?php


namespace app\api\service\live;


use app\api\service\Follow;
use app\api\service\LiveBase2;
use think\Db;

class Close extends LiveBase2
{
    //直播收益(主播端)
    public function facePlateByAnchor($room_id)
    {
        $endLiveInfo = Db::name('live_history')->where(['room_id' => $room_id])->field('user_id, live_like, nickname, avatar, profit, start_time, end_time, real_audience, robot')->find();

        if (empty($endLiveInfo)) return make_error('请求参数有误');

        $duration = $endLiveInfo['end_time'] - $endLiveInfo['start_time'];

        $endLiveInfo['duration'] = $this->diffTime($duration, '');

        $endLiveInfo['new_fans'] = 0;

        $endLiveInfo['total_audience'] = $endLiveInfo['real_audience'] + $endLiveInfo['robot'];

        unset($endLiveInfo['start_time'], $endLiveInfo['end_time'], $endLiveInfo['real_audience'], $endLiveInfo['robot']);

        return $endLiveInfo;
    }


    //直播收益(客户端)
    public function facePlateByClient($room_id)
    {
        $endLiveInfo = Db::name('live_history')->where(['room_id' => $room_id])->field('user_id, province, city, nickname, avatar')->find();

        if (empty($endLiveInfo)) return make_error('请求参数有误');

        $level = Db::name('user')->where(['user_id' => $endLiveInfo['user_id']])->field('level')->find();

        $followModel = new Follow();

        $endLiveInfo['is_follow'] = (int)$followModel->isFollow($endLiveInfo['user_id']);

        $followList = $followModel->getAllFollow(USERID);

        $followUserList = array_column($followList, 'follow_id');

        $this->where['user_id'] = $followUserList;

        $endLiveInfo['stop_msg'] = '本场直播已结束';

        $followLive = Db::name('live')->where($this->where)->field('id room_id, room_model, cover_url')->limit(4)->select();

        if (count($followLive) < 4)
        {
            $all = Db::name('live')->where(['status'=>1])->whereNotIn('user_id',implode(',', $followUserList))->field('id room_id, room_model, cover_url')->limit(4)->select();
        }


        isset($all) && $followLive = array_merge($followLive, $all);

        foreach ($followLive as &$value) {
            $value['status_desc'] = '正在直播';
            $value['jump'] = getJump('enter_room', ['room_id' => $value['room_id'], 'from' => 'hot']);
        }

        $endLiveInfo['recommendLive'] = [
            'title' => '猜你喜欢的主播',
            'item' => $followLive,
        ];

        $endLiveInfo = array_merge($endLiveInfo, $level);

        return $endLiveInfo;
    }
}