<?php

namespace bxkj_module\service;

use bxkj_common\ClientInfo;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;

class Bean extends Service
{

    //参与经纪人业绩统计
    protected static $achievement = ['live_gift', 'cover_star_vote', 'egg'];

    //参与用户英雄榜统计
    protected static $heroesRank = ['live_gift', 'cover_star_vote', 'liudanji'];

    public static function parseBeanAccForUsers(&$users, $prefix = '')
    {
        $userIds = self::getIdsByList($users, 'user_id');
        if (!empty($userIds)) {
            $beanList = Db::name('bean')->whereIn('user_id', $userIds)->field('id bean_id,user_id,cash_status,pay_status,bean,fre_bean,total_bean,loss_bean,last_pay_time')->select();
            foreach ($users as &$item) {
                $beanInfo = self::getItemByList($item['user_id'], $beanList, 'user_id');
                if (!empty($prefix)) {
                    $item[$prefix] = $beanInfo;
                } else {
                    $item = array_merge($item, is_array($beanInfo) ? $beanInfo : []);
                }
            }
        }
    }

    //奖励
    public function reward($inputData)
    {
        if (!enum_in($inputData['type'], 'bean_reward_types')) {
            return $this->setError('奖励类型不存在');
        }
        $bean = (int)$inputData['bean'];
        $userId = $inputData['user_id'];
        if ($bean <= 0) return $this->setError('奖励数值不正确');
        if (empty($userId)) return $this->setError('USER_ID不能为空');
        $data['user_id'] = $userId;
        $data['bean'] = $bean;
        $data['type'] = $inputData['type'];
        $data['create_time'] = time();
        $id = Db::name('bean_reward')->insertGetId($data);
        if (!$id) return $this->setError('奖励失败[01]');
        $data['id'] = $id;
        $incRes = $this->inc(array(
            'user_id' => $userId,
            'total' => (int)$bean,
            'trade_type' => 'reward',
            'trade_no' => $id,
            'client_ip' => $inputData['client_ip'] ? $inputData['client_ip'] : '',
            'app_v' => $inputData['app_v'] ? $inputData['app_v'] : ''
        ));
        if (!$incRes) return $this->setError('奖励失败[02]');
        return $data;
    }

    //奖励钻石
    public function rewardBean($inputData)
    {

        if (!enum_in($inputData['type'], 'bean_reward_types')) {
            return $this->setError('奖励类型不存在');
        }

        $bean = (int)$inputData['bean'];
        $userId = $inputData['user_id'];
        if ($bean <= 0) return $this->setError('奖励数值不正确');
        if (empty($userId)) return $this->setError('USER_ID不能为空');
        $data['user_id'] = $userId;
        $data['bean'] = $bean;
        $data['type'] = $inputData['type'];
        $data['create_time'] = time();
        $id = Db::name('bean_reward')->insertGetId($data);
        if (!$id) return $this->setError('奖励失败[01]');
        $data['id'] = $id;
        $incRes = $this->incBean(array(
            'user_id' => $userId,
            'total' => (int)$bean,
            'trade_type' => $inputData['trade_type'],
            'trade_no' => $id,
            'client_ip' => $inputData['client_ip'] ? $inputData['client_ip'] : '',
            'app_v' => $inputData['app_v'] ? $inputData['app_v'] : ''
        ));
        if (!$incRes) return $this->setError('奖励失败[02]');
        return $data;
    }

    //收入
    public function inc($inputData)
    {
        return $this->change('inc', $inputData);
    }

    //奖励添加
    public function incBean($inputData){
        $type = 'inc';
        ClientInfo::refreshByParams($inputData);
        $userId = $inputData['user_id'];
        $total = $inputData['total'];
        $tradeType = $inputData['trade_type'];
        $newStr= sprintf('%09s', $inputData['trade_no']);
        $tradeNo = date('Ymd',time()).$newStr;
        $typeNames = array(
            'inc' => '收入',
            'exp' => '支出'
        );
        if (!array_key_exists($type, $typeNames)) $this->setError('变更类型不正确');
        if (empty($tradeType)) return $this->setError('交易类型不能为空');
        if (!validate_regex($total, '/^[0-9]+$/') || $total <= 0) return $this->setError(APP_BEAN_NAME . '数额不正确');
        self::startTrans();
        $userService = new User();
        if (!is_array($userId)) {
            $user = $userService->getBasicInfo($userId);
        } else {
            $user =& $inputData['user_id'];
            $userId = $user['user_id'];
        }

        if (empty($user)) {
            self::rollback();
            return $this->setError('用户不存在');
        }

        $log['log_no'] = get_order_no('log');
        $log['user_id'] = $userId;
        $log['type'] = $type;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_bean'] = $user['total_bean'];
        $log['last_fre_bean'] = $user['fre_bean'];
        $log['last_bean'] = $user['bean'];
        $log['client_ip'] = ClientInfo::get('client_ip');
        $log['app_v'] = ClientInfo::get('v');
        $log['create_time'] = time();
        $loss_bean = $user['loss_bean'];//不参与业绩统计的额度
        $lossTotal = 0;
        //更新钻石表
        $update['bean'] = $type == 'inc' ? ($user['bean'] + $total) : ($user['bean'] - $total);
        $update['fre_bean'] = $user['fre_bean'];
        $update['total_bean'] = $update['bean'] + $update['fre_bean'];
        $update['last_change_time'] = time();
        //统计
        if ($type == 'inc') {
                //加入累计充值的
                $update['recharge_total'] = $user['recharge_total'] + $total;
        }
        $num = Db::name('bean')->where(array('id' => $user['bean_id']))->update($update);
        $typeName = $typeNames[$type];
        if (!$num) {
            self::rollback();
            return $this->setError("{$typeName}失败[01]");
        }
        $user = array_merge($user, $update);
        if (!empty($userUpdate)) {
            $userUpdateNum = Db::name('user')->where(array('user_id' => $user['user_id']))->update($userUpdate);
            if (!$userUpdateNum) {
                self::rollback();
                return $this->setError("{$typeName}失败[01]");
            }
            $user = array_merge($user, $userUpdate);
        }
        $userService->updateRedis($user['user_id'], array_merge($update, $userUpdate ? $userUpdate : []));
        $log = array_merge($log, copy_array($update, 'fre_bean,bean,total_bean'));
        $id = Db::name('bean_log')->insert($log);
        if (!$id) {
            self::rollback();
            return $this->setError("{$typeName}失败[02]");
        }
        $update['log_no'] = $log['log_no'];
        self::commit();
        return $update;
    }

    //支出
    public function exp($inputData)
    {
        return $this->change('exp', $inputData);
    }

    protected function change($type, $inputData)
    {
        ClientInfo::refreshByParams($inputData);
        $userId = $inputData['user_id'];
        $total = $inputData['total'];
        $tradeType = $inputData['trade_type'];
        $tradeNo = $inputData['trade_no'];
        $typeNames = array(
            'inc' => '收入',
            'exp' => '支出'
        );
        if (!array_key_exists($type, $typeNames)) $this->setError('变更类型不正确');
        if (empty($tradeType)) return $this->setError('交易类型不能为空');
        if (!validate_regex($total, '/^[0-9]+$/') || $total <= 0) return $this->setError(APP_BEAN_NAME . '数额不正确');
        self::startTrans();
        $userService = new User();
        if (!is_array($userId)) {
            $user = $userService->getBasicInfo($userId, null);
        } else {
            $user =& $inputData['user_id'];
            $userId = $user['user_id'];
        }
        if (empty($user)) {
            self::rollback();
            return $this->setError('用户不存在');
        }
        //支出
        if ($type == 'exp') {
            if ($user['pay_status'] != '1') {
                self::rollback();
                return $this->setError('账户支付功能已禁用');
            }
            if ($tradeType == 'cash' && $user['cash_status'] != '1') {
                self::rollback();
                return $this->setError('账户提现功能已禁用');
            }
            if ($total > $user['bean']) {
                self::rollback();
                return $this->setError(APP_BEAN_NAME . '不足', 1005);
            }
        }
        $log['log_no'] = get_order_no('log');
        $log['user_id'] = $userId;
        $log['type'] = $type;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_bean'] = $user['total_bean'];
        $log['last_fre_bean'] = $user['fre_bean'];
        $log['last_bean'] = $user['bean'];
        $log['client_ip'] = ClientInfo::get('client_ip');
        $log['app_v'] = ClientInfo::get('v');
        $log['create_time'] = time();
        $loss_bean = $user['loss_bean'];//不参与业绩统计的额度
        $lossTotal = 0;
        //更新金币表
        $update['bean'] = $type == 'inc' ? ($user['bean'] + $total) : ($user['bean'] - $total);
        $update['fre_bean'] = $user['fre_bean'];
        $update['total_bean'] = $update['bean'] + $update['fre_bean'];
        $update['last_change_time'] = time();

        //统计
        if ($type == 'inc') {
            if ($tradeType == 'recharge') {
                //累计充值的
                $update['recharge_total'] = $user['recharge_total'] + $total;
            }
        } else {
            $update['last_pay_time'] = time();//最后一次支出时间
            if ($loss_bean > 0) {
                $lossTotal = $total > $loss_bean ? $loss_bean : $total;
                $update['loss_bean'] = $loss_bean - $lossTotal;
            }
            if ($tradeType == 'cash') {
                //累计提现的
                $update['cash_total'] = $user['cash_total'] + $total;
            } else {
                //累计消费的
                $update['pay_total'] = $user['pay_total'] + $total;
                //总消费转为经验值
                $rate = config('app.app_setting.exp_rate');
                $incExp = $rate * $total;
                $userUpdate['exp'] = $user['exp'] + $incExp;
                $nowLv = User::getFillLv($userUpdate['exp']);
                //升级
                if ($nowLv > $user['level']) {
                    $userUpdate['level'] = $nowLv;
                    $userUpdate['last_upgrade_time'] = time();//最近升级时间
                }
            }
        }
        $num = Db::name('bean')->where(array('id' => $user['bean_id']))->update($update);
        $typeName = $typeNames[$type];
        if (!$num) {
            self::rollback();
            return $this->setError("{$typeName}失败[01]");
        }
        $user = array_merge($user, $update);
        if (!empty($userUpdate)) {
            $userUpdateNum = Db::name('user')->where(array('user_id' => $user['user_id']))->update($userUpdate);
            if (!$userUpdateNum) {
                self::rollback();
                return $this->setError("{$typeName}失败[01]");
            }
            $user = array_merge($user, $userUpdate);
        }

        $userService->updateRedis($user['user_id'], array_merge($update, $userUpdate ? $userUpdate : []));

        $log = array_merge($log, copy_array($update, 'fre_bean,bean,total_bean'));
        $id = Db::name('bean_log')->insert($log);
        if (!$id) {
            self::rollback();
            return $this->setError("{$typeName}失败[02]");
        }
        $update['log_no'] = $log['log_no'];


        //虚拟用户也计入用户英雄榜 2018-10-16
        if ($type == 'exp')
        {
            // 用户英雄榜
            if (in_array($tradeType, self::$heroesRank)) self::updateHeroesRank($user, 'gift', $total);

            //股权榜-计算所有
            self::updateHeroesRank($user, 'all', $total);

            $kpiLog = $log;

            if ($loss_bean > 0)
            {
                $kpiLog['total'] = $kpiLog['total'] - $lossTotal;//扣除不参与统计的
                $kpiLog['loss_total'] = $lossTotal;
            }

            //统计经纪人业绩
            if (in_array($tradeType, self::$achievement))
            {
                $kpi = new Kpi($kpiLog['create_time']);
                $consRes = $kpi->cons($inputData['to_uid'], $user, $kpiLog);
                if (!$consRes) {
                    self::rollback();
                    return $this->setError("cons失败");
                }
            }
        }
        
        self::commit();
        return $update;
    }

    //更新英雄榜
    protected function updateHeroesRank($user, $type, $total)
    {
        $weekNum = DateTools::getWeekNum();
        $redis = RedisClient::getInstance();
        $hisk = "rank:heroes:{$type}:history";//总历史榜
        $yk = "rank:heroes:{$type}:y:" . date('Y');//年榜
        $mk = "rank:heroes:{$type}:m:" . date('Ym');//月榜
        $wk = "rank:heroes:{$type}:w:" . $weekNum;//周榜
        $dk = "rank:heroes:{$type}:d:" . date('Ymd');//日榜
        $userId = $user['user_id'];
        //同步增长
        $redis->zIncrBy($hisk, $total, $userId);
        $redis->zIncrBy($yk, $total, $userId);
        $redis->zIncrBy($mk, $total, $userId);
        $redis->zIncrBy($wk, $total, $userId);
        $redis->zIncrBy($dk, $total, $userId);
    }
    //查询今日是否已经发放了相关奖励
    public function QueryTodayfind($condition )
    {
        $this->db = Db::name('bean_log');
        $list     = $this->db->where($condition)->whereBetweenTime('create_time', date("Y-m-d"))->find();
        return $list;
    }

    public function getOne($condition)
    {
        $this->db = Db::name('bean_log');
        $list = $this->db->where($condition)->find();
        return $list;
    }

    //获取账号明细
    public function getInfo($userId)
    {
        $result = Db::name('bean')->field('cash_status,pay_status,bean,fre_bean,total_bean,isset_pwd')->where('user_id', '=', $userId)->find();
        if (empty($result)) return $this->setError(APP_BEAN_NAME.'账号不存在');
        return $result ? $result : [];
    }
}