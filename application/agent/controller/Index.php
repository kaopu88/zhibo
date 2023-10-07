<?php

namespace app\agent\controller;

use app\agent\service\AgentKpi;
use app\agent\service\Trend;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\facade\Request;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $this->assign('get', $get);
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $where = 'an.status = 1 and an.visible in(0,2)';
        $admin_notice = Db::name('admin_notice')->field('an.*,da.username')->alias('an')->join('__ADMIN__ da', 'an.aid=da.id')->where($where)->order('an.sort desc,an.create_time desc')->limit(6)->select();
        $agentInfo = Db::name('agent')->where('id', AGENT_ID)->find();
        $this->assign('agent_info', $agentInfo);
        $cashType = config('app.cash_setting');
        $this->assign('cash_type', $cashType['cash_type'] ? $cashType['cash_type'] : 0);
        $this->assign('admin_notice', $admin_notice);
        return $this->fetch();
    }

    public function get_summary_data()
    {
        $agentKpi = new AgentKpi();
        $data = $agentKpi->getSummaryData();
        $this->success('获取成功', $data);
    }

    public function get_history_data()
    {
        $agentKpi = new AgentKpi();
        $runit = input('runit');
        $rnum = input('rnum');
        if (empty($runit) || empty($rnum)) return json_error('参数不全');
        $rnum = str_replace('-', '', $rnum);
        $redis = RedisClient::getInstance();
        if ($runit == 'during')
        {
            $consNum = $agentKpi->getConsSum(AGENT_ID, 'f', '2019072')
                - $agentKpi->getConsSum(AGENT_ID, 'd', '20190729')
                - $agentKpi->getConsSum(AGENT_ID, 'd', '20190730')
                - $agentKpi->getConsSum(AGENT_ID, 'd', '20190731');
            $milletNum = $agentKpi->getMilletSum(AGENT_ID, 'f', '2019072')
                - $agentKpi->getMilletSum(AGENT_ID, 'd', '20190729')
                - $agentKpi->getMilletSum(AGENT_ID, 'd', '20190730')
                - $agentKpi->getMilletSum(AGENT_ID, 'd', '20190731');
        }else{
            $consNum = $agentKpi->getConsSum(AGENT_ID, $runit, $rnum);
            $activeNum = $redis->zScore("kpi:agent:all:active:{$runit}:{$rnum}", AGENT_ID);
            $milletNum = $agentKpi->getMilletSum(AGENT_ID, $runit, $rnum);
            $pullUserNum = $redis->zScore("kpi:agent:all:fans:{$runit}:{$rnum}", AGENT_ID);
            $durationNum = $redis->zScore("kpi:agent:all:duration:{$runit}:{$rnum}", AGENT_ID);
            $durationNum = round($durationNum / 60);
            $rechargeNum = $agentKpi->getRechargeSum(AGENT_ID,$runit,$rnum);
        }

        return json_success([
            'cons' => $consNum,
            'millet' => $milletNum,
            'recharge' => $rechargeNum,
            'active' => (int)$activeNum,
            'pull_user' => (int)$pullUserNum,
            'duration' => (int)$durationNum
        ], 'success');
    }

    public function get_cons_trend()
    {
        if (Request::isPost()) {
            $start = input('start');
            $end = input('end');
            $unit = input('unit');

            if (empty($start) || empty($end) || empty($unit))
                $this->error('请检查参数是否正确');
            if (DateTools::strToTime($start) > DateTools::strToTime($end))
                $this->error('时间段设置不正确，可能是结束时间小于开始时间');

            $types = array(
                array('mark' => "agent:cons:all", 'name' => '客户消费额('.APP_BEAN_NAME.')', 'member' => AGENT_ID),
                array('mark' => "agent:recharge:all", 'name' => '充值金额', 'member' => AGENT_ID),
                array('mark' => "agent:millet:all", 'name' => APP_MILLET_NAME, 'member' => AGENT_ID),
            );

            $trend = new Trend('');
            $data = $trend->getSeriesData($types, $start, $end, $unit);
            $data['title'] = DateTools::getRangeTitle($start, $end, $unit) . '数据';
            $this->success('获取成功', $data);
        }
    }

}
