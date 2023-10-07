<?php


namespace app\api\service\live;

use app\api\service\LiveBase2;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;

class Pk extends LiveBase2
{
    protected static $livePrefix = 'BG_LIVE:';
    protected static $audienceKey = ':audience', $roomZombieKey = ':robot';

    protected $pk_data = [];

    public function initialize()
    {
        $coreSdk = new CoreSdk();

        $res = [];

        if (empty($this->pk_data)) return make_error('未添加好友');

        foreach ($this->pk_data as $key=>$user_id)
        {
            if (empty($user_id)) continue;

            if (!$this->checkLive($user_id)) continue;

            $user_info = $coreSdk->post('user/get_user', ['user_id'=>$user_id]);

            if (empty($user_info)) continue;

            array_push($res, $user_info);
        }

        return empty($res) ? make_error('没有好友在直播中') : $res;
    }


    //好友
    public function setFollow($user_id)
    {
        $key = "follow:{$user_id}";

        $followList = $this->redis->zrevrange($key, 0, -1, true);

        if (!empty($followList))
        {
            $this->pk_data = $users_id = array_keys($followList);;
        }

        return $this;
    }



    protected function checkLive($user_id)
    {
        $res = Db::name('live')->where(['user_id'=>$user_id])->find();
        return empty($res) ? false : true;
    }


    public function getPkList($p)
    {
        $arr = [];

        $p = empty($p) ? 0 : ($p-1)*PAGE_LIMIT;

        $pk = Db::name('live_pk')->where(['status'=>0])->order('id desc')->limit($p, PAGE_LIMIT)->select();

        if (empty($pk)) return $arr;

        foreach ($pk as $val)
        {
            $active_info = Db::name('live')->where(['id'=>$val['active_room_id']])->field('cover_url, id as room_id,user_id,city,province,nickname')->find();

            if (empty($active_info)) continue;

            $target_info = Db::name('live')->where(['id'=>$val['target_room_id']])->field('cover_url, id as room_id,user_id,city,province, nickname')->find();

            if (empty($target_info)) continue;

            $energy = $this->_energyCal($val['active_income'], $val['target_income']);

            $active_info['jump'] = getJump('enter_room', ['room_id' => $val['active_room_id'], 'from' => 'hot_recommend']);
            $target_info['jump'] = getJump('enter_room', ['room_id' => $val['target_room_id'], 'from' => 'hot_recommend']);
            $active_info['level'] = Db::name('anchor')->where('user_id', $active_info['user_id'])->value('anchor_lv');
            $target_info['level'] = Db::name('anchor')->where('user_id', $target_info['user_id'])->value('anchor_lv');
            $active_info['audience'] = $this->getNumber($val['active_room_id']);
            $target_info['audience'] = $this->getNumber($val['target_room_id']);
            $active_info['city'] = empty($active_info['city']) ? (empty($active_info['province']) ? '未知' : $active_info['province']) : $active_info['city'];
            $target_info['city'] = empty($target_info['city']) ? (empty($active_info['province']) ? '未知' : $active_info['province']) : $target_info['city'];
            $tmp = [
                'energy' => $energy,
                'active_energy' => $val['active_income'],
                'target_energy' => $val['target_income'],
                'active_info' => $active_info,
                'target_info' => $target_info
            ];

            array_push($arr, $tmp);
        }

        return $arr;
    }

    public function getNumber($room_id)
    {
        $redis = new RedisClient();
        $realAudience = $redis->zcard(self::$livePrefix . $room_id . self::$audienceKey);
        $zombie = $redis->zcard(self::$livePrefix . $room_id . self::$roomZombieKey);
        $realAudience *= 2;
        $zombie *= 20;
        $sum = $realAudience + $zombie;
        $total = empty($sum) ? 480 : $sum;
        return $total;
    }

    public function getDefaultPkOption($user_id)
    {
        $pk_options = [
            'pk_default' => [
                'pk_topic' => ['含水唱歌', '才艺比拼', '我点你唱', '女神之争', '方言唱歌'],
                'ac_topic' => ['真心话', '大冒险', '不眨眼', '贴纸条', '画一字眉'],
                'pk_duration' => ['300', '600', '1200', '1800', '2700', '3600'],
            ],
            'user_defined' => [
                'pk_topic' => '未设置',
                'ac_topic' => '未设置',
                'pk_duration' => '300',
            ]
        ];

        $user_pk_params = $this->redis->hget('config:pk_option', $user_id);

        !empty($user_pk_params) && $pk_options['user_defined'] = array_merge($pk_options['user_defined'], json_decode($user_pk_params, true));

        return $pk_options;
    }

}