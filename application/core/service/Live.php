<?php

namespace app\core\service;

use app\core\model\Live as LiveModel;
use app\core\model\LiveHistory;
use bxkj_common\RedisClient;
use bxkj_module\push\AppPush;
use bxkj_module\service\Service;
use think\Collection;


class Live extends Service
{
    protected static $roomIncomeKey = ':incomeTotal', $roomLike = ':like', $livePrefix = 'BG_LIVE:', $redis = null;

    public function __construct()
    {
        parent::__construct();

        if (!self::$redis instanceof \Redis) self::$redis = RedisClient::getInstance();
    }


    //关闭直播间
    public function destroyRoom($room_id)
    {
        $roomModel = new LiveModel();

        $room = $roomModel->get($room_id);

        if (Collection::make($room)->isEmpty()) return $this->setError('房间信息有误');

        $real_audience = Zombie::getAudienceCount($room_id); //真实用户

        $robot = Zombie::getZombieCount($room_id); //机器人

        $profit = self::$redis->get(self::$livePrefix . $room_id . self::$roomIncomeKey); //收益;

        $live_like = self::$redis->get(self::$livePrefix . $room_id . self::$roomLike); //喜欢

        $liveHistoryModel = new LiveHistory();

        $data = [
            'room_id' => $room->id,
            'user_id' => $room->user_id,
            'nickname' => $room->nickname,
            'avatar' => $room->avatar,
            'start_time' => $room->create_time,
            'title' => $room->title,
            'province' => $room->province,
            'city' => $room->city,
            'district' => $room->district,
            'stream' => $room->stream,
            'cover' => $room->cover_url,
            'type' => $room->type,
            'type_val' => $room->type_val,
            'room_model' => $room->room_model,
            'room_channel' => $room->room_channel,
            'real_audience' => $real_audience,
            'robot' => $robot,
            'end_time' => time(),
            'profit' => (float)$profit,
            'live_like' => $live_like,
        ];

        $liveHistoryModel->data($data);

        $res = $liveHistoryModel->save();

        $room->delete();

        $this->releaseLiveResource($room);

        $duration = $data['end_time'] - $room->create_time;

        $Task = new Task();

        $pk_win_num = self::$redis->get(self::$livePrefix.$room->id.':pk_num');

        $new_fans = self::$redis->zcount("fans:{$room->user_id}", $room->create_time, $data['end_time']+86400);

        $live_effective_time = config('app.live_effective_time');

        $Task->generateTaskDetails([
            'user_id' => $room->user_id,
            'live_duration' => $duration > $live_effective_time ? $duration : 0,
            'light_num' => $live_like,
            'gift_profit' => (float)$profit,
            'new_fans' => (int)$new_fans,
            'pk_win_num' => $pk_win_num,
        ]);

        (new Kpi())->duration($room->user_id, $duration);

        return (boolean)$res;
    }


    //超管关播
    public function superDestroyRoom($room_id, $msg, $params = [])
    {
        $live_info = LiveModel::get($room_id);

        if (empty($live_info)) return $this->setError('直播间已关');

        $PkServer = new Pk();

        $pk_info = $PkServer->checkPk($live_info['user_id']);

        if ($pk_info['is_pk'])
        {
            $pk_id = $PkServer->getPkId($live_info['user_id'], $room_id, $pk_info['flag']);
        } else {
            $pk_id = '';
        }

        $superCloseRoomData = [
            'mod' => 'Live',
            'act' => 'superClose',
            'args' => ['room_id' => $room_id, 'msg' => $msg, 'pk_id' => $pk_id, 'user_id' => $live_info['user_id']],
            'web' => 1
        ];

        $socket = new Socket();

        $res = $socket->connectSocket($superCloseRoomData);

        if (!$res) return $socket->getError();

        $closeRes = $this->destroyRoom($room_id);

        if (!$closeRes) return $this->getError();

        $this->bannedLive($live_info['user_id']); //加入禁播内

        return true;
    }

    //后台开直播间
    public function backgroundCreate($room_id)
    {
        $live_info = LiveModel::get($room_id);
        if (empty($live_info)) return $this->setError('创建房间错误');
        $superCreateRoomData = [
            'mod' => 'Live',
            'act' => 'create',
            'args' => ['room_id' => $room_id, 'user_id' => $live_info['user_id']],
            'web' => 1
        ];

        $socket = new Socket();
        $res = $socket->connectSocket($superCreateRoomData);
        if (!$res) return $socket->getError();
        return true;
    }



    //超管禁播
    public function bannedLive($user_id)
    {

    }


    //释放直播资源
    protected function releaseLiveResource($room)
    {
        Zombie::releaseZombieResource($room->id); //回收机器人资源

        $task_id = self::$redis->get('BG_LIVE:'.$room->id.':message_task');

        if (!empty($task_id))
        {
            $AppPush = new AppPush();

            $AppPush->cancel($task_id);
        }

        $tops = self::$redis->get('cache:hotTop');
        if(!empty($tops)) {
            $tops = explode(',', $tops);
            $tops = array_diff($tops, [$room->user_id]);
            $tops = trim(implode(',', $tops),',');
            self::$redis->set('cache:hotTop',$tops);
        }

        self::$redis->srem(self::$livePrefix.'Living', $room->user_id); //状态下线


        self::$redis->del(self::$livePrefix . $room->id . ':anchorInfo');
        self::$redis->del(self::$livePrefix . $room->id . ':robot');
        self::$redis->del(self::$livePrefix . $room->id . ':like');
        self::$redis->del(self::$livePrefix . $room->id . ':audience');
        self::$redis->del(self::$livePrefix . $room->id . ':incomeTotal');
        self::$redis->del(self::$livePrefix . $room->id . ':message');
        self::$redis->del(self::$livePrefix . $room->id . ':message_level');
        self::$redis->del(self::$livePrefix . $room->id . ':allNum');
        self::$redis->del(self::$livePrefix . $room->id . ':PAY');
        self::$redis->del(self::$livePrefix . $room->id . ':PWD');
        self::$redis->del(self::$livePrefix . $room->id . '::KICK');
        self::$redis->del(self::$livePrefix . $room->id . '::SHUT');
        self::$redis->del(self::$livePrefix . $room->id . ':pk_num');
        self::$redis->del(self::$livePrefix . $room->id . ':message_task');
        self::$redis->del(self::$livePrefix . $room->id . ':robotTask');

        //$keys = self::$redis->keys(self::$livePrefix . $room->id . ':*');
        //self::$redis->del($keys);
    }








}