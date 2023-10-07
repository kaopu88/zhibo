<?php

namespace app\agent\service;

use bxkj_module\service\KpiQuery;
use bxkj_common\RedisClient;
use think\Db;

class AgentKpi extends KpiQuery
{
    public function getSummaryData()
    {
        $now = time();
        $today = date('Ymd', $now);
        $yesterday = date('Ymd', $now - (3600 * 24));
        $month = date('Ym', $now);
        $agentKpi = new AgentKpi();
        $redis = RedisClient::getInstance();
        //今日数据
        $todayConsNum = $agentKpi->getConsSum(AGENT_ID, 'd', $today);
        $todayActiveNum = $redis->zScore("kpi:agent:all:active:d:{$today}", AGENT_ID);
        $todayMilletNum = $agentKpi->getMilletSum(AGENT_ID, 'd', $today);
        $todayPullUserNum = $redis->zScore("kpi:agent:all:fans:d:{$today}", AGENT_ID);
        $todayDurationNum = $redis->zScore("kpi:agent:all:duration:d:{$today}", AGENT_ID);
        //$todayDurationNum = round($todayDurationNum / 60);
        $todayRechargeNum = $this->getRechargeSum(AGENT_ID,'d',$today);
        //昨日数据
        $yesterdayConsNum = $agentKpi->getConsSum(AGENT_ID, 'd', $yesterday);
        $yesterdayActiveNum = $redis->zScore("kpi:agent:all:active:d:{$yesterday}", AGENT_ID);
        $yesterdayMilletNum = $agentKpi->getMilletSum(AGENT_ID, 'd', $yesterday);
        $yesterdayPullUserNum = $redis->zScore("kpi:agent:all:fans:d:{$yesterday}", AGENT_ID);
        $yesterdayDurationNum = $redis->zScore("kpi:agent:all:duration:d:{$yesterday}", AGENT_ID);
        //$yesterdayDurationNum = round($yesterdayDurationNum / 60);
        $yesterdayRechargeNum = $this->getRechargeSum(AGENT_ID,'d',$yesterday);

        //本月数据
        $monthConsNum = $agentKpi->getConsSum(AGENT_ID, 'm', $month);
        $monthActiveNum = $redis->zScore("kpi:agent:all:active:m:{$month}", AGENT_ID);
        $monthMilletNum = $agentKpi->getMilletSum(AGENT_ID, 'm', $month);
        $monthPullUserNum = $redis->zScore("kpi:agent:all:fans:m:{$month}", AGENT_ID);
        $monthDurationNum = $redis->zScore("kpi:agent:all:duration:m:{$month}", AGENT_ID);
        //$monthDurationNum = round($monthDurationNum / 60);
        $monthRechargeNum = $this->getRechargeSum(AGENT_ID,'m',$month);

        //历史数据
        $agentInfo = Db::name('agent')->where('id', AGENT_ID)->find();
        $historyConsNum = $agentInfo['total_cons'];
        $historyActiveNum = 0;
        $historyMilletNum = $agentInfo['total_millet'];
        $historyPullUserNum = $agentInfo['total_fans'];
        $historyDurationNum = round($agentInfo['total_duration'] / 60);
        //$historyRechargeNum = $kpiConsService->getSum('month');

        $cashType = config('app.cash_setting.cash_type');
       // if ($agentInfo['cash_type'] == 2 || !empty($cashType) ) {
        if ((empty($cashType) && $agentInfo['cash_type'] == 1) || (!empty($cashType) && $agentInfo['cash_type'] != 2)) {
            $todayMilletCashNum = $agentKpi->getCashMilletSum(AGENT_ID, 'd', $today, 'success');
            $yesterdayMilletCashNum = $agentKpi->getCashMilletSum(AGENT_ID, 'd', $yesterday, 'success');
            $monthMilletCashNum = $agentKpi->getCashMilletSum(AGENT_ID, 'm', $month, 'success');
            $todayNotMilletCashNum = $agentKpi->getCashMilletSum(AGENT_ID, 'd', $today);
            $yesterdayNotMilletCashNum = $agentKpi->getCashMilletSum(AGENT_ID, 'd', $yesterday);
            $monthNotMilletCashNum = $agentKpi->getCashMilletSum(AGENT_ID, 'm', $month);
        }

        $data = array(
            'today' => array(
                'cons_num' => $todayConsNum ? $todayConsNum : 0,
                'active_num' => $todayActiveNum ? $todayActiveNum : 0,
                'millet_num' => $todayMilletNum ? $todayMilletNum : 0,
                'pull_user_num' => $todayPullUserNum ? $todayPullUserNum : 0,
                'duration_num' => $todayDurationNum ? time_str($todayDurationNum,'i') : 0,
                'duration_str' => $todayDurationNum ? time_str($todayDurationNum) : 0,
                'recharge_num' => $todayRechargeNum ? $todayRechargeNum : 0,
                'cash_millet_num' => isset($todayMilletCashNum) ? $todayMilletCashNum : 0,
                'notcash_millet_num' => isset($todayNotMilletCashNum) ? $todayNotMilletCashNum : 0,
            ),
            'yesterday' => array(
                'cons_num' => $yesterdayConsNum ? $yesterdayConsNum : 0,
                'active_num' => $yesterdayActiveNum ? $yesterdayActiveNum : 0,
                'millet_num' => $yesterdayMilletNum ? $yesterdayMilletNum : 0,
                'pull_user_num' => $yesterdayPullUserNum ? $yesterdayPullUserNum : 0,
                'duration_num' => $yesterdayDurationNum ? time_str($yesterdayDurationNum, 'i') : 0,
                'duration_str' => $yesterdayDurationNum ? time_str($yesterdayDurationNum) : 0,
                'recharge_num' => $yesterdayRechargeNum ? $yesterdayRechargeNum : 0,
                'cash_millet_num' => isset($yesterdayMilletCashNum) ? $yesterdayMilletCashNum : 0,
                'notcash_millet_num' => isset($yesterdayNotMilletCashNum) ? $yesterdayNotMilletCashNum : 0,
            ),
            'month' => array(
                'cons_num' => $monthConsNum ? $monthConsNum : 0,
                'active_num' => $monthActiveNum ? $monthActiveNum : 0,
                'millet_num' => $monthMilletNum ? $monthMilletNum : 0,
                'pull_user_num' => $monthPullUserNum ? $monthPullUserNum : 0,
                'duration_num' => $monthDurationNum ? time_str($monthDurationNum,'i') : 0,
                'duration_str' => $monthDurationNum ? time_str($monthDurationNum) : 0,
                'recharge_num' => $monthRechargeNum ? $monthRechargeNum : 0,
                'cash_millet_num' => isset($monthMilletCashNum) ? $monthMilletCashNum : 0,
                'notcash_millet_num' => isset($monthNotMilletCashNum) ? $monthNotMilletCashNum : 0,
            ),
            'history' => array(
                'cons_num' => $historyConsNum ? $historyConsNum : 0,
                'active_num' => $historyActiveNum ? $historyActiveNum : 0,
                'millet_num' => $historyMilletNum ? $historyMilletNum : 0,
                'pull_user_num' => $historyPullUserNum ? $historyPullUserNum : 0,
                'duration_num' => $historyDurationNum ? time_str($historyDurationNum) : 0,
                'recharge_num' => $historyRechargeNum ? $historyRechargeNum : 0,
                'cash_millet_num' => 0,
                'notcash_millet_num' => 0,
            )
        );
        return $data;
    }

    public function getConsSum($agent_id, $unit, $num, $range = 'all')
    {
        $prefix = "agent:{$range}:{$agent_id}:cons";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('kpi_cons');
            $this->setTimeRange($db, $unit, $num);
            $prifit = config('app.live_setting.bag_prifit_status');
            $data = [
                ['agent_id', 'eq', $agent_id],
            ];
            if (empty($prifit)) {
                $data = [
                    ['agent_id', 'eq', $agent_id],
                    ['is_prifit', 'eq', 0],
                ];
            }
            $db->where($data);

            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public function getMilletSum($agent_id, $unit, $num)
    {
        $prefix = "agent:all:{$agent_id}:millet";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('kpi_millet');
            $this->setTimeRange($db, $unit, $num);
            $prifit = config('app.live_setting.bag_prifit_status');
            $data = [
                ['agent_id', 'eq', $agent_id],
            ];
            if (empty($prifit)) {
                $data = [
                    ['agent_id', 'eq', $agent_id],
                    ['is_prifit', 'eq', 0],
                ];
            }
            $db->where($data);
            $sum = $db->sum('millet');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public function getRechargeSum($agent_id, $unit, $num)
    {
        $prefix = "agent:all:{$agent_id}:recharge";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum) || $sum == 0) {
            $pay_methods = enum_array("pay_methods");
            $pay_methods = array_column($pay_methods,'value');
            $pay_methods = array_merge(array_diff($pay_methods, array('system_free')));
            $db = Db::name('recharge_order');
            $db->alias('recharge');
            $db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
            $db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
            $db->field('user.user_id');
            $db->field('recharge.id,recharge.user_id,recharge.pay_method,recharge.pay_status,recharge.pay_time,recharge.create_time,recharge.total_fee');
            $where[] = ['recharge.pay_status', '=', '1'];
            $where[] = ['recharge.isvirtual', '=', '0'];
            Agent::agentWhere($where, ['agent_id' => $agent_id], 'pr.');
            $this->setPayTimeRange($db, $unit, $num);
            $db->where($where);
            $db->whereIn('pay_method',$pay_methods);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public static function setPayTimeRange(&$db, $unit, $num,$range = "")
    {
        $num = $num ? str_replace('-', '', $num) : '';
        if (!empty($num)) {
            if($unit == 'd'){
                $db->whereBetweenTime('pay_time',$num);
            }else if($unit == 'm'){
                $db->whereTime('pay_time','month');
            }

        }
    }

    public function getCashMilletSum($agent_id, $unit, $num, $type = 'wait')
    {
        $prefix = "agent:all:{$agent_id}:{$type}cash_millet";
        $sum = self::getCache($prefix, $unit, $num);

        if (!isset($sum)) {
            $db = Db::name('millet_cash');
            $this->setTimeRange($db, $unit, $num);

            $data = [
                ['agent_id', 'eq', $agent_id],
                ['status', 'eq', $type],
            ];

            $db->where($data);
            $sum = $db->sum('millet');
            self::setCache($prefix, $unit, $num, $sum);
        }

        return $sum;
    }


}