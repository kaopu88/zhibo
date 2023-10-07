<?php

namespace app\promoter\service;

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
        $todayDurationNum = round($todayDurationNum / 60);

        //昨日数据
        $yesterdayConsNum = $agentKpi->getConsSum(AGENT_ID, 'd', $yesterday);
        $yesterdayActiveNum = $redis->zScore("kpi:agent:all:active:d:{$yesterday}", AGENT_ID);
        $yesterdayMilletNum = $agentKpi->getMilletSum(AGENT_ID, 'd', $yesterday);
        $yesterdayPullUserNum = $redis->zScore("kpi:agent:all:fans:d:{$yesterday}", AGENT_ID);
        $yesterdayDurationNum = $redis->zScore("kpi:agent:all:duration:d:{$yesterday}", AGENT_ID);
        $yesterdayDurationNum = round($yesterdayDurationNum / 60);

        //本月数据
        $monthConsNum = $agentKpi->getConsSum(AGENT_ID, 'm', $month);
        $monthActiveNum = $redis->zScore("kpi:agent:all:active:m:{$month}", AGENT_ID);
        $monthMilletNum = $agentKpi->getMilletSum(AGENT_ID, 'm', $month);
        $monthPullUserNum = $redis->zScore("kpi:agent:all:fans:m:{$month}", AGENT_ID);
        $monthDurationNum = $redis->zScore("kpi:agent:all:duration:m:{$month}", AGENT_ID);
        $monthDurationNum = round($monthDurationNum / 60);

        //历史数据
        $agentInfo = Db::name('agent')->where('id', AGENT_ID)->find();
        $historyConsNum = $agentInfo['total_cons'];
        $historyActiveNum = 0;
        $historyMilletNum = $agentInfo['total_millet'];
        $historyPullUserNum = $agentInfo['total_fans'];
        $historyDurationNum = round($agentInfo['total_duration'] / 60);

        $data = array(
            'today' => array(
                'cons_num' => $todayConsNum ? $todayConsNum : 0,
                'active_num' => $todayActiveNum ? $todayActiveNum : 0,
                'millet_num' => $todayMilletNum ? $todayMilletNum : 0,
                'pull_user_num' => $todayPullUserNum ? $todayPullUserNum : 0,
                'duration_num' => $todayDurationNum ? $todayDurationNum : 0,
            ),
            'yesterday' => array(
                'cons_num' => $yesterdayConsNum ? $yesterdayConsNum : 0,
                'active_num' => $yesterdayActiveNum ? $yesterdayActiveNum : 0,
                'millet_num' => $yesterdayMilletNum ? $yesterdayMilletNum : 0,
                'pull_user_num' => $yesterdayPullUserNum ? $yesterdayPullUserNum : 0,
                'duration_num' => $yesterdayDurationNum ? $yesterdayDurationNum : 0,
            ),
            'month' => array(
                'cons_num' => $monthConsNum ? $monthConsNum : 0,
                'active_num' => $monthActiveNum ? $monthActiveNum : 0,
                'millet_num' => $monthMilletNum ? $monthMilletNum : 0,
                'pull_user_num' => $monthPullUserNum ? $monthPullUserNum : 0,
                'duration_num' => $monthDurationNum ? $monthDurationNum : 0,
            ),
            'history' => array(
                'cons_num' => $historyConsNum ? $historyConsNum : 0,
                'active_num' => $historyActiveNum ? $historyActiveNum : 0,
                'millet_num' => $historyMilletNum ? $historyMilletNum : 0,
                'pull_user_num' => $historyPullUserNum ? $historyPullUserNum : 0,
                'duration_num' => $historyDurationNum ? $historyDurationNum : 0
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
            $db->where([
                ['agent_id', 'eq', $agent_id],
            ]);
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
            $db->where([
                ['agent_id', 'eq', $agent_id],
            ]);
            $sum = $db->sum('millet');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }


}