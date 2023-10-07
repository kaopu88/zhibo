<?php

namespace app\core\controller;

use app\core\service\Socket;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;


/**
 * 活动服务类
 * Class Activity
 * @package app\core\controller
 */

class Activity extends Controller
{

    private static $day_timestamp = 86400;

    private static $hof_limit = 10000000;

    private static $redis_key = 'activity:', $act_name = 'gift_scramble:', $anchor_key = 'anchor:', $love_act_name = 'love_raise:';


    /**
     * 礼物争夺战-定时任务
     * @return \think\response\Json
     */
    public function giftScrambleTimer()
    {
        //获取本周活动配置
        @list($week_start, $week_end) = lastWeek();

        $tracks = Db::name('activity_gift_scramble')
            ->where(['start_time' => $week_start, 'end_time' => $week_end-self::$day_timestamp])
            ->field('id, period, gift_id, name')
            ->select();

        if (empty($tracks)) return json_success([]);

        $redis = RedisClient::getInstance();

        $week_rank = [];

        foreach ($tracks as $value)
        {
            //获取各赛道下的前三名
            $top = $redis->zrevrange(self::$redis_key.self::$act_name.self::$anchor_key.$value['id'], 0, 0, true);

            if (empty($top)) continue;

            foreach ($top as $user_id => $score)
            {
                $tmp = [
                    'user_id' => $user_id,
                    'points' => $score,
                    'track_id' => $value['id'],
                    'period' => $value['period'],
                    'gift_id' => $value['gift_id'],
                    'name' => $value['name'],
                ];

                array_push($week_rank, $tmp);
            }
        }

        if (empty($week_rank)) return json_success([]);

        //排序周排名
        usort($week_rank, function ($a, $b) {

            if ($a['points'] == $b['points']) return 0;

            return ($a['points'] > $b['points']) ? -1 : 1;
        });

        $week_rank = array_slice($week_rank, 0, 3);

        $this->writeHofByGiftScramble($week_rank);

        $this->writeNamingByGiftScramble($week_rank);

        return json_success([]);
    }


    /**
     * 礼物争夺战-处理礼物冠名定时任务
     * @return \think\response\Json
     */
    public function handlerNaming()
    {
        //获取本周的需要冠名的用户
        $week = date('W');

        $gift_scramble_naming = Db::name('activity_gift_scramble_naming')->where('week', $week-1)->order('points desc')->select();

        if (empty($gift_scramble_naming)) return json_success([]);

        $redis = RedisClient::getInstance();

        $CoreSdk = new CoreSdk();

        foreach ($gift_scramble_naming as $value)
        {
            //获取冠名权
            $naming_user_id = $redis->get('cache:gift_name:'.$value['gift_id']);

            //如果为空则并且已冠名则说明到期去掉冠名权
            if (empty($naming_user_id) && $value['is_naming'] == 1)
            {
                Db::name('gift')->where('id', $value['gift_id'])->update(['name' => $value['gift_name']]);
            }
            //如果为空则并且未冠处理冠名
            else if (empty($naming_user_id) && $value['is_naming'] == 0){

                $redis->set('cache:gift_name:'.$value['gift_id'], $value['user_id']);

                //冠名时长
                $redis->expire('cache:gift_name:'.$value['gift_id'], $value['naming_duration']);

                $users = $CoreSdk->getUser($value['user_id']);

                //完成冠名
                Db::name('gift')->where('id', $value['gift_id'])->update(['name' => $users['nickname'].'的'.$value['gift_name']]);

                Db::name('activity_gift_scramble_naming')->where('id', $value['id'])->update(['is_naming' => 1]);
            }
        }

        $redis->del('BG_GIFT:gift');

        return json_success([]);
    }


    /**
     * 礼物争夺战-写入名人堂
     * @return bool
     */
    protected function writeHofByGiftScramble($data)
    {
        if ($data[0]['points'] < self::$hof_limit) return true;

        $in_hof_info = Db::name('activity_gift_scramble_hof')->where('user_id', $data[0]['user_id'])->find();

        //加入名人堂
        if (empty($in_hof_info))
        {
            Db::name('activity_gift_scramble_hof')->insert([
                'points' => $data[0]['points'],
                'user_id' => $data[0]['user_id'],
                'num' => 1,
                'update_time' => time(),
                'create_time' => time(),
            ]);
        }
        else{
            Db::name('activity_gift_scramble_hof')->where('user_id', $data[0]['user_id'])->update([
                'points' => $data[0]['points']+$in_hof_info['points'],
                'num' => $in_hof_info['num']+1,
                'update_time' => time(),
            ]);
        }
    }


    /**
     * 礼物争夺战-写入冠名权
     * @return \think\response\Json
     */
    protected function writeNamingByGiftScramble($data)
    {
        $week = date('W');

        $week_count = Db::name('activity_gift_scramble_naming')->where('week', $week-1)->count();

        if (!empty($week_count)) return json_success([]);

        if (empty($data)) return json_success([]);

        $naming_duration = [604800, 259200, 86400];

        $naming = [];

        //处理前三名
        foreach ($data as $key => $value)
        {
            //处理每个主播所能冠名的时长
            $tmp = [
                'week' => $week-1,
                'period' => $value['period'],
                'user_id' => $value['user_id'],
                'points' => $value['points'],
                'gift_id' => $value['gift_id'],
                'create_time' => time(),
                'gift_name' => $value['name'],
                'naming_duration' => $naming_duration[$key],
                'is_naming' => 0,
            ];

            array_push($naming, $tmp);
        }

        // 入库当前所获冠名的主播信息
        Db::name('activity_gift_scramble_naming')->insertAll($naming);
    }



    /**
     * 爱情表白罐-表白罐每日衰减定时任务
     *
     */
    public function loveRaiseDecay()
    {
        $redis = RedisClient::getInstance();

        $day = date('Ymd');

        //所有建立供养关系的表白罐
        $all_container = Db::name('activity_love_container')->where('status', 2)->select();

        if (empty($all_container)) return json_success([]);

        //处理每个表白罐
        foreach ($all_container as $container)
        {
            $support_key = self::$redis_key.self::$love_act_name.$container['user_id'];

            //当前主播下的能量
            $energy = $redis->zscore($support_key, $container['id']);

            if ($energy < 10)
            {
                if ($this->recoveryLoveContainer($container['id']))
                {
                    $redis->zrem($support_key, $container['id']);
                    continue;
                }
            }

            //查看今日供养者是否送了520
            $lock_key = self::$redis_key.self::$love_act_name.$day;

            $is_lock = $redis->zscore($lock_key, $container['id']);

            if (!empty($is_lock)) continue;

            //未送进行衰减
            $decay_value = round($energy*0.1);

            if (($energy-$decay_value) < 10)
            {
                if ($this->recoveryLoveContainer($container['id']))
                {
                    $redis->zrem($lock_key, $container['id']);
                    continue;
                }
            }

            $redis->zincrby($support_key, -$decay_value, $container['id']);
        }

        return json_success([], 'ok');
    }

    //爱情表白罐-释放表白罐
    protected function recoveryLoveContainer($pot_id)
    {
        Db::name('activity_love_container')->where('id', $pot_id)->update([
            'status' => 0,
            'provider_id' => 0,
        ]);

        Db::name('activity_love_container_reply')->where('container_id', $pot_id)->update([
            'handle_status' => 3,
            'handle_time' => time(),
        ]);

        return true;
    }


    /**
     * 七夕定时推送
     *
     * @return \think\response\Json
     */
    public function pushQixiBroadCast()
    {
        //获取相关配置
        $config = Db::name('activity')->where([
                ['mark', 'eq', 'love_qixi'],
                ['status', 'eq', 1]
            ])
            ->find();

        if (empty($config)) return json_success([], 'ok');

        $now = time();

        if ($now < $config['start_time'] || $now > $config['end_time']) return json_success([], 'ok');

        $rule = json_decode($config['rule'], true);

        //主播榜单Key
        $anchor_rank_key = $rule['redis_key'].$rule['current_act'].$rule['anchor_key'];

        $redis = RedisClient::getInstance();

        $tops = $redis->zrevrange($anchor_rank_key, 0, 0, true);

        if (empty($tops)) return json_success([], 'ok');

        $uids = array_keys($tops);

        $CoreSdk = new CoreSdk();

        $users = $CoreSdk->getUser($uids[0]);

        $message = [
            'mod' => 'Live',
            'act' => 'pushBroadCast',
            'args' => ['content' => '情定七夕，爱在'.APP_PREFIX_NAME.' 恭喜主播“'.$users['nickname'].'”以 '.$tops[$uids[0]].' 积分暂居榜首，继续加油吧！'],
            'web' => 1
        ];

        $socket = new Socket();

        $res = $socket->connectSocket($message);

        if (!$res) return json_error($socket->getError());

        return json_success([], 'ok');
    }

}