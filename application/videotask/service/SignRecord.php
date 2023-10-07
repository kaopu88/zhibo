<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/7 0007
 * Time: 下午 2:41
 */
namespace app\videotask\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Bean;
use bxkj_module\service\Millet;
use think\Model;

class SignRecord extends Model
{
    public function getList($page = 1, $length = 100, $where = [], $filed = '*')
    {
        $list = $this->where($where)->field($filed)->page($page,$length)->select();
        return $list;
    }

    /**
     * 获取30天的奖励详情
     * @param $signRewardMillet 每日正常签到奖励的金币
     * @param $signRewardBean 每日正常签到奖励的钻石
     * @param $signContinuityRewardMillet 每日正常签到递增签到奖励的金币
     * @param $signContinuityRewardBean 每日正常签到递增签到奖励的钻石
     * @param $signMillet 连续签到奖励的金币
     * @param $signBean 连续签到奖励的钻石
     * @return \think\response\Json
     */
    public function signDetail($config)
    {
        $redis = RedisClient::getInstance();
        $data = $redis->get("sign_data");
        if (!empty($data)) return ['data' => json_decode($data, true), 'msg' => '获取成功~', 'code' => 200];

        $day = day_thirty($config['is_sign_circle']);
        if (empty($day) || !is_array($day)) return ['msg' => '天数配置错误~', 'code' => 1001];
        $item = [];
        $signRewardMillet = !empty($config['sign_reward']['millet']) ? $config['sign_reward']['millet'] : 0;
        $signRewardBean = !empty($config['sign_reward']['bean']) ? $config['sign_reward']['bean'] : 0;
        $signContinuityRewardMillet = !empty($config['sign_continuity_reward']['millet']) ? $config['sign_continuity_reward']['millet'] : 0;
        $signContinuityRewardBean = !empty($config['sign_continuity_reward']['bean']) ? $config['sign_continuity_reward']['bean'] : 0;
        $signDay = !empty($config['sign_day']) ? $config['sign_day'] : [];
        $signMillet = !empty($config['sign_millet']) ? $config['sign_millet'] : [];
        $signBean = !empty($config['sign_bean']) ? $config['sign_bean'] : [];

        foreach ($day as $key => $value) {
            $continueMillet = 0;
            $continueBean = 0;
            $isContinue = 0;
            $idKey = array_search($value, $signDay);
            if ($idKey !== false) {
                $continueMillet = $signMillet[$idKey];
                $continueBean = $signBean[$idKey];
                $isContinue = 1;
            }

            $item[$key]['day'] = $value;
            $item[$key]['millet'] = $signRewardMillet + ($value - 1) * $signContinuityRewardMillet + $continueMillet;
            $item[$key]['bean'] = $signRewardBean + ($value - 1) * $signContinuityRewardBean + $continueBean;
            $item[$key]['is_continue'] = $isContinue;
        }
        $redis->set("sign_data", json_encode($item));
        return ['data' => $item, 'msg' => '获取成功~', 'code' => 200];
    }

    /**
     * 查看今天是否有签到数据
     * -1表示用户不存在
     * 1表示今天有数据
     * 0表示没有
     */
    public function isSign($uid)
    {
        if (empty($uid)) return ['code' => -1];
        $recode = $this->where(['uid' => $uid])->order('id desc')->find();
        if (!empty($recode)) $isSign = is_in_today(strtotime($recode['add_time']));
        if ($isSign) return ['code' => 1, 'res' => $recode];
        return ['code' => 0, 'res' => $recode];
    }

    /**
     * 奖励钻石
     */
    public function rewardBean($uid, $rewardBean)
    {
        $bean = new Bean();
        $bean->reward([
            'user_id' => $uid,
            'type' => 'sign_reward_bean',
            'bean' => $rewardBean
        ]);
    }

    /**
     * 奖励金币
     */
    public function rewardMillet($uid, $rewardMillet)
    {
        $millet = new Millet();
        $millet->reward([
            'user_id' => $uid,
            'cont_uid' => $uid,
            'type' => 'sign_reward_bean',
            'bean' => $rewardMillet
        ], 'sign_reward');
    }
}