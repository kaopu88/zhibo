<?php

namespace app\core\controller;

use bxkj_common\CoreSdk;
use bxkj_module\service\AppPush;
use bxkj_module\service\Kpi;
use bxkj_module\service\Service;
use bxkj_module\service\UserBehavior;
use bxkj_module\service\UserCreditLog;
use bxkj_push\AomyPush;
use bxkj_common\Console;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_common\SectionManager;
use think\cache\driver\Redis;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class Open extends Controller
{
    public function qiniu_upload_callback()
    {
        $response = [
            'success' => true,
            'key' => Request::post('key'),
            'hash' => Request::post('hash'),
            'fsize' => Request::post('fsize'),
            //'bucket' => Request::post('bucket'),
            //'name' => Request::post('name'),
            'w' => Request::post('w'),
            'h' => Request::post('h'),
            'fname' => Request::post('fname'),
            'mimeType' => Request::post('mimeType'),
        ];
        return json($response);
    }

    public function start_live_film()
    {
        $now = time();
        $timelineId = input('timeline_id');
        if (empty($timelineId)) return json_error(make_error('电影档期ID不能为空'));
        $where = ['id' => $timelineId, 'status' => '0'];
        $timeline = Db::name('live_film_timeline')->where($where)->find();
        if (empty($timeline)) return json_error(make_error('电影档期ID不存在'));
        $filmInfo = Db::name('live_film')->where('id', $timeline['film_id'])->find();
        if (empty($filmInfo)) return json_error(make_error('电影不存在'));
        $userService = new \bxkj_module\service\User();
        $user = $userService->getUser($timeline['anchor_uid']);
        if (empty($user)) return json_error(make_error('主播不存在'));
        $num = Db::name('live')->where(['user_id' => $timeline['anchor_uid']])->count();
        if ($num > 0) {
            return json_error(make_error('主播已经开播'));
        }
        $data = [];
        $data['user_id'] = $user['user_id'];
        $data['nickname'] = $user['nickname'];
        $data['avatar'] = $user['avatar'];
        $data['title'] = $timeline['live_title'] ? $timeline['live_title'] : '';
        $data['pull'] = '';
        $data['stream'] = '';
        $data['cover_url'] = $timeline['live_cover'] ? $timeline['live_cover'] : '';
        $data['province'] = !empty($user['province_name']) ? $user['province_name'] : '';
        $data['city'] = !empty($user['city_name']) ? $user['city_name'] : '';
        $data['district'] = !empty($user['district_name']) ? $user['district_name'] : '';
        $data['type'] = $timeline['type'];
        $data['type_val'] = $timeline['type_val'];
        $data['room_model'] = '2';
        $data['create_time'] = $now;
        $data['room_channel'] = '0';
        $data['status'] = 1;
        Service::startTrans();
        $roomId = Db::name('live')->insertGetId($data);
        if (!$roomId) return json_error(make_error('创建直播间失败'));
        //30秒缓冲时间
        $endTime = $now + $timeline['ad_duration'] + $filmInfo['video_duration'] - $timeline['offset'] + 30;
        $timer = new \bxkj_module\service\Timer();
        $key = $timer->add([
            'trigger_time' => $endTime,
            'cycle' => 0,
            'method' => 'post',
            'data' => [
                'room_id' => $roomId,
                'timeline_id' => $timelineId
            ],
            'url' => CORE_URL . '/open/end_live_film'
        ]);
        if (!$key) return json_error(make_error('创建定时器失败'));
        $update = [
            'room_id' => $roomId,
            'end_timer_key' => $key,
            'status' => '1',
            'start_time' => $now,
            'end_time' => $endTime
        ];
        $num2 = Db::name('live_film_timeline')->where(['id' => $timelineId])->update($update);
        if (!$num2) return json_error(make_error('更新失败'));
        $zombie = config('app.zombie_config');
        $sum = mt_rand($zombie['min'], $zombie['max']);
        $redis = RedisClient::getInstance();
        for ($i = 0; $i < $sum; $i++) {
            $robot_uid = $redis->spop('zombiePool'); //在池内随机拿一个机器人
            $robot = $userService->getUser($robot_uid);
            if ($robot) {
                $redis->zAdd('BG_LIVE:' . $roomId . ':robot', $robot['level'], $robot_uid);
            }
        }
        Db::name('live_film_period')->where(['timeline_id' => $timelineId])->update([
            'start_time' => $now,
            'end_time' => $endTime
        ]);
        Service::commit();
        $userBehavior = new UserBehavior($user['user_id']);
        $userBehavior->live(['room_id' => $roomId]);
        return json_success($data, '开播成功');
    }

    public function end_live_film()
    {
        $timelineId = input('timeline_id');
        if (empty($timelineId)) return json_error(make_error('电影档期ID不能为空'));
        $where = ['id' => $timelineId, 'status' => '1'];
        $timeline = Db::name('live_film_timeline')->where($where)->find();
        if (empty($timeline)) return json_error(make_error('电影档期ID不存在'));
        $roomId = $timeline['room_id'];
        $coreSdk = new CoreSdk();
        $res = $coreSdk->post('live/superCloseRoom', ['room_id' => $roomId, 'msg' => '影片已结束~']);
        $now = time();
        $update = [
            'status' => '2',
            'end_time' => $now
        ];
        Db::name('live_film_timeline')->where(['id' => $timelineId])->update($update);
        Db::name('live_film_period')->where(['timeline_id' => $timelineId])->delete();
        Db::name('live_film_period')->where([
            ['user_id', 'eq', $timeline['anchor_uid']],
            ['end_time', '<=', $now]
        ])->delete();
        return json_success($timeline, '关播成功');
    }


}
