<?php

namespace app\service;

//送礼物分销
class Distribute
{
    //获取分销等级对应的佣金比例
    public static function getDistributeRate($userId, $rate)
    {
        global $db;
        $fenxiao_sql = "SELECT fenxiao_level FROM " . TABLE_PREFIX . "user WHERE user_id=" . $userId . " LIMIT 1";
        $fenxiao = $db->query($fenxiao_sql);
        $fenxiao_level = [];
        if (empty($fenxiao)) return 0;

        if ($fenxiao[0]['fenxiao_level'] > 0) {
            $sql = "SELECT " . $rate . " FROM " . TABLE_PREFIX . "gift_commission_level WHERE id=" . $fenxiao[0]['fenxiao_level'] . " LIMIT 1";
            $level_info = $db->query($sql);
            if (!empty($level_info)) {
                $fenxiao_level = $level_info[0][$rate];
            }
        } else {
            $sql = "SELECT value FROM " . TABLE_PREFIX . "sys_config WHERE mark='giftdistribute' LIMIT 1";
            $level_info = $db->query($sql);
            if (!empty($level_info)) {
                $level_info = json_decode($level_info[0]['value'], true);
                $fenxiao_level = $level_info[$rate];
            }
        }

        return $fenxiao_level ?: 0;
    }

    //获取礼物分销配置
    public static function getDistributeConfig()
    {
        global $db, $redis;
        $json = $redis->get('distribute:gift');
        if (empty($json)) {
            $sql = "SELECT value FROM " . TABLE_PREFIX . "sys_config WHERE mark='giftdistribute' LIMIT 1";
            $distribute_info = $db->query($sql);
            if (empty($distribute_info)) return false;
            $redis->set('distribute:gift', $distribute_info[0]['value'], 4 * 3600);
            $distribute_info = json_decode($distribute_info[0]['value'], true);
        } else {
            $distribute_info = json_decode($json, true);
        }

        return $distribute_info;
    }

    //计算分销添加记录等
    public static function distributionCommission($userId, $totalMillet = 0, $gift_id, $tradeType = 'live_gift')
    {
        global $db, $redis;
        $trade_type = 'distribute';
        if (empty($userId) || $totalMillet <= 0) return false;

        $distribute_info = self::getDistributeConfig();
        if (empty($distribute_info) || empty($distribute_info['is_open'])) return false;

        $user = User::getUser($userId);
        self::distribute($userId, ['reward_num' => 1, 'reward_money' => $totalMillet]);
        self::distributeUpgrade($userId);
        if ($user['pid'] == 0) return false;

        $one_rate = self::getDistributeRate($user['pid'], 'one_rate');
        $preuser = User::getUser($user['pid']);
        $commission_money = $totalMillet * $one_rate / 100;
        $param = [];
        $param['gift_id'] = $gift_id;
        $param['trade_type'] = $tradeType;
        $param['total'] = $totalMillet;
        $param['cont_uid'] = $userId;
        Db::startTrans();

        if ($commission_money > 0 &&  $distribute_info['level'] >= 1) {
            try {
                $no = get_order_no($trade_type);
            } catch (\Exception $exception) {
                Db::rollback();
                return false;
            }
            $update = [
                'commission_price' => ($preuser['commission_price'] + $commission_money),
                'commission_total_price' => ($preuser['commission_total_price'] + $commission_money)
            ];
            $res = $db->update(TABLE_PREFIX . 'bean')->cols(
                ['commission_price' => ($preuser['commission_price'] + $commission_money),
                    'commission_total_price' => ($preuser['commission_total_price'] + $commission_money)
                ])->where("user_id=" . $preuser['user_id'])->query();

            if (!$res) {
                Db::rollback();
                return false;
            }
            $param['trade_no'] = $no;
            $param['to_uid'] = $preuser['user_id'];
            $param['commission_rate'] = $one_rate;
            $param['commission_money'] = $commission_money;
            self::addCommissionLog($param);
            self::distribute($preuser['user_id'], ['one_reward_num' => 1, 'one_reward_money' => $totalMillet]);
            self::distributeUpgrade($preuser['user_id']);
            User::updateRedis($preuser['user_id'], $update);
        }

        $two_pre_pid = 0;
        if ($distribute_info['level'] >= 2 && !empty($preuser['pid'])) {
            $pre_two_user = User::getUser($preuser['pid']);
            $two_pre_pid = $pre_two_user['pid'];
            $two_rate = self::getDistributeRate($preuser['pid'], 'two_rate');
            $commissiontwo__money = $totalMillet * $two_rate / 100;
            try {
                $no = get_order_no($trade_type);
            } catch (\Exception $exception) {
                Db::rollback();
                return false;
            }
            if ($commissiontwo__money > 0) {
                $update = [
                    'commission_price' => ($pre_two_user['commission_price'] + $commissiontwo__money),
                    'commission_total_price' => ($pre_two_user['commission_total_price'] + $commissiontwo__money)
                ];
                $res = $db->update(TABLE_PREFIX . 'bean')->cols(
                    ['commission_price' => ($pre_two_user['commission_price'] + $commissiontwo__money),
                        'commission_total_price' => ($pre_two_user['commission_total_price'] + $commissiontwo__money)
                    ])->where("user_id=" . $pre_two_user['user_id'])->query();
                if (!$res) {
                    Db::rollback();
                    return false;
                }
                $param['trade_no'] = $no;
                $param['to_uid'] = $pre_two_user['user_id'];
                $param['commission_rate'] = $two_rate;
                $param['commission_money'] = $commissiontwo__money;
                self::addCommissionLog($param);
                User::updateRedis($pre_two_user['user_id'], $update);
            }
        }

        if (!empty($two_pre_pid) && $distribute_info['level'] >= 3) {
            $pre_three_user = User::getUser($two_pre_pid);
            $three_rate = self::getDistributeRate($two_pre_pid, 'three_rate');
            $commissionthree__money = $totalMillet * $three_rate / 100;
            try {
                $no = get_order_no($trade_type);
            } catch (\Exception $exception) {
                Db::rollback();
                return false;
            }
            if ($commissionthree__money > 0) {
                $update = [
                    'commission_price' => ($pre_three_user['commission_price'] + $commissionthree__money),
                    'commission_total_price' => ($pre_three_user['commission_total_price'] + $commissionthree__money)
                ];
                $res = $db->update(TABLE_PREFIX . 'bean')->cols(
                    ['commission_price' => ($pre_three_user['commission_price'] + $commissionthree__money),
                        'commission_total_price' => ($pre_three_user['commission_total_price'] + $commissionthree__money)
                    ])->where("user_id=" . $pre_two_user['user_id'])->query();
                if (!$res) {
                    Db::rollback();
                    return false;
                }
                $param['trade_no'] = $no;
                $param['to_uid'] = $pre_three_user['user_id'];
                $param['commission_rate'] = $three_rate;
                $param['commission_money'] = $commissionthree__money;
                self::addCommissionLog($param);
                User::updateRedis($pre_three_user['user_id'], $update);
            }
        }

        Db::commit();
    }

    //添加到分销日志表
    protected static function addCommissionLog($params)
    {
        global $db;
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
        $id = $db->insert(TABLE_PREFIX . 'gift_commission_log')->cols($data)->query();
        return $id;
    }

    //更新 消费金额 消费次数 人数 等
    public static function distribute($userId, array $params = [])
    {
        //更新分销商的 消费金额 消费次数等
        global $redis;
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

    public static function distributeUpgrade($userId)
    {
        if (empty($userId)) return false;
        global $db, $redis;
        $key = 'distribute:' . $userId;
        if (!$redis->exists($key)) return false;
        $fenxiao_sql = "SELECT fenxiao_level FROM " . TABLE_PREFIX . "user WHERE user_id=" . $userId . " LIMIT 1";
        $fenxiao = $db->query($fenxiao_sql);
        if (empty($fenxiao)) return false;

        $fenxiao_level = $fenxiao[0]['fenxiao_level'];
        $sql = "SELECT * FROM " . TABLE_PREFIX . "gift_commission_level WHERE id >" . $fenxiao_level . " order by id asc";
        $level_list = $db->query($sql);
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
            $num = $db->update(TABLE_PREFIX . 'user')->cols(['fenxiao_level' => $upgrade_level['id']])->where('user_id = ' . $userId)->query();
        }

        return true;
    }
}