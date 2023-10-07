<?php


namespace app\api\service\live;


use app\api\service\LiveBase2;
use bxkj_common\CoreSdk;

class Guard extends LiveBase2
{
    protected static $guardKey = 'BG_GUARD:';
    protected static $guardAvatar = 'http://bx.com/public/avatar/guard_new.png';

    /**
     * 获取主播守护列表
     * @return array|string
     */
    public function getGuard($anchor_id)
    {
        $users = [];

        $guardTotals = $this->redis->zrevrange(static::$guardKey . $anchor_id, 0, -1, true);
        
        // $gift_ids = json_decode($this->redis->get('DengLevel:100544_100459'), true);
        // var_dump($gift_ids);die;
        
        if (empty($guardTotals)) return $users;

        $coreSdk = new CoreSdk();

        $users_id = array_keys($guardTotals);

        $lists = $coreSdk->getUsers($users_id);

        $now = time();

        foreach ($lists as $key => $val) {
            $users[] = [
                'nickname' => $val['nickname'],
                'avatar' => $val['avatar'],
                'sign' => $val['sign'],
                'user_id' => $val['user_id'],
                'level' => $val['level'],
                'vip_status' => $val['vip_expire'] < $now ? 0 : 1,
                'is_creation' => $val['is_creation'],
                'verified' => $val['verified'],
                'gender' => $val['gender'],
                'user_millet' => $this->redis->zscore("rank:contr:real:{$anchor_id}:history", $val['user_id']) ?: 0
            ];
        }

        return $users;
    }

    /**
     * 获取主播排名第一的守护者头像
     * @param $user_id int 主播
     * @return string
     */
    public function guardAvatar($user_id)
    {
        $default = ['guard_avatar' => self::$guardAvatar, 'guard_uid' => ''];

        $guardCount = $this->redis->zcard(self::$guardKey . $user_id); //当前主播的守护量

        if (!empty($guardCount)) {
            $this->redis->zremrangebyscore(self::$guardKey . $user_id, 1, time()); //移除过期的

            $top = $this->redis->zrevrange(self::$guardKey . $user_id, 0, 0); //获取第一个用户id

            if (!empty($top)) {
                $topUser = (new CoreSdk())->getUsers($top[0]);

                if (!empty($topUser[0])) {
                    if (!empty($topUser[0]['avatar'])) {
                        $default['guard_avatar'] = $topUser[0]['avatar'];

                        $default['guard_uid'] = $topUser[0]['user_id'];
                    }
                }
            }
        }

        return $default;
    }

    /**
     * 获取前三守护
     * @param $user_id
     * @return array
     * @throws \bxkj_module\exception\ApiException
     */
    public function getThreeAvatar($user_id)
    {
        $default = [];
        $guardCount = $this->redis->zcard(self::$guardKey . $user_id); //当前主播的守护量

        if (!empty($guardCount)) {
            $this->redis->zremrangebyscore(self::$guardKey . $user_id, 1, time()); //移除过期的
            $top = $this->redis->zrevrange(self::$guardKey . $user_id, 0, 2); //获取第一个用户id

            if (!empty($top)) {
               for ($i= 0; $i < 3; $i++) {
                   if (empty($top[$i])) break;
                   $topUser = (new CoreSdk())->getUsers($top[$i]);

                   if (!empty($topUser[0])) {
                       if (!empty($topUser[0]['avatar'])) {
                           $default[] = [
                               'guard_avatar' => $topUser[0]['avatar'] ? $topUser[0]['avatar'] : '',
                               'guard_uid' => $topUser[0]['user_id'] ? $topUser[0]['user_id'] : '',
                               'guard_show' => 1
                           ];
                       }
                   }
               }
            }
        }

        $res = [
            'guard' => $default,
            'ad_url' => H5_URL.'/live/activitySlider',
            'activity_url' => [
                'url' => H5_URL.'/live/LiveSlider',
                'position' => '5,80,65,100'
            ]
        ];

        return $res;
    }
}