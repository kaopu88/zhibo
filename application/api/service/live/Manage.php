<?php


namespace app\api\service\live;


use app\api\service\Follow;
use app\common\service\Manage as ManageCommon;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use app\common\service\User;
use think\Db;

class Manage extends ManageCommon
{

    protected $room_auth_list = [
        'report_on' => 1,
        'setting_manage' => 1,
        'cancel_manage' => 1,
        'kicking' => 1,
        'shutSpeak' => 1,
        'close_room' => 1,
        'stop_room' => 1,
        'pull_black' => 1,
        'private_letter' => 1,
        'send_private_letter' => 1
    ];

    protected static $fields = ['user_id', 'isvirtual', 'avatar', 'gender', 'level', 'city_name', 'nickname', 'is_follow', 'vip_status', 'is_creation', 'verified', 'sign', 'total_millet', 'isvirtual_millet', 'exp', 'is_anchor'];

    //房间角色信息
    public function userCard($room_id, $user_id)
    {
        //默认同为用户
        $myIdentity = $hisIdentity = self::USER;

        $this->where['id'] = $room_id;

        $room = Db::name('live')->where($this->where)->field('user_id, avatar, nickname, city address')->find();

        if (empty($room)) return make_error('主播已关播');

        $coreSdk = new CoreSdk();

        $user_info = $coreSdk->post('user/get_user', ['user_id' => $user_id, 'self_uid' => USERID]);

        if (empty($user_info)) return make_error('未查到此用户');

        if ($user_id == $room['user_id'])
        {
            $hisIdentity = self::ANCHOR; //是主播

            if (empty($room['address']))
            {
                unset(self::$fields['user_id'], self::$fields['avatar'], self::$fields['nickname']);
            } else {
                unset(self::$fields['user_id'], self::$fields['avatar'], self::$fields['nickname'], self::$fields['city_name']);
            }
        }

        $user_info = copy_array($user_info, self::$fields);

        if (empty($room['address']) || $user_id != $room['user_id']) $user_info['address'] = empty($user_info['city_name']) ? self::$default_address : $user_info['city_name'];

        //最终的用户信息
        $info = array_merge($room, $user_info);

        $this->getLevel($info, $hisIdentity);

        $info['control_status'] = $user_info['isvirtual'] == 0 ? $this->verifyManage($room['user_id'], $user_id) : 0; //直播间管理员状态

        $info['guard_status'] = $this->verifyGuard($room['user_id'], $user_id); // 主播守护状态

        //多少关注数
        $info['followCount'] = (new Follow())->followCount($user_id);

        //多少粉丝数
        $info['fansCount'] = (new Follow())->fansCount($user_id);

        //送出数
        $total = Db::name('bean')->where(['user_id'=>$user_id])->field('pay_total')->find();

        $info['sendCount'] = !empty($total['pay_total']) ? $total['pay_total'] : '0';

        $info['total_millet'] += $info['isvirtual_millet'];

        unset($info['isvirtual_millet']);

        $this->formatData($info, ['total_millet', 'sendCount']);

        if ($user_id == USERID)
        {
            $this->room_auth_list = array_map(function ($i) {
                return --$i;
            }, $this->room_auth_list);

            $info['is_follow'] = 0;
        }
        else{

            $this->verifyManage($room['user_id'], USERID) && $myIdentity = self::MANAGE;

            USERID == $room['user_id'] && $myIdentity = self::ANCHOR;

            $this->validateSuper(USERID) && $myIdentity = self::SUPER;

            $this->verifyManage($room['user_id'], $user_id) && $hisIdentity = self::MANAGE;

            $this->validateSuper($user_id) && $hisIdentity = self::SUPER;

            //平等身份或低于对方身份
            if ($myIdentity == $hisIdentity || $myIdentity < $hisIdentity)
            {
                $this->room_auth_list = array_map(function ($i) {
                    return --$i;
                }, $this->room_auth_list);
                $this->room_auth_list['pull_black'] = 1;
                $this->room_auth_list['report_on'] = 1;
                $user_id == $room['user_id'] && $this->room_auth_list['private_letter'] = 1;
                $this->room_auth_list['send_private_letter'] = ($this->room_auth_list['private_letter'] && empty($this->redis->zScore('blacklist:' . USERID, $user_id))) ? 1 : 0;
            }
            else if ($myIdentity > $hisIdentity) {

                switch (true) {
                    case $myIdentity == self::ANCHOR && $hisIdentity == self::USER ://主播对普通者
                        $allow = ['setting_manage', 'kicking', 'shutSpeak', 'pull_black', 'report_on'];
                        break;

                    case $myIdentity == self::ANCHOR && $hisIdentity == self::MANAGE ://主播对管理员
                        $allow = ['cancel_manage', 'kicking', 'shutSpeak', 'pull_black', 'report_on'];
                        break;

                    case $myIdentity == self::MANAGE && $hisIdentity == self::USER ://管理员对普通者
                        $allow = ['shutSpeak', 'kicking', 'pull_black', 'report_on'];
                        break;

                    case $myIdentity == self::SUPER && $hisIdentity == self::USER ://超管对普通者
                        $allow = ['setting_manage', 'kicking', 'shutSpeak', 'stop_room', 'pull_black', 'report_on'];
                        break;

                    case $myIdentity == self::SUPER && $hisIdentity == self::ANCHOR ://超管对主播
                        $allow = ['close_room', 'stop_room', 'pull_black', 'report_on'];
                        break;

                    case $myIdentity == self::SUPER && $hisIdentity == self::MANAGE ://超管对管理
                        $allow = ['cancel_manage', 'kicking', 'shutSpeak', 'stop_room', 'pull_black', 'report_on'];
                        break;
                    default:
                        $allow = ['setting_manage', 'cancel_manage', 'kicking', 'shutSpeak', 'close_room', 'stop_room', 'pull_black', 'report_on'];
                        break;
                }

                array_walk($this->room_auth_list, function (&$i, $k) use ($allow) {
                    if (!in_array($k, $allow)) --$i;
                });
            }
        }

        //是否被拉黑
        if ($this->room_auth_list['pull_black'] == 1 && $this->redis->zScore('blacklist:' . USERID, $user_id)) $this->room_auth_list['pull_black'] = 0;

        if($this->room_auth_list['shutSpeak'] == 1)
        {
            //是否被禁言判断
            $isInList = $this->redis->hget(self::$livePrefix.$room_id.':SHUT', $user_id);

            if ($isInList && time() <= $isInList) $this->room_auth_list['shutSpeak'] = 0;
        }

        if($this->room_auth_list['kicking'] == 1 && $this->redis->sismember(self::$livePrefix.$room_id.':KICK', $user_id)) $this->room_auth_list['kicking'] = 0;

        $impression = [];

        if ($info['is_anchor'])
        {
            $impression = Db::name('user_impression')->alias('uim')
                ->join('impression im', 'uim.impression_id=im.id AND im.`status`=1')
                ->field('im.`name`, im.color, count(uim.id) num')
                ->where('uim.anchor_uid', $user_id)
                ->group('uim.impression_id')
                ->order('num desc')
                ->limit(3)
                ->select();
        }

        $info['is_anchor'] = (int)$info['is_anchor'];

        return ['user' => $info, 'permissions' => $this->room_auth_list, 'impression' => $impression];
    }


    private function getLevel(&$user_info, $identity)
    {
        $UserService = new User();

        if ($user_info['is_anchor'])
        {
            //获取主播等级和进度
            $anchor_level = Db::name('anchor')->where('user_id', $user_info['user_id'])->value('anchor_lv');
            if (empty($anchor_level)) $anchor_level = 1;
            $user_info['anchor_level'] = $anchor_level;
            $user_info['anchor_level_progress'] = $UserService->getAnchorLevelProcess($user_info);
        }

        //处理用户等级进度
        $user_info['level_progress'] = $UserService->getUserLevelProcess($user_info);
    }


    //踢人
    public function kicking($room_id, $user_id)
    {
        $res = $this->liveManageAuthCheck($room_id, $user_id);

        if (is_error($res)) return $res;

        $this->redis->sadd(self::$livePrefix . $room_id . self::$kickingKey, $user_id);

        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.live_get_out', ['user_id' => $user_id,'room_name' => $room_id]);

        return true;
    }


    //禁言
    public function shutSpeak($room_id, $user_id)
    {
        $res = $this->liveManageAuthCheck($room_id, $user_id);

        if (is_error($res)) return $res;

        $expireTime = config('app.live_setting.shutspeak_expire_time');

        $this->redis->hset(self::$livePrefix . $room_id . self::$shutSpeakKey, $user_id, time() + $expireTime);

        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.live_shield', ['user_id' => $user_id,'room_name' => $room_id]);

        return true;
    }


    //直播管理员
    public function liveManageSwitch($anchor_id, $user_id)
    {
        $res = Db::name('live_manage')->where(['anchor_uid' => $anchor_id])->select();

        if (!empty($res)) $allManage = array_column($res, 'manage_uid');

        if (isset($allManage) && in_array($user_id, $allManage)) {
            //取消
            Db::name('live_manage')->where(['anchor_uid' => $anchor_id, 'manage_uid' => $user_id])->delete();

            $this->redis->srem('liveManage:' . $anchor_id, $user_id);

            $res = ['msg' => '取消管理成功', 'status' => 0];
        } else {
            $count = config('app.live_setting.live_manage_sum');

            if (count($res) > $count - 1) return make_error('当前管理已满,不能添加新的管理');

            //添加
            Db::name('live_manage')->insert(['anchor_uid' => $anchor_id, 'manage_uid' => $user_id, 'create_time' => time()]);

            $this->redis->sadd('liveManage:' . $anchor_id, $user_id);

            $res = ['msg' => '设置管理成功', 'status' => 1];
        }

        return $res;

    }


    //获取直播间管理员列表
    public function getManageList()
    {
        $list = Db::name('live_manage')->where(['anchor_uid' => USERID])->select();

        if (empty($list)) return [];

        $coreSdk = new CoreSdk();

        $manageIds = array_column($list, 'manage_uid');

        $lists = $coreSdk->getUsers($manageIds);

        $res = [];

        $now = time();

        foreach ($lists as $key => $value) {
            $res[$key] = [
                'user_id' => $value['user_id'],
                'avatar' => $value['avatar'],
                'nickname' => $value['nickname'],
                'gender' => $value['gender'],
                'level' => $value['level'],
                'sign' => $value['sign'],
                'vip_status' => $value['vip_expire'] < $now ? 0 : 1,
                'verified' => $value['verified'],
                'is_creation' => $value['is_creation'],
                'jump' => getJump('personal', ['user_id' => $value['user_id']])
            ];
        }

        return $res;
    }
}