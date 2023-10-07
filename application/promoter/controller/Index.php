<?php

namespace app\promoter\controller;

use app\promoter\service\AgentKpi;
use app\promoter\service\Trend;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\facade\Request;

class Index extends Controller
{
    public function index()
    {
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $this->assign('get', $get);
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
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
        }

        return json_success([
            'cons' => $consNum,
            'millet' => $milletNum,
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

            $member = $this->admin['promoter_uid'] ? $this->admin['promoter_uid'] : AGENT_ID;

            $types = array(
                array('mark' => "promoters:cons:".AGENT_ID, 'name' => '客户消费额('.APP_BEAN_NAME.')', 'member' => $member),
                array('mark' => "promoters:recharge:".AGENT_ID, 'name' => '充值金额', 'member' => $member),
                array('mark' => "promoters:millet:".AGENT_ID, 'name' => APP_MILLET_NAME, 'member' => $member),
            );

            $trend = new Trend('');
            $data = $trend->getSeriesData($types, $start, $end, $unit);
            $data['title'] = DateTools::getRangeTitle($start, $end, $unit) . '数据';
            $this->success('获取成功', $data);
        }
    }

}
