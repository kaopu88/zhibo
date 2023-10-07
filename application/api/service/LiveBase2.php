<?php

namespace app\api\service;


use app\common\service\Service;
use bxkj_common\YunBo;
use bxkj_module\service\User;
use think\Db;

class LiveBase2 extends Service
{
    protected static $room_type = ['', '私密', '付费', '计费', 'VIP', '等级'];

    protected static $room_desc = ['', '私密', '付费', '计费', 'VIP', '导购', 'PK', '关注', '热门', '魅力前十'];

    protected static $room_mode = ['直播', '录播', '电影直播', '游戏直播', '语聊', '电台'];

    protected static $photo_frame = 'https://static.cnibx.cn/0a419ec8a8c7eba29a8af1e532eb5e8977a3d6cb.png';

    //带圆角
    protected static $photo_frame2 = 'https://static.cnibx.cn/d3c9aa0c7a14237ef6d1c8085cac928ec23c861a.png';

    //房间提示信息
    protected static $room_type_msg = [
        '私密{$mode}, 请输入密码!',
        '付费{$mode}, {$value}' . APP_BEAN_NAME . '/场,是否支付',
        '计费{$mode}, {$value}' . APP_BEAN_NAME . '/分钟,是否支付',
        '主播已开启VIP{$mode}, 开通VIP后即可进入',
        '{$mode}间等级限制, 需要{$value}级进入'
    ];

    //直播相关redisKey
    protected static $kickingKey = ':KICK'
    , $shutSpeakKey = ':SHUT'
    , $liveChannel = 'BG_NAV:LIVE:'
    , $livePayKey = ':PAY'
    , $livePassword = ':PWD'
    , $liveNotice = 'NOTICE', $voice_prefix = 'BG_VOICE:', $voice_num = 'voice_number:',
        $voice_postion_type = 'voice_postion_type:', $voice_speak = 'voice_speak:';

    protected static $room_user_identity = ['直播间管理员', '主播', '超级管理员'];

    protected static $default_address = '未知';

    protected $order = 'id desc'; //默认排序

    protected $p = 1;

    protected $hot_p = 1;

    const ANCHOR = 3, MANAGE = 2, SUPER = 4, USER = 1; //直播间角色

    //直播间角色权限
    protected $room_auth_list = [
        'room_manage_on' => 1,
        'room_report_on' => 1,
        'room_manage_item' => [
            'setting_manage' => 1,
            'cancel_manage' => 1,
            'kicking' => 1,
            'shutSpeak' => 1,
            'close_room' => 1,
            'stop_room' => 1,
        ],
    ];

    protected static $act_key = 'cache:sales_act', $pk_prefix = 'BG_PK:';//活动缓存键

    const FILM_MODE = 2, RECORD_MODE = 1, GAME_MODE = 3, VOICE_MODE = 4, RADIO_MODE = 5; //直播模式

    const NORMAL_TYPE = 0, PRIVATE_TYPE = 1, CHARGE_TYPE = 2, TIME_CHARGE_TYPE = 3, VIP_TYPE = 4, LEVEL_TYPE = 5; //直播类型

    protected $where = ['status' => 1];

    protected static $liveLocation = 'location:', $audience_delay_time = 5;

    //直播表存储数据
    protected $storage_data = [
        'user_id' => 0,
        'agent_id' => 0,
        'nickname' => '',
        'avatar' => '', //给一个默认头像
        'create_time' => 0,
        'title' => '',
        'province' => '',
        'city' => '',
        'district' => '',
        'cover_url' => '',
        'stream' => '',
        'pull' => '',
        'type' => 0,
        'type_val' => '0',
        'room_channel' => '0',
        'room_model' => 0,
        'status' => 0,
        'lng' => 0,
        'lat' => 0,
        'voice_number' => 0,
        'background_url' => '',
        'content' => ''
    ];

    //主播端数据
    protected $anchor_data = [
        'user_id' => '',
        'nickname' => '',
        'avatar' => '',
        'gender' => 0,
        'level' => '0',
        'total_millet' => '0',
        'act_balance' => '0',
        'game_coin' => '0',
        'is_follow' => 0,
        'live_room_name' => '',
        'rank' => 0,
    ];

    //用户端数据
    protected $user_data = [
        'game_coin' => '0',
        'act_balance' => '0',
    ];

    //活动数据
    protected static $activity_data = [
        //直播间守护
        'guard' => [
            'guard_show' => 1,
            'guard_avatar' => '',
            'guard_uid' => 0,
        ],

        //直播间广告地址
        'ad_url' => H5_URL . '/live/activitySlider',
        //直播间活动地址
        'activity_url' => [
            'url' => H5_URL . '/live/LiveSlider',
            'position' => '5,80,65,100', //右，下，宽，高
        ]
    ];

    //直播间数据
    protected $room_data = [
        'room_id' => 0,
        'room_model' => 0,
        'type' => 0,
        'type_val' => '0',
        'room_channel' => 0,
        'room_second_channel' => 0,
        'chat_server' => '',
        'chat_server_port' => '5555',
        'game_server' => '',
        'game_server_port' => '5252',
        'barrage' => '500',
        'cover_url' => '',
        'push' => '',
        'stream' => '',
        'title' => '',
        'audience' => 0,
        'pull' => '',
        'province' => '',
        'city' => '',
        'district' => '',
        'service_platform' => 'qiniu',
        'pk_data' => [],
        'pk_energy' => [],
        'is_pk' => 0,
        'pk_time' => '0',
        'smoke_data' => ['user_id' => '', 'time' => ''],
        'voice_number' => 0,
        'background_url' => '',
        'content' => ''
    ];

    //直播电影数据
    protected $film_data = [
        'start_time' => '',
        'start_time_stamp' => '0',
        'ad' => [],
        'title' => '',
        'duration' => 0,
        'video_rate' => '',
        'notice' => '',
    ];

    //
    protected $voice_data = [
        'host_user' => ['avatar' => '', 'gender' => 0, 'total_score' => 0],
        //'postion' => ['user_id' => '', 'avatar' => '', 'gender' => '', 'is_speak' => 0, 'is_anchor_speak' => 0, 'total_score' => 0],
        'postion' => []
    ];


    //获取直播间
    public function getRoomOne($room_id)
    {
        $this->where['id'] = $room_id;

        $room = Db::name('live')->field('*, id room_id')->where($this->where)->find();

        return $room;
    }

    //获取直播间
    public function getRoomByUserId($user_id)
    {
        $this->where['user_id'] = $user_id;

        $room = Db::name('live')->field('*, id room_id')->where($this->where)->find();

        return $room;
    }

    protected function checkPk($user_id)
    {
        $is_active = $this->redis->exists(self::$pk_prefix . 'pking:active:' . $user_id);

        $is_target = $this->redis->exists(self::$pk_prefix . 'pking:target:' . $user_id);

        if (empty($is_active) && empty($is_target)) {
            $is_pk = false;

            $flag = '';
        } else {
            $is_pk = true;

            $flag = $is_active ? 'active' : 'target';
        }

        return ['is_pk' => $is_pk, 'flag' => $flag];
    }

    protected function _energyCal($active, $target)
    {
        $energy = round(($active + 50) / ($active + $target + 100), 2);

        if ($energy < 0.05 || $energy > 0.95) {
            $energy = $energy < 0.05 ? 0.05 : 0.95;
        }

        return $energy;
    }

    //初始化主播数据
    protected function initializeAnchorData($data = [])
    {
        if (empty($data)) return;

        foreach ($this->anchor_data as $key => &$val) {
            isset($data[$key]) && !empty($data[$key]) && $val = $data[$key];
        }
    }

    //初始化用户数据
    protected function initializeUserData($data = [])
    {
        if (empty($data)) return;

        foreach ($this->user_data as $key => &$val) {
            isset($data[$key]) && !empty($data[$key]) && $val = $data[$key];
        }
    }

    //初始化房间数据
    protected function initializeRoomData($data = [])
    {
        if (empty($data)) return;

        foreach ($this->room_data as $key => &$val) {
            isset($data[$key]) && !empty($data[$key]) && $val = $data[$key];

            if (is_array($this->room_data[$key]) && empty($val)) $this->room_data[$key] = (object)[];
        }
    }

    //初始化直播存储数据
    protected function initializeStorageData($data = [])
    {
        if (empty($data)) return;

        foreach ($this->storage_data as $key => &$val) {
            isset($data[$key]) && !empty($data[$key]) && $val = $data[$key];
        }
    }

    //获取pk相关数据
    protected function getPkData($user_id, $room_id, $flag)
    {
        $where = $flag == 'active' ? "p.active_id={$user_id} and p.active_room_id={$room_id}" : "p.target_id={$user_id} and p.target_room_id={$room_id}";

        $on_uid = $flag == 'active' ? 'p.target_id' : 'p.active_id';

        $prefix = config('database.prefix');

        $sql = sprintf("SELECT p.*, l.pull FROM {$prefix}live_pk p INNER JOIN {$prefix}live l ON p.status=0 and l.user_id=%s WHERE %s ORDER BY p.id DESC LIMIT 1", $on_uid, $where);

        $res = Db::query($sql);

        $res = $res ? $res[0] : [];

        if (empty($res)) return [];

        $pk_time = ($res['pk_duration'] + 180) - (time() - $res['pk_start_time']);

        if ($pk_time < self::$audience_delay_time) return [];

        $energy = $this->_energyCal($res['active_income'], $res['target_income']);

        $pk_user_id = $flag == 'active' ? $res['target_id'] : $res['active_id'];
        $pk_room_id = $flag == 'active' ? $res['target_room_id'] : $res['active_room_id'];

        return [
            'pk_id' => $res['id'],
            'user_id' => $pk_user_id,
            'pull' => $res['pull'],
            'energy' => $energy,
            'pk_room_id' => $pk_room_id,
            'active_energy' => $res['active_income'],
            'target_energy' => $res['target_income'],
            'pk_time' => $pk_time,
            'pk_type' => $res['pk_type']
        ];
    }

    //初始化电影直播数据
    protected function initializeFilmData($room_id, $liveTime)
    {
        $prefix = config('database.prefix');
        $now_time = time();
        $timeline = Db::name('live_film_timeline')->where('room_id=' . $room_id)->find();
        $start_time = 0;
        $this->film_data['start_time'] = '';
        $this->film_data['ad'] = [];
        $this->film_data['ad_duration'] = '0';
        $this->film_data['ad_countdown'] = '0';
        $this->film_data['title'] = '未知';
        $this->film_data['duration'] = '0';
        $this->film_data['video_rate'] = '1.78';
        $this->film_data['notice'] = '本场电影已结束，谢谢观看！';
        $this->film_data['offset'] = '0';
        if ($timeline) {
            $key = "counted_users:live_film:{$timeline['id']}";
            if (!$this->redis->sisMember($key, USERID)) {
                Db::query("update {$prefix}live_film_timeline set box_office=box_office+1 where id=?", [$timeline['id']]);
                $this->redis->sAdd($key, USERID);
                $this->redis->expire($key, 86400 * 7);
            }
            $film_info = Db::name('live_film')->where('id=' . $timeline['film_id'])->find();
            $filmStartTime = $timeline['start_time'] + $timeline['ad_duration'];//正片开始播放时间点
            $start_time = $now_time - $filmStartTime + (int)$timeline['offset'];//距离正片播放还有多长时间 负数表示还差多长时间 正数表示已播出多长时间
            $this->film_data['start_time'] = date('H:i', $filmStartTime);
            $adIds = $timeline['ad_ids'] ? explode(',', $timeline['ad_ids']) : [];
            $this->film_data['ad'] = $this->getLiveFilmAd($adIds);
            $this->film_data['ad_duration'] = (string)$timeline['ad_duration'];
            $tmp = $now_time - $timeline['start_time'];
            $this->film_data['ad_countdown'] = (string)($tmp > $timeline['ad_duration'] ? 0 : ($timeline['ad_duration'] - $tmp));
            $this->film_data['title'] = $film_info['video_title'];
            $this->film_data['duration'] = (string)$film_info['video_duration'];
            $this->film_data['video_rate'] = $film_info['video_rate'] | '';
            $this->film_data['offset'] = (string)$timeline['offset'];//播出的偏移量
            $this->room_data['pull'] = '';
            if (!empty($film_info['video_url'])) {
                $this->room_data['pull'] = $film_info['video_url'];
            } else if (!empty($film_info['third_url'])) {
                $third_info = YunBo::getVideo($film_info['third_url']);
                if ($third_info && !is_error($third_info)) {
                    $this->film_data['third'] = $third_info;
                    if ($third_info['play'] == 'mp4' || $third_info['play'] == 'hls') {
                        $this->room_data['pull'] = $third_info['src'];
                    }
                }
            }
        }
        $this->film_data['start_time_stamp'] = (string)$start_time;
        $tmp = $now_time + ($this->film_data['duration'] - $start_time);
        $nextList = Db::query("select * from {$prefix}live_film_timeline where start_time>" . $tmp . ' and status=\'0\' order by start_time asc,id asc limit 1');
        if ($nextList) {
            $next = $nextList[0];
            $anchor = Db::name('user')->field('user_id,nickname,avatar')->where(['user_id' => $next['anchor_uid']])->find();
            $timeStr = is_in_today($next['start_time']) ? date('H:i', $next['start_time']) : date('m-d H:i', $next['start_time']);
            $this->film_data['notice'] = "本场结束！\n精彩预告：{$anchor['nickname']}主播即将在{$timeStr}播放《{$next['live_title']}》敬请观看~";
        }
    }


    //获取电影直播间广告
    protected function getLiveFilmAd($ids)
    {
        if (empty($ids)) return [];
        $idsWhere = implode(',', $ids);
        $advInfos = Db::name('live_film_ad')->field('id,video_url ad_video,ad_link,ad_title,video_duration,video_rate,video_cover')->where('id in (' . $idsWhere . ')')->limit(count($ids))->select();
        if (empty($advInfos)) return [];

        return $advInfos;
    }


    //验证是否已输入过密码
    protected function verifyPassword($room_id)
    {
        $res = $this->redis->zscore(self::$livePrefix . $room_id . self::$livePassword, USERID);

        return !empty($res);
    }


    //验证等级
    protected function verifyLevel($user_id, $level)
    {
        $userModel = new User();

        $user = $userModel->getUser($user_id);

        return $user['level'] >= $level;
    }


    //验证vip
    protected function verifyVip($user_id)
    {
        $userModel = new User();

        $user = $userModel->getUser($user_id);

        return $user['vip_status'] == 1 && $user['vip_expire'] > time();
    }


    //验证付费记录
    protected function verifyPay($room_id)
    {
        $res = $this->redis->zscore(self::$livePrefix . $room_id . self::$livePayKey, USERID);

        return !empty($res);
    }


}