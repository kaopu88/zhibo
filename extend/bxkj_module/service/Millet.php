<?php

namespace bxkj_module\service;

use bxkj_common\ClientInfo;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;
use think\Url;

/**
 * 主播收益类
 *
 * Class Millet
 * @package bxkj_module\service
 */
class Millet extends Service
{
    //参与主播业绩统计
    protected static $achievement = ['live_gift', 'cover_star_vote'];

    //收入谷子
    public function inc($inputData)
    {
        return $this->change('inc', $inputData);
    }

    //金币收入奖励
    public function incMill($inputData)
    {
        return $this->changeMill('inc', $inputData);
    }

    public function reward($inputData, $type = 'reward')
    {
        if (!enum_in($inputData['type'], 'bean_reward_types')) {
            return $this->setError('奖励类型不存在');
        }
        self::startTrans();
        $bean = (int)$inputData['bean'];
        $userId = $inputData['user_id'];
        $contUid = $inputData['cont_uid'];
        if ($bean <= 0) return $this->setError('奖励数值不正确');
        if (empty($userId)) return $this->setError('USER_ID不能为空');
        $trade_no = get_order_no('cash');
        $incRes = $this->inc(array(
            'cont_uid' => $contUid,
            'user_id' => $userId,
            'trade_type' => $type,
            'trade_no' => $trade_no,
            'total' => $bean
        ));
        if (!$incRes) {
            self::rollback();
            return $this->setError($this->getError());
        }
        //判断是否需要加入kpi记录
        $res = $this->insertKpi($contUid, $userId, $trade_no, $bean, $type);

        self::commit();
        return array();
    }

    /**
     * @param $promterRaltion
     * @param $contUid 消费者UID
     * @param $userId 获得者UID
     * @param $bean 获得的数量
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function insertKpi($contUid, $userId, $tradeNo, $bean, $type)
    {
        $promterRaltion = Db::name('promotion_relation')->field('agent_id, promoter_uid')->where(['user_id' => $userId])->find();
        if (empty($promterRaltion)) return false;
        $isInsertKpi = isagent_kpi_prifit($promterRaltion['agent_id']);
        if ($isInsertKpi) {
            //记录明细
            $data = [];
            $tmpWhere = ['user_id' => $contUid];
            $rel = Db::name('promotion_relation')->where($tmpWhere)->find();
            $data['agent_id'] = $promterRaltion['agent_id'];//主播所在的代理商ID
            $data['promoter_uid'] = isset($rel['promoter_uid']) ? $rel['promoter_uid'] : 0; //消费者的推广
            $data['cont_uid'] = $contUid;
            $data['cont_agent_id'] = isset($rel['agent_id']) ? $rel['agent_id'] : 0; //消费者所属代理
            $data['trade_type'] = $type;
            $data['trade_no'] = $tradeNo;
            $data['log_no'] = get_order_no('log');
            $data['millet'] = $bean;
            $data['get_uid'] = $userId;
            $now = time();
            $data['year'] = date('Y', $now);
            $data['month'] = date('Ym', $now);
            $data['day'] = date('Ymd', $now);
            $data['fnum'] = DateTools::getFortNum($now);
            $data['week'] = DateTools::getWeekNum($now);
            $data['create_time'] = $now;
            $id = Db::name('kpi_millet')->insertGetId($data);
            if (!$id) return false;
        }
        return true;
    }


    //奖励金币
    public function rewardMill($inputData, $type = 'reward')
    {
        if (!enum_in($inputData['type'], 'bean_reward_types')) {
            return $this->setError('奖励类型不存在');
        }

        self::startTrans();
        $bean = (int)$inputData['bean'];
        $userId = $inputData['user_id'];
        $contUid = $inputData['cont_uid'];
        if ($bean <= 0) return $this->setError('奖励数值不正确');
        if (empty($userId)) return $this->setError('USER_ID不能为空');
        $incRes = $this->incMill(array(
            'cont_uid' => $contUid,
            'user_id' => $userId,
            'trade_type' => $type,
            'trade_no' => get_order_no('cash'),
            'total' => $bean
        ));
        if (!$incRes) {
            self::rollback();
            return $this->setError($this->getError());
        }
        self::commit();
        return $incRes;
    }

    //支出谷子
    public function exp($inputData)
    {
        return $this->change('exp', $inputData);
    }

    public function cash($inputData)
    {
        ClientInfo::refreshByParams($inputData);
        $cash_setting = config('app.cash_setting');
        $userService = new User();
        if (is_array($inputData['user_id'])) {
            $user =& $inputData['user_id'];
        } else {
            if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
            $user = $userService->getBasicInfo($inputData['user_id']);
        }
        $millet = $inputData['millet'];
        $cash_account = $inputData['cash_account'];
        if (!validate_regex($millet, '/^[0-9]+$/') || $millet <= 0) return $this->setError(APP_MILLET_NAME . '数值不正确');
        if (empty($cash_account)) return $this->setError('请选择提现账号');
        if (empty($user)) return $this->setError('用户不存在');
        self::startTrans();
        if ($user['millet_status'] != '1') {
            self::rollback();
            return $this->setError(APP_MILLET_NAME . '提现功能已禁用');
        }
        if ($cash_setting['cash_on'] != 1) {
            self::rollback();
            return $this->setError('平台提现功能已禁用');
        }
        $cash_min = $cash_setting['cash_min'];
        if ($millet < $cash_min) return $this->setError('不能低于'.$cash_min.APP_MILLET_NAME);

        if ($millet > $user['millet']) {
            self::rollback();
            return $this->setError(APP_MILLET_NAME . '不足');
        }
        $cashAccountInfo = Db::name('cash_account')->where(array('user_id' => $user['user_id'], 'id' => $cash_account, 'delete_time' => null))->find();
        if (!$cashAccountInfo) {
            self::rollback();
            return $this->setError('提现账号不存在');
        }
        if ($cashAccountInfo['verify_status'] == '2') {
            self::rollback();
            return $this->setError('提现账号无效');
        }
        $num = Db::name('millet_cash')->where(array('user_id' => $user['user_id'], 'status' => 'wait'))->count();
        if ($num > 3) {
            self::rollback();
            return $this->setError('已有3笔提现申请正在处理');
        }
        $type = 0;
        if ($user['is_anchor'] == '1') $type = 1;
        $data['cash_no'] = get_order_no('cash');
        $data['user_id'] = $user['user_id'];
        $data['status'] = 'wait';
        $data['millet'] = $millet;
        $data['rmb'] = $this->cashMilletToRmb($millet, $type, $user['user_id']);
        $data['aid'] = 1;
        $data['cash_account'] = $cashAccountInfo['id'];
        $data['create_time'] = time();
        $this->supplementaryTime($data);
        $id = Db::name('millet_cash')->insertGetId($data);
        if (!$id) {
            self::rollback();
            return $this->setError('提现失败[01]');
        }
        $data['id'] = $id;
        $res = $this->exp(array(
            'user_id' => &$user,
            'trade_type' => 'cash',
            'trade_no' => $data['cash_no'],
            'total' => $millet
        ));
        if (!$res) {
            self::rollback();
            return $this->setError($this->getError());
        }
        self::commit();
        return array(
            'millet' => $user['millet'],
            'fre_millet' => $user['fre_millet'],
            'total_millet' => $user['total_millet'],
            'cash_no' => $data['cash_no']
        );
    }

    protected function change($type, $inputData)
    {
        ClientInfo::refreshByParams($inputData);
        $total = $inputData['total'];
        $tradeType = $inputData['trade_type'];
        $tradeNo = $inputData['trade_no'];
        $typeNames = array(
            'inc' => '收入',
            'exp' => '支出'
        );
        if (!array_key_exists($type, $typeNames)) return $this->setError('变更类型不正确');
        if (empty($tradeType)) return $this->setError('交易类型不能为空');
        if (!validate_regex($total, '/^\d+$/') || $total <= 0) return $this->setError(APP_MILLET_NAME . '数额不正确');
        $userService = new User();
        if ($type == 'inc') {
            //查询贡献者身份
            if (is_array($inputData['cont_uid'])) {
                $contributor =& $inputData['cont_uid'];
            } else {
                $contributor = $userService->getBasicInfo($inputData['cont_uid']);
            }
            //1虚拟的 0真实的 虚拟用户送的礼物等不计入业绩和主播排名
            if (empty($contributor) || !isset($contributor['isvirtual'])) return $this->setError('用户身份信息缺失');
            $contIsvirtual = $contributor['isvirtual'];
        } else {
            $contributor = [];
            $contIsvirtual = 0;
        }
        //查询用户身份
        if (is_array($inputData['user_id'])) {
            $user =& $inputData['user_id'];
        } else {
            $user = $userService->getBasicInfo($inputData['user_id']);
        }
        if (empty($user) || !isset($user['isvirtual'])) return $this->setError('用户身份信息缺失02');
        //自动兑换
        if (isset($inputData['exchange_type']) && !isset($total)) {
            $total = $this->exchange(array(
                'type' => $inputData['exchange_type'],
                'id' => $inputData['exchange_id'],
                'total' => $inputData['exchange_total'],
            ));
            if ($total === false) return false;
        }
        self::startTrans();
        //支出
        if ($type == 'exp') {
            if ($user['isvirtual'] == 1 && $tradeType == 'cash') {
                self::rollback();
                return $this->setError('虚拟用户不允许结算' . APP_MILLET_NAME);
            }
            if ($user['millet_status'] != '1') {
                self::rollback();
                return $this->setError(APP_MILLET_NAME . '已冻结');
            }
            if ($total > $user['millet']) {
                self::rollback();
                return $this->setError(APP_MILLET_NAME . '不足');
            }
        }
        $log['log_no'] = get_order_no('log');
        $log['cont_uid'] = isset($contributor['user_id']) ? $contributor['user_id'] : 0;
        $log['user_id'] = $user['user_id'];
        $log['type'] = $type;
        $log['isvirtual'] = $contIsvirtual;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_millet'] = $user['total_millet'];
        $log['last_fre_millet'] = $user['fre_millet'];
        $log['last_millet'] = $user['millet'];
        $log['last_isvirtual_millet'] = $user['isvirtual_millet'];
        $log['client_ip'] = ClientInfo::get('client_ip');
        $log['app_v'] = ClientInfo::get('v');
        $log['exchange_type'] = $inputData['exchange_type'] ? $inputData['exchange_type'] : '';
        $log['exchange_total'] = $inputData['exchange_total'] ? $inputData['exchange_total'] : '';
        $log['exchange_id'] = $inputData['exchange_id'] ? $inputData['exchange_id'] : '';
        $log['create_time'] = time();
        $millet = $user['millet'];
        $fre_millet = $user['fre_millet'];
        $total_millet = $millet + $fre_millet;
        $isvirtual_millet = $user['isvirtual_millet'];//收到的虚拟谷子数
        if ($type == 'inc') {
            $redis = RedisClient::getInstance();
            // $weekNum = "anchor_millet:w:".DateTools::getWeekNum();
            $dayNum = "anchor_millet:d:" . date('Ymd');
            $redis->zIncrBy($dayNum, $total, $user['user_id']);
        }
        //更新用户表
        if ($contIsvirtual == 0) {
            $millet = $type == 'inc' ? ($user['millet'] + $total) : ($user['millet'] - $total);
            $total_millet = $millet + $fre_millet;
            $update['millet'] = $millet;
            $update['fre_millet'] = $fre_millet;
            $update['total_millet'] = $total_millet;
        } else {
            $isvirtual_millet = $type == 'inc' ? ($user['isvirtual_millet'] + $total) : ($user['isvirtual_millet'] - $total);
            $update['isvirtual_millet'] = $isvirtual_millet;
        }
        if ($type == 'inc') {
            //历史总获得谷子数量
            if ($contIsvirtual == 0) {
                $update['his_millet'] = $user['his_millet'] + $total;
            } else {
                $update['his_isvirtual_millet'] = $user['his_isvirtual_millet'] + $total;
            }
        } else {
            if ($tradeType == 'cash') {
                $update['millet_cash_total'] = $user['millet_cash_total'] + $total;
            }
        }
        $typeName = $typeNames[$type];
        if (!empty($update)) {
            $num = Db::name('user')->where(array('user_id' => $user['user_id']))->update($update);
            if (!$num) {
                self::rollback();
                return $this->setError("{$typeName}失败[01]");
            }
            $user = array_merge($user, $update);
            $userService->updateRedis($user['user_id'], $update);
        }
        $log = array_merge($log, array(
            'fre_millet' => $fre_millet,
            'millet' => $millet,
            'total_millet' => $total_millet,
            'isvirtual_millet' => $isvirtual_millet
        ));
        $id = Db::name('millet_log')->insert($log);
        if (!$id) {
            self::rollback();
            return $this->setError("{$typeName}失败[02]");
        }
        $update['log_no'] = $log['log_no'];
        self::commit();
        if ($type == 'inc') {
            //收获米粒转为经验值
            $rate = config('app.app_setting.exp_rate');
            $incExp = $rate * $total;
            Db::name('anchor')->where(['user_id' => $user['user_id']])->setInc('anchor_exp', $incExp);
            $anchor = Db::name('anchor')->where(['user_id' => $user['user_id']])->find();
            if ($anchor) {
                $nowLv = User::getFillLv($anchor['anchor_exp'], 'anchor');
                //升级
                if ($nowLv > $user['anchor_lv']) {
                    Db::name('anchor')->where(['user_id' => $user['user_id']])->update(['anchor_lv' => $nowLv]);
                }
            }
            // 业绩统计
            if (in_array($tradeType, self::$achievement) && $contIsvirtual == 0) {
                $kpi = new Kpi($log['create_time']);
                $kpi->millet($contributor, $user, $log);
                //收到礼物转化而来的谷子计入主播魅力榜
                $this->updateCharmRank($user, $total);
            }
        }
        return $update;
    }

    protected function changeMill($type, $inputData)
    {
        ClientInfo::refreshByParams($inputData);
        $total = $inputData['total'];
        $tradeType = $inputData['trade_type'];
        $tradeNo = $inputData['trade_no'];
        $typeNames = array(
            'inc' => '收入',
            'exp' => '支出'
        );
        if (!array_key_exists($type, $typeNames)) return $this->setError('变更类型不正确');
        if (empty($tradeType)) return $this->setError('交易类型不能为空');
        if (!validate_regex($total, '/^\d+$/') || $total <= 0) return $this->setError(APP_MILLET_NAME . '数额不正确');
        $userService = new User();
        if ($type == 'inc') {
            //查询贡献者身份
            if (is_array($inputData['cont_uid'])) {
                $contributor =& $inputData['cont_uid'];
            } else {
                $contributor = $userService->getBasicInforeward($inputData['cont_uid']);
            }
            //1虚拟的 0真实的 虚拟用户送的礼物等不计入业绩和主播排名
            if (empty($contributor) || !isset($contributor['isvirtual'])) return $this->setError('用户身份信息缺失');
            $contIsvirtual = $contributor['isvirtual'];
        } else {
            $contributor = [];
            $contIsvirtual = 0;
        }
        //查询用户身份
        $user = $userService->getBasicInforeward($inputData['user_id']);
        if (empty($user) || !isset($user['isvirtual'])) return $this->setError('用户身份信息缺失02');
        self::startTrans();
        $log['log_no'] = get_order_no('log');
        $log['cont_uid'] = isset($contributor['user_id']) ? $contributor['user_id'] : 0;
        $log['user_id'] = $user['user_id'];
        $log['type'] = $type;
        $log['isvirtual'] = $contIsvirtual;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_millet'] = $user['total_millet'];
        $log['last_fre_millet'] = $user['fre_millet'];
        $log['last_millet'] = $user['millet'];
        $log['last_isvirtual_millet'] = $user['isvirtual_millet'];
        $log['client_ip'] = ClientInfo::get('client_ip');
        $log['app_v'] = ClientInfo::get('v');
        $log['exchange_type'] = $inputData['exchange_type'] ? $inputData['exchange_type'] : '';
        $log['exchange_total'] = $inputData['exchange_total'] ? $inputData['exchange_total'] : '';
        $log['exchange_id'] = $inputData['exchange_id'] ? $inputData['exchange_id'] : '';
        $log['create_time'] = time();
        $millet = $user['millet'];
        $fre_millet = $user['fre_millet'];
        $total_millet = $millet + $fre_millet;
        $isvirtual_millet = $user['isvirtual_millet'];//收到的虚拟谷子数
        //更新用户表
        if ($contIsvirtual == 0) {
            $millet = $type == 'inc' ? ($user['millet'] + $total) : ($user['millet'] - $total);
            $total_millet = $millet + $fre_millet;
            $update['millet'] = $millet;
            $update['fre_millet'] = $fre_millet;
            $update['total_millet'] = $total_millet;
        } else {
            $isvirtual_millet = $type == 'inc' ? ($user['isvirtual_millet'] + $total) : ($user['isvirtual_millet'] - $total);
            $update['isvirtual_millet'] = $isvirtual_millet;
        }
        if ($type == 'inc') {
            //历史总获得谷子数量
            if ($contIsvirtual == 0) {
                $update['his_millet'] = $user['his_millet'] + $total;
            } else {
                $update['his_isvirtual_millet'] = $user['his_isvirtual_millet'] + $total;
            }
        } else {
            if ($tradeType == 'cash') {
                $update['millet_cash_total'] = $user['millet_cash_total'] + $total;
            }
        }
        $typeName = $typeNames[$type];
        if (!empty($update)) {
            $num = Db::name('user')->where(array('user_id' => $user['user_id']))->update($update);
            if (!$num) {
                self::rollback();
                return $this->setError("{$typeName}失败[01]");
            }
            $user = array_merge($user, $update);
            $userService->updateRedis($user['user_id'], $update);
        }
        $log = array_merge($log, array(
            'fre_millet' => $fre_millet,
            'millet' => $millet,
            'total_millet' => $total_millet,
            'isvirtual_millet' => $isvirtual_millet
        ));
        $id = Db::name('millet_log')->insert($log);
        if (!$id) {
            self::rollback();
            return $this->setError("{$typeName}失败[02]");
        }
        $update['log_no'] = $log['log_no'];
        self::commit();
        return $update;
    }

    //主播魅力榜
    protected function updateCharmRank($user, $total)
    {
        $redis = RedisClient::getInstance();
        $hisk = 'rank:charm:history';//总历史榜
        $yk = 'rank:charm:y:' . date('Y');//年榜
        $mk = 'rank:charm:m:' . date('Ym');//月榜
        $wk = 'rank:charm:w:' . DateTools::getWeekNum();//周榜
        $dk = 'rank:charm:d:' . date('Ymd');//日榜
        $userId = $user['user_id'];
        //同步增长
        $redis->zIncrBy($hisk, $total, $userId);
        $redis->zIncrBy($yk, $total, $userId);
        $redis->zIncrBy($mk, $total, $userId);
        $redis->zIncrBy($wk, $total, $userId);
        $redis->zIncrBy($dk, $total, $userId);
    }

    //兑换谷子
    public function exchange($excData)
    {
        $type = $excData['type'];
        $total = $excData['total'];
        if (!in_array($type, ['bean', 'gift', 'millet'])) return $this->setError('兑换类型不正确');
        if ($type == 'millet') return $total;
        if ($type == 'bean') return $total;
    }

    //收益转换成rmb $type 0默认用户
    protected function cashMilletToRmb($millet, $type = 0, $userId = '')
    {
        $cash_setting = config('app.cash_setting');
        //转换率
        $cash_rate = !empty($type) ? $cash_setting['cash_rate'] : $cash_setting['cash_user_rate'];
        if (!empty($userId)) {
            $anchor = Db::name('anchor')->where(['user_id' => $userId])->find();
            if (!empty($anchor) && !empty($anchor['cash_rate']) && $anchor['cash_rate'] != '0.00') $cash_rate = $anchor['cash_rate'];
        }
        //每笔手续费
        $cash_fee = $cash_setting['cash_fee'];
        //税率
        $cash_taxes = $cash_setting['cash_taxes'];
        $taxes = $cash_taxes * $millet;
        $rmb = $millet * $cash_rate;
        return $rmb - $cash_fee - $taxes;
    }

    //补充时间参数
    protected function supplementaryTime(&$data)
    {
        if (empty($data)) return;
        $now = isset($time) ? $time : time();
        $data['year'] = date('Y', $now);
        $data['month'] = date('Ym', $now);
        $data['day'] = date('Ymd', $now);
        $data['fnum'] = DateTools::getFortNum($now);
        $data['week'] = DateTools::getWeekNum($now);
        $relation = Db::name('promotion_relation')->where(['user_id' => $data['user_id']])->find();
        if (!empty($relation)) {
            $data['agent_id'] = $relation['agent_id'];
            $data['promoter_uid'] = $relation['promoter_uid'];
        }
    }

    //查询今日是否已经发放了相关奖励
    public function QueryTodayfind($condition)
    {
        $this->db = Db::name('millet_log');
        $list = $this->db->where($condition)->whereBetweenTime('create_time', date("Y-m-d"))->find();
        return $list;
    }

    public function getOne($condition)
    {
        $this->db = Db::name('millet_log');
        $list = $this->db->where($condition)->find();
        return $list;
    }

    public function commisonCash($inputData)
    {
        $commission_cash_setting = config('giftdistribute.');
        if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
        $userService = new User();
        $user = $userService->getBasicInfo($inputData['user_id']);
        if (empty($user)) return $this->setError('用户不存在');

        $millet = $inputData['millet'];
        $cash_account = $inputData['cash_account'];
        if (!validate_regex($millet, '/^[0-9]+$/') || $millet <= 0) return $this->setError($commission_cash_setting['name'] . '数值不正确');
        if ($commission_cash_setting['commission_cash_on'] != 1) {
            return $this->setError('平台提现功能已禁用');
        }
        $cash_min = $commission_cash_setting['commission_cash_min'];
        if ($millet < $cash_min) return $this->setError('不能低于'.$cash_min. $commission_cash_setting['name']);
        if ($millet > $user['commission_price']) return $this->setError($commission_cash_setting['name'] . '不足');

        $cashAccountInfo = Db::name('cash_account')->where(array('user_id' => $user['user_id'], 'id' => $cash_account, 'delete_time' => null))->find();
        if (!$cashAccountInfo) return $this->setError('提现账号不存在');
        if ($cashAccountInfo['verify_status'] == '2') return $this->setError('提现账号无效');

        $start_time = strtotime(date('Y-m-d'));
        @list($year, $month) = explode('-', date('Y-m-d'));
        $month_start_time = strtotime(date("{$year}-{$month}-01"));
        $month_end_time = mktime(23, 59, 59, abs($month)+1, 0, $year);
        $cash_num = Db::name('millet_commison_cash')
            ->where('user_id', 'eq', $inputData['user_id'])
            ->whereTime('create_time', 'between', [$month_start_time, $month_end_time])
            ->count('id');
        $cash_monthlimit = $commission_cash_setting['commission_cash_monthlimit'];

        if ($cash_num >= $cash_monthlimit) return $this->setError('每月最多可提现'.$cash_monthlimit.'次');

        self::startTrans();
        $data['cash_no'] = get_order_no('distribute');
        $data['user_id'] = $user['user_id'];
        $data['status'] = 'wait';
        $data['millet'] = $millet;
        $data['rmb'] = $this->cashCommisonMilletToRmb($millet);
        $data['aid'] = 1;
        $data['cash_account'] = $cashAccountInfo['id'];
        $data['create_time'] = time();
        $this->supplementaryTime($data);
        $id = Db::name('millet_commison_cash')->insertGetId($data);
        if (!$id) {
            self::rollback();
            return $this->setError('提现失败[01]');
        }
        $commisonPrice = Db::name('bean')->where(['user_id' => $user['user_id']])->dec('commission_price', $millet)->update();
        if (!$commisonPrice) {
            self::rollback();
            return $this->setError('提现失败[01]');
        }
        $addData['to_uid'] = $user['user_id'];
        $addData['cont_uid'] = $user['user_id'];
        $addData['total'] = $millet;
        $addData['trade_type'] = 'cash';
        $addData['type'] = 'exp';
        $addData['trade_no'] = $data['cash_no'];
        $addData['commission_money'] = $millet;
        $addData['create_time'] = time();
        $id = Db::name('gift_commission_log')->insertGetId($addData);
        if (!$id) {
            self::rollback();
            return $this->setError('提现失败[01]');
        }
        self::commit();
        $update['commission_price'] = $user['commission_price'] - $millet;
        $userService->updateRedis($user['user_id'], $update);
        return array(
            'commission_price' => $update['commission_price'],
            'commission_pre_price' => $user['commission_pre_price'],
            'commission_total_price' => $user['commission_total_price'],
            'cash_no' => $data['cash_no']
        );
    }

    protected function cashCommisonMilletToRmb($millet)
    {
        $commission_cash_setting = config('giftdistribute.');
        //每笔手续费
        $cash_fee = isset($commission_cash_setting['commission_cash_fee']) ? $commission_cash_setting['commission_cash_fee'] : 0;
        //税率
        $cash_taxes = $commission_cash_setting['commission_cash_rate'];
        $rmb = $millet * $cash_taxes;
        return $rmb - $cash_fee;
    }
}