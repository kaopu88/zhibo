<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/9/27 0027
 * Time: 上午 10:33
 */

namespace bxkj_module\service;

use bxkj_common\RedisClient;
use think\Db;

class Fenxiao extends Service
{
    //获取礼物分销配置 分销地方直接写
    public function getDistributeConfig()
    {
        $distribute_info = Db::name('sys_config')->where(array('mark' => 'giftdistribute'))->find();
        $data = json_decode($distribute_info['value'], true);
        return $data;
    }

    //获取分销等级对应的佣金比例
    public function getDistributeRate($userId, $rate)
    {
        $fenxiao_level = Db::name('user')->where('user_id', $userId)->value('fenxiao_level');
        if ($fenxiao_level > 0) {
            $level_info = Db::name('gift_commission_level')->field($rate)->where(array('id' => $fenxiao_level))->find();
            $fenxiao_level = $level_info[$rate];
        } else {
            $level_info = Db::name('sys_config')->where(array('mark' => 'giftdistribute'))->find();
            $level_info = json_decode($level_info['value'], true);
            $fenxiao_level = $level_info[$rate];
        }
        return $fenxiao_level;
    }

    /**
     * @param $userId 打赏的用户
     * @param $totalMillet 金额
     * @return bool
     */
    public function distributionCommission($userId, $totalMillet = 0, $gift_id, $tradeType = 'live_gift')
    {
        if (empty($userId) || $totalMillet <= 0) return false;

        $distribute_info = $this->getDistributeConfig();
        if ( empty($distribute_info)  || empty($distribute_info['is_open'])) return false;

        $userService = new User();
        $user = $userService->getUser($userId);
        $this->distribute($userId, ['reward_num' => 1, 'reward_money' => $totalMillet]);
        $this->distributeUpgrade($userId);
        if ($user['pid'] == 0) return false;
        $one_rate = $this->getDistributeRate($user['pid'], 'one_rate');
        $preuser = $userService->getUser($user['pid']);
        $commission_money = $totalMillet * $one_rate / 100;
        $param = [];
        $param['gift_id'] = $gift_id;
        $param['trade_type'] = $tradeType;
        $param['total'] = $totalMillet;
        $param['cont_uid'] = $userId;
        Db::startTrans();
        $trade_type = 'distribute';
        if ($commission_money > 0) {
            try {
                $no = get_order_no($trade_type);
            } catch (\Exception $exception) {
                Db::rollback();
                return $this->setError('timeout');
            }
            $param['trade_no'] = $no;
            $param['to_uid'] = $preuser['user_id'];
            $param['commission_rate'] = $one_rate;
            $param['commission_money'] = $commission_money;
            $update = [
                'commission_price' => ($preuser['commission_price'] + $commission_money),
                'commission_total_price' => ($preuser['commission_total_price'] + $commission_money)
            ];
            $res = Db::name('bean')->where(array('user_id' => $preuser['user_id']))->update($update);
            if (!$res) {
                Db::rollback();
                return $this->setError('未知错误');
            }
            $this->distribute($preuser['user_id'], ['one_reward_num' => 1, 'one_reward_money' => $totalMillet]);
            $this->distributeUpgrade($preuser['user_id']);
            $this->addCommissionLog($param);
            User::updateRedis($preuser['user_id'], $update);
        }

        $two_pre_pid = 0;
        if ($distribute_info['level'] >= 2 && !empty($preuser['pid'])) {
            $pre_two_user = $userService->getUser($preuser['pid']);
            $two_pre_pid = $pre_two_user['pid'];
            $two_rate = $this->getDistributeRate($preuser['pid'], 'two_rate');
            $commissiontwo__money = $totalMillet * $two_rate / 100;
            try {
                $no = get_order_no($trade_type);
            } catch (\Exception $exception) {
                Db::rollback();
                return $this->setError('timeout');
            }

            if ($commissiontwo__money > 0) {
                $update = [
                    'commission_price' => ($pre_two_user['commission_price'] + $commissiontwo__money),
                    'commission_total_price' => ($pre_two_user['commission_total_price'] + $commissiontwo__money)
                ];
                $res = Db::name('bean')->where(array('user_id' => $pre_two_user['user_id']))->update($update);
                if (!$res) {
                    Db::rollback();
                    return $this->setError('未知错误');
                }
                $param['trade_no'] = $no;
                $param['to_uid'] = $pre_two_user['user_id'];
                $param['commission_rate'] = $two_rate;
                $param['commission_money'] = $commissiontwo__money;
                $this->addCommissionLog($param);
                User::updateRedis($pre_two_user['user_id'], $update);
            }
        }

        if (!empty($two_pre_pid) && $distribute_info['level'] >= 3) {
            $pre_three_user = $userService->getUser($two_pre_pid);
            $three_rate = $this->getDistributeRate($two_pre_pid, 'three_rate');
            $commissionthree__money = $totalMillet * $three_rate / 100;
            try {
                $no = get_order_no($trade_type);
            } catch (\Exception $exception) {
                Db::rollback();
                return $this->setError('timeout');
            }

            if ($commissionthree__money > 0) {
                $update = [
                    'commission_price' => ($pre_three_user['commission_price'] + $commissionthree__money),
                    'commission_total_price' => ($pre_three_user['commission_total_price'] + $commissionthree__money)
                ];
                $res = Db::name('bean')->where(array('user_id' => $pre_three_user['user_id']))->update($update);
                if (!$res) {
                    Db::rollback();
                    return $this->setError('未知错误');
                }
                $param['trade_no'] = $no;
                $param['to_uid'] = $pre_three_user['user_id'];
                $param['commission_rate'] = $three_rate;
                $param['commission_money'] = $commissionthree__money;
                $this->addCommissionLog($param);
                User::updateRedis($pre_three_user['user_id'], $update);
            }
        }

        Db::commit();
        return true;
    }

    public function addCommissionLog(array $params)
    {
        $data = [
            'gift_id' => $params['gift_id'],
            'to_uid' => $params['to_uid'],//获得者ID
            'total' => $params['total'],
            'trade_type' => $params['trade_type'],
            'type' => 'inc',
            'cont_uid' => $params['cont_uid'],//贡献人id
            'trade_no' => $params['trade_no'],
            'commission_rate' => $params['commission_rate'],
            'commission_money' => $params['commission_money'],
            'create_time' => time(),
        ];
        $id = Db::name('gift_commission_log')->insertGetId($data);
        return $id;
    }

    public function distribute($userId, array $params = [])
    {
        //更新分销商的 消费金额 消费次数等
        $redis = new RedisClient();
        $reward_num = isset($params['reward_num']) ? $params['reward_num'] : 0;
        $reward_money = isset($params['reward_money']) ? $params['reward_money'] : 0;
        $one_reward_num = isset($params['one_reward_num']) ? $params['one_reward_num'] : 0;
        $one_reward_money = isset($params['one_reward_money']) ? $params['one_reward_money'] : 0;
        $child_num = isset($params['child_num']) ? $params['child_num'] : 0;
        $key = 'distribute:' . $userId;
        //同步增长
        $redis->zIncrBy($key, $reward_num, 'reward_num');
        $redis->zIncrBy($key, $reward_money, 'reward_money');
        $redis->zIncrBy($key, $one_reward_num, 'one_reward_num');
        $redis->zIncrBy($key, $one_reward_money, 'one_reward_money');
        $redis->zIncrBy($key, $child_num, 'child_num');
    }

    //分销商检测升级 用户Id
    public function distributeUpgrade($userId)
    {
        if (empty($userId)) return false;
        $redis = new RedisClient();
        $key = 'distribute:' . $userId;
        if (!$redis->exists($key)) return false;

        $fenxiao_level = Db::name('user')->where('user_id', $userId)->value('fenxiao_level');
        $where = [
            ['id', '>', $fenxiao_level]
        ];
        $level_list = Db::name('gift_commission_level')->where($where)->order('id asc')->select();
        if (empty($level_list)) return false;

        $upgrade_level = null;
        foreach ($level_list as $item) {
            if ($item['upgrade_type'] == 2) {
                if (
                    $redis->zScore($key, 'reward_num') >= $item['fenxiao_reward_num'] &&
                    $redis->zScore($key, 'reward_money') >= $item['fenxiao_reward_money'] &&
                    $redis->zScore($key, 'one_reward_num') >= $item['one_fenxiao_reward_num'] &&
                    $redis->zScore($key, 'one_reward_money') >= $item['one_fenxiao_reward_money'] &&
                    $redis->zScore($key, 'child_num') >= $item['child_num']
                ) {
                    $upgrade_level = $item;
                    break;
                }
            } else {
                if (($redis->zScore($key, 'reward_num') >= $item['fenxiao_reward_num'] && $item['fenxiao_reward_num'] > 0) ||
                    ($redis->zScore($key, 'reward_money') >= $item['fenxiao_reward_money'] && $item['fenxiao_reward_money'] > 0) ||
                    ($redis->zScore($key, 'one_reward_num') >= $item['one_fenxiao_reward_num'] && $item['one_fenxiao_reward_num'] > 0) ||
                    ($redis->zScore($key, 'one_reward_money') >= $item['one_fenxiao_reward_money'] && $item['one_fenxiao_reward_money'] > 0) ||
                    ($redis->zScore($key, 'child_num') >= $item['child_num'] && $item['child_num'] > 0)) {
                    $upgrade_level = $item;
                    break;
                }
            }
        }

        if ($upgrade_level) {
            $num = Db::name('user')->where(['user_id' => $userId])->update(['fenxiao_level' => $upgrade_level['id']]);
        }
        return true;
    }
}