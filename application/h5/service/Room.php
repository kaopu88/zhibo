<?php

namespace app\h5\service;

use bxkj_module\exception\ApiException;
use bxkj_module\service\Service;
use bxkj_module\service\Timer;
use bxkj_module\service\User;
use bxkj_module\service\UserBehavior;
use bxkj_common\RedisClient;
use think\Db;

class Room extends Service
{

    protected static $storage = [
        'user_id' => 0,
        'nickname'=> '',
        'avatar'=> '',
        'create_time'=> 0,
        'title'=> '默认标题~',
        'province'=>  '',
        'city' => '',
        'district' => '',
        'cover_url' => '',
        'stream'=> '',
        'pull'=> '',
        'type'=> 0,
        'type_val'=> '0',
        'room_channel'=> '0',
        'room_model'=> 0,
        'status' => 1,
    ];


    protected function insideSource($data)
    {
        if (empty($data['video_id'])) return $this->setError('站内资源时,视频ID不能为空');

        $filmInfo = Db::name('film')->where(['id'=>$data['video_id']])->find();

        if (empty($filmInfo)) return $this->setError('未查找到该视频');

        $videoModel = new VideoManage();

        $info = $videoModel->getVideoInfo($filmInfo['video_id'], ['basicInfo', 'metaData']);

        //设置封面地址
        if (empty($data['cover_url']))
        {
            if (!empty($filmInfo['cover_url']))
            {
                $data['cover_url'] = $filmInfo['cover_url'];
            }
            else{
                //先生成截图
                $videoProcess = new VideoProcess();

                call_user_func_array([$videoProcess, 'createSnapshot'], [['video_id'=>$filmInfo['video_id']]]);

                $videoProcess->createSnapshot($filmInfo['video_id']);

                $snapshotRes = $videoProcess->getResult();

                if ($snapshotRes['code'] != 0) return $this->setError('截取视频封面错误,请手动上传封面');

                //再获取
                $snapshot = (new VideoManage())->getVideoInfo($filmInfo['video_id'], 'snapshotByTimeOffsetInfo');

                $data['cover_url'] = $snapshot['snapshotByTimeOffsetInfo']['snapshotByTimeOffsetList'][0]['picInfoList'][0]['url'];

                //更新视频数据库数据
                Db::name('film')->where('id', $data['video_id'])->update(['cover_url'=>$data['cover_url']]);
            }
        }
        else
        {
            $cover_url = parse_url($data['cover_url']);

            if(empty($cover_url['scheme']))
            {
                $data['cover_url'] .= 'http://';
            }
        }

        //设置视频总时长
        if (empty($filmInfo['duration']))
        {
            if ($info['code'] != 0) return $this->setError('获取影片基础信息错误,请联系管理员');

            $data['video_duration'] = $info['basicInfo']['duration'];

            //更新视频数据库数据
            Db::name('film')->where('id', $data['video_id'])->update(['duration'=>$data['video_duration'], 'film_size'=>$info['basicInfo']['size']]);
        }
        else{
            $data['video_duration'] = $filmInfo['duration'];
        }

        $data['video_rate'] = $info['code'] == 0 ? round($info['metaData']['width']/$info['metaData']['height'], 2) : '';

        $videoTitle = trim(str_replace(['.mp4', '#', '@'], '', $filmInfo['title']), '#');

        $data['video_url'] = $filmInfo['video_url'];

        $data['video_title'] = $videoTitle;

        return $data;
    }


    protected function outsideSource($data)
    {
        $allow_video_format = ['wmv', 'avi', 'mkv', 'rmvb', 'rm', 'xvid', 'mp4', '3gp', 'mpg'];

        if (empty($data['video_duration'])) return $this->setError('站外资源视频时长不能空');

        if (empty($data['video_url'])) return $this->setError('站外资源视频地址不能空');

        $video_suffix = pathinfo($data['video_url'], PATHINFO_EXTENSION);

        if (!in_array($video_suffix, $allow_video_format)) return $this->setError('不支持的视频格式或视频地址不正确');

        if (empty($data['cover_url'])) return $this->setError('站外资源直播封面必须设置');

        $cover_url = parse_url($data['cover_url']);

        if(empty($cover_url['scheme']))
        {
            $data['cover_url'] .= 'http://';
        }

        $video_url = parse_url($data['video_url']);

        if(empty($video_url['scheme']))
        {
            $data['video_url'] .= 'http://';
        }

        $data['video_title'] = $data['title'];

        list($h, $m, $s) = explode(':', $data['video_duration']);

        $data['video_duration'] = $h*60*60+$m*60+$s;

        return $data;
    }



    public function addLiveData($data)
    {
        $userModel = new User();

        $user = $userModel->getUser($data['anchor_id']);

        if(empty($user)) return $this->setError('主播信息有误请更换主播ID');

        if (empty($data['title'])) return $this->setError('直播标题不能为空');

        if ($data['video_source'] == 0)
        {
           $data = $this->insideSource($data);

           if ($data === false) return false;
        }
        else if ($data['video_source'] == 1)
        {
            $data = $this->outsideSource($data);

            if ($data === false) return false;
        }
        else{
            return $this->setError('缺少视频播放地址或视频ID');
        }

        $url = parse_url($data['cover_url']);

        if (0 === strcasecmp($url['host'], STORAGE_URL))
        {
            if (!empty($url['query']))
            {
                $data['cover_url'] = "{$url['scheme']}://{$url['host']}{$url['path']}";
            }
        }

        $timer_task = empty($data['timer_task']) ? 0 : strtotime($data['timer_task']);

        if (empty($data['play_time']))
        {
            $play_time = 0;
        }
        else
        {
            $p = str_replace(['小时','分'], ':', $data['play_time']);

            list($hour, $minute) = explode(':', rtrim($p, ':'));

            $play_time = $hour*3600+$minute*60;
        }

        $group_data = [
            'create_time'=> time(),
            'live_title' => $data['title'],
            'video_source' => $data['video_source'],
            'live_uid' => $user['user_id'],
            'live_mode' => 2,
            'video_duration' => $data['video_duration'],
            'play_time'=> $play_time,
            'video_url' => $data['video_url'],
            'video_title' => $data['video_title'],
            'live_cover' => $data['cover_url'],
            'live_type' => $data['type'],
            'live_type_val' => $data['type_val'] ? $data['type_val'] : '',
            'room_id' => 0,
            'is_live' => 0,
            'status' => 1,
            'timer_task' => $timer_task,
        ];

        if ($data['video_source'] == 0)
        {
            $group_data['video_id'] = $data['video_id'];
            $group_data['video_rate'] = $data['video_rate'];
        }

        try{
            $roomId = Db::name('live_film')->insertGetId($group_data);
        }catch (ApiException $e)
        {
            return $this->setError('请检查数据, 是否已有相同的数据');
        }

        if (!$roomId) return $this->setError('添加电影直播数据错误');

        if ($timer_task != 0)
        {
            $timer = new Timer();

            $taskData = [
                'data' => json_encode(['id'=>$roomId]),
                'url'=>H5_URL.'/createRoom',
                'cycle'=>0,
                'trigger_time'=>$timer_task,
            ];

            $timer->add($taskData);
        }

        return true;
    }



    public function createRoom($data)
    {
        $userModel = new User();

        $anchor = $userModel->getUser($data['live_uid']);

        if(empty($anchor)) return $this->setError('主播信息有误请更新直播数据', 8000);

        $nowTime = time();

        self::$storage['create_time'] = $nowTime;
        self::$storage['stream'] = 'BGSTREAM_'.$anchor['user_id'] . '_' . $nowTime; //生成流名
        self::$storage['user_id'] = $anchor['user_id'];
        self::$storage['nickname'] = $anchor['nickname'];
        self::$storage['avatar'] = $anchor['avatar'];
        self::$storage['title'] = $data['live_title'];
        self::$storage['province'] = !empty($anchor['province_name']) ? $anchor['province_name'] : '';
        self::$storage['city'] = !empty($anchor['city_name']) ? $anchor['city_name'] : '';
        self::$storage['district'] = !empty($anchor['district_name']) ? $anchor['district_name'] : '';
        self::$storage['cover_url'] = $data['live_cover'];
        self::$storage['room_model'] = $data['live_mode'];
        self::$storage['pull'] = $data['video_url'];
        self::$storage['type'] = $data['live_type'];
        self::$storage['type_val'] = $data['live_type_val'] ? $data['live_type_val'] : '';

        try{

            Db::startTrans();

            $roomId = Db::name('live')->insertGetId(self::$storage);

            Db::name('live_film')->where('id', $data['id'])->update(['is_live'=>1, 'room_id'=>$roomId]);

            Db::commit();
        }catch (\Exception $e){

            return $this->setError('请检查此用户是否已开播', 8000);
        }

        if (!$roomId) return $this->setError('创建房间错误', 8000);

        $zombie = config('app.zombie_config');

        $sum = mt_rand($zombie['min'], $zombie['max']);

        $redis = RedisClient::getInstance();

        for ($i=0; $i<$sum; $i++)
        {
            $robot_uid = $redis->spop('zombiePool'); //在池内随机拿一个机器人

            $redis->sAdd('BG_LIVE:'.$roomId.':robot', $robot_uid);
        }

        (new UserBehavior($anchor['user_id']))->live(['room_id'=>$roomId]);

        $close_time = $data['video_duration'] + $nowTime + $data['play_time'] + 30;

        (new Timer())->add(['url'=>API_URL.'/?service=LiveCallback.closeFilmLiveRoom', 'data'=>json_encode(['room_id'=>$roomId]), 'cycle'=>0, 'trigger_time'=>$close_time]);

        return $roomId;
    }

}