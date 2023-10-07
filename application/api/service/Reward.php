<?php

namespace app\api\service;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\Task;
use bxkj_module\service\User;
use think\Db;

class Reward extends Video
{
    protected static $redis_key = 'video:reward:';

    protected static $gift_category = 1;

    //所有物品icon地址
    protected static $reward = [
        [
            'id' => '1',
            'icon' => 'https://static.cnibx.cn/income.png',
            'name' => APP_MILLET_NAME,
            'type' => 'income',
        ],
        [
            'id' => '2',
            'icon' => 'https://static.cnibx.cn/exp.png',
            'name' => '经验值',
            'type' => 'exp',
        ],
        [
            'id' => '3',
            'icon' => 'https://static.cnibx.cn/coin.png',
            'name' => APP_BEAN_NAME,
            'type' => 'coin',
        ]
    ];

    /**
     * 当前视频是否开启奖励
     *
     * @param array $video_info
     * @return int
     */
    public function isOpenReward()
    {
        /**
         * 思路：
         * 　每天白天出现的机率比较少
         * 　每天晚上１８点到２３点机率大、品种多
         *   每天定时出现一个大的boss级别的礼物
         *  多刷礼物机率越多
         *
         */

        if (RUNTIME_ENVIROMENT == 'testing') return 1;

        $now = time();

        //历史奖励次数
        $reward_count = $this->redis->get(self::$redis_key . 'user:' . USERID . ':count');

        //一次没打开过
        if (empty($reward_count)) return 1;

        //上次获取的时间
        $time = $this->redis->get(self::$redis_key . 'user:' . USERID . ':open_time');

        $diff = $now - $time;

        switch ($reward_count) {
            case $reward_count < 5 :
                //白天
                $probability = mt_rand(300, 600);//5分钟~10分钟
                break;

            case $reward_count < 10 :
                //白天
                $probability = mt_rand(900, 1800);//15分钟~30分钟
                break;

            case $reward_count < 60 :
                //高峰期
                $probability = mt_rand(2700, 3600);//45分钟~1小时
                break;

            default:
                $probability = mt_rand(4500, 7200);//1小时15分~2小时
                break;
        }

        return (int)($diff > $probability);
    }

    /**
     * 打赏排行
     *
     * @param $user_id
     * @param $offset
     * @param $lenght
     * @return array
     */
    public function rank($video_id, $offset, $length)
    {
        if ($offset >= 50) return [];

        $rank = $offset + 1;

        $length = $length + $offset;

        $data = [];

        $rankList = $this->redis->zrevrange(self::$redis_key . 'rank:' . $video_id, $offset, $length - 1, true);

        if (empty($rankList)) return [];

        $user = new User();

        $Follow = new Follow();

        foreach ($rankList as $user_id => $user_score) {

            $user_info = $user->getUser($user_id);

            if (empty($user_info)) continue;

            $users = [
                'rank' => $rank,
                'user_id' => $user_id,
                'avatar' => $user_info['avatar'],
                'nickname' => $user_info['nickname'],
                'is_follow' => (int)$Follow->is_follow(USERID, $user_id),
                'give_coin' => $this->formatData($user_score),
            ];

            $rank++;

            array_push($data, $users);
        }

        return $data;
    }

    /**
     * 打赏礼物列表
     * @param $user_id //视频发布者id
     * @param $offset
     * @param $length
     */
    public function rewardList($video_id)
    {
        $reward_info = Db::name('gift')
            ->where(['status' => '1', 'cid' => self::$gift_category])
            ->field('id, name, picture_url icon, price, discount, privileges')
            ->order('sort asc')
            ->select();

        if (empty($reward_info)) return ['coin' => '', 'rank' => [], 'gift' => []];

        //当前用户所获取的未使用的奖励
        $user_reward_info = Db::name('video_user_reward')
            ->where(['user_id' => USERID, 'type' => 'gift'])
            ->field('gift_id, sum(num) reward_num')
            ->group('gift_id')
            ->select();

        if (!empty($user_reward_info)) $rewards = array_column($user_reward_info, 'reward_num', 'gift_id');

        foreach ($reward_info as &$value) {
            $value['num'] = isset($rewards) && array_key_exists($value['id'], $rewards) ? $rewards[$value['id']] : 0;

            $value['is_msg'] = (int)($value['privileges'] == 'leave_msg');

            $value['price'] *= $value['discount'];

            unset($value['discount'], $value['privileges']);
        }

        //用户余额信息
        $bean = !empty(USERID) ? Db::name('bean')->where(['user_id' => USERID])->value('bean') : 0;

        //榜单信息
        $rank = $this->rank($video_id, 0, 3);

        $data = [
            'coin' => (int)$bean,
            'rank' => $rank,
            'gift' => $reward_info,
        ];

        return $data;
    }

    /**
     * 开启打赏奖励礼物
     *
     */
    public function openRewardGift($video_id, $digg_num = 0)
    {
        $now = time();

        $user_update_data = $reward_data = $prizes = $reward_all = [];

        $where = [
            ['status', '=', '1'],
            ['cid', '=', self::$gift_category],
            ['price', '<', 1000],
        ];


        //先获取视频礼物资源
        $reward_info = Db::name('gift')
            ->where($where)
            ->field('id, name, picture_url as icon')
            ->order('sort asc')
            ->select();

        //视频礼物与设定奖品组合
        if (!empty($reward_info)) self::$reward = array_merge($reward_info, self::$reward);

        //整理获得所有奖励品种
        foreach (self::$reward as $rewards) {
            if (!array_key_exists('type', $rewards)) {
                $rewards['type'] = 'gift';
                $redis_key = self::$redis_key . 'user:' . USERID . ':gift_' . $rewards['id'];
                //每个品种用户历史获取总数(也可以看作是本次处理的概率)
                $gift_probability[$rewards['id']] = (int)$this->redis->get($redis_key);
            } else {
                $redis_key = self::$redis_key . 'user:' . USERID . ':' . $rewards['type'];
                //每个品种用户历史获取总数(也可以看作是本次处理的概率)
                $virtual_probability[$rewards['id']] = (int)$this->redis->get($redis_key);
            }

            $reward_all[$rewards['id']] = $rewards;
        }

        //概率计算：(digg_num数量、每个品种下历史获得的数量、被奖励总次数)

        //1、
        //用户总计被奖励次数
        //被奖励次数越多获得的数量和品种越少(digg_num的余量用于中和digg_num数)
        //$reward_count = $this->redis->get(self::$redis_key.'user:'.USERID.':count');

        //2、
        //digg_num越大获得的品种和数量越多(决定本次获得的品种个数)

        $reward_type_count = $digg_num > 16 ? mt_rand(3, 4) : mt_rand(1, 4);
        //3、
        //概率数组
        //每个品种下历史获得的数量越大本次获得的相同品种越低并且数量越少(决定本次品种的概率(取反),若获得相同时则丢掉并次数加１(重新获取一次))

        $prize_tmp = [];

        while ($reward_type_count) {
            //获取奖励品种
            if (empty($prize_tmp)) {
                //保证礼物资源只取一个
                $prize_key = self::randReward($gift_probability);
            } else {
                $prize_key = self::randReward($virtual_probability);
            }

            if (in_array($prize_key, $prize_tmp) || empty($prize_key)) continue;

            switch ($reward_all[$prize_key]['type']) {
                case 'gift':
                    $count = rand(1, 2);
                    break;

                case 'coin':
                    $count = rand(1, 3);
                    break;

                case 'income':
                    $count = rand(1, 10);
                    break;

                default:
                    $count = 1;
                    break;
            }

            $tmp = [
                'type' => $reward_all[$prize_key]['type'],
                'num' => $count, //可用余量,随使用会消耗
                'total' => $count, //本次获取数量,不随使用而减少
                'user_id' => USERID,
                'video_id' => $video_id, //那个视频下获得的
                'gift_id' => $reward_all[$prize_key]['type'] == 'gift' ? $reward_all[$prize_key]['id'] : null,
                'digg_total' => $digg_num,
                'create_time' => $now,
            ];

            switch ($tmp['type']) {
                case 'income':
                    $user_update_data['millet'] += $count;
                    break;

                case 'coin':
                    $user_update_data['bean'] += $count;
                    break;

                case 'exp':
                    $user_update_data['exp'] += $count;
                    break;
            }

            array_push($reward_data, $tmp);

            $redis_key = $tmp['type'] == 'gift' ? self::$redis_key . 'user:' . USERID . ':gift_' . $reward_all[$prize_key]['id'] : self::$redis_key . 'user:' . USERID . ':' . $tmp['type'];

            //记录用户所获奖励品种数量
            $this->redis->incrby($redis_key, $count);

            //最终所获奖品集合
            $prizes[] = [
                'icon' => $reward_all[$prize_key]['icon'],
                'name' => $reward_all[$prize_key]['name'],
                'count' => $count
            ];

            array_push($prize_tmp, $prize_key);

            --$reward_type_count;
        }

        //更新用户帐户数据
        if (!empty($user_update_data)) {
            $userModel = new User();



            //帐户增加
            if (isset($user_update_data['bean'])) {
                Db::name('bean')
                    ->where(['user_id' => USERID])
                    ->inc('bean', $user_update_data['bean'])
                    ->inc('total_bean', $user_update_data['bean'])
                    ->update();

                unset($user_update_data['bean']);

                $redis_update = $user_update_data;
            }

            if (!empty($user_update_data)) {
                if (isset($user_update_data['millet'])) {
                    $user_update = [
                        'millet' => Db::raw("`millet` + {$user_update_data['millet']}"),
                        'total_millet' => Db::raw("`total_millet` + {$user_update_data['millet']}"),
                        'his_millet' => Db::raw("`his_millet` + {$user_update_data['millet']}")
                    ];

                }

                isset($user_update_data['exp']) && $user_update['exp'] = Db::raw("`exp` + {$user_update_data['exp']}");

                if (isset($user_update)) Db::name('user')->where(['user_id' => USERID])->update($user_update);
            }

            //更新用户信息
            $userModel->updateData(USERID, $redis_update);
        }

        //写入个人奖励表
        Db::name('video_user_reward')->insertAll($reward_data);

        //记录抽奖次数
        $this->redis->incr(self::$redis_key . 'user:' . USERID . ':count');

        $this->redis->set(self::$redis_key . 'user:' . USERID . ':open_time', $now);

        return $prizes;
    }

    protected function probability()
    {
        //视频属性(视频曝光率、视频取向、)
        //用户相关属性(注册时间、平台赠送记录、使用率、)
        //用户历史获取记录(历史获取次数、获取的品种与数量)
        //宝箱数量总量
        //后台管理指定
        //发布者属性(身份、视频质量、认证创作)
        //时间周期
        //

    }

    //随机获取一个奖励
    protected static function randReward(&$probability)
    {
        asort($probability);

        $result = '';

        //概率数组的总概率精度
        $proSum = array_sum($probability);

        //概率数组循环
        foreach ($probability as $key => $proCur) {
            $randNum = mt_rand(0, $proSum);

            if ($randNum >= $proCur) {
                $result = $key;
                unset($probability[$key]);
                break;
            } else {
                $proSum -= $proCur;
            }
        }

        return $result;
    }

    //随机获取奖励个数
    protected static function randRewardNum()
    {

        return rand(1, 3);
    }

    /**
     * 打赏
     * @param $id
     * @param $to_uid
     * @param $message
     */
    public function giveReward($gift_id, $to_uid, $video_id, $message = '')
    {
        if (USERID == $to_uid) return $this->setError('不能给自已的视频打赏');

        //获取礼物信息
        $reward_gift = Db::name('gift')->where(['id' => $gift_id, 'cid' => self::$gift_category])->find();

        if (empty($reward_gift)) return $this->setError('未查到相关礼物');

        //获取最新帐户余额(有可能不是最新的)
        $bean = Db::name('bean')->where(['user_id' => USERID])->value('bean');

        //礼物价格
        $gift_price = $reward_gift['price'] * $reward_gift['discount'];

        //获取当前用户所获得的礼物
        $my_gift = Db::name('video_user_reward')->where(['gift_id' => $gift_id, 'user_id' => USERID, 'type' => 'gift'])->sum('num');

        if (empty($my_gift)) {
            //验证余额是否够
            if ($bean < $gift_price) return $this->setError(APP_BEAN_NAME . '不足', 1005);

            $bean = (int)($bean - $gift_price);
        } else {
            $bean = (int)$bean;
        }

        //扣款
        $MQ = new RabbitMqChannel(['gift.common']);

        $MQ->exchange('main')->sendOnce('gift.give', ['act' => 'give', 'data' => [
            'gift_id' => $gift_id,
            'num' => 1,
            'user_id' => USERID,
            'to_uid' => $to_uid,
            'consume_order' => 'video_user_reward,bean',
            'pay_scene' => 'video',
            'video_id' => $video_id,
            'leave_msg' => $message,
            'total_millet' => $gift_price
        ]]);

        //打赏任务
        $taskMod = new Task();
        $data = [
            'user_id' => USERID,
            'task_type' => 'dayReward',
            'task_value' => 1,
            'status' => 0
        ];
        $taskMod->subTask($data);

        //写入打赏榜单redis
        $this->redis->zIncrBy(self::$redis_key . 'rank:' . $video_id, $gift_price, USERID);
        $this->redis->zIncrBy('video_gift_' . $gift_id . ':'.$to_uid , 1, 'gift_video_num');
        return ['bean' => $bean];
    }

    /**
     * 开启宝箱前置检查
     * @param $video_id
     * @return \App\Common\BuguCommon\BaseError|\bxkj_common\BaseError|int
     */
    public function preOpenReward($video_id)
    {
        $is = $this->isOpenReward();

        if (!$is) return $this->setError('哇。宝箱飞走咯~');

        return 1;
    }

}