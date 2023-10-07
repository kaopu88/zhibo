<?php

namespace app\common\command;

use bxkj_common\DateTools;
use bxkj_module\service\AgentPrice;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\facade\Config;
use think\facade\Env;
use think\console\input\Argument;


class Settlement extends Command
{
    protected function configure()
    {

        // 指令配置
        $this->setName('bxkj_settlement')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart")
            //->addOption('city', null, Option::VALUE_REQUIRED, 'city name')
            ->setDescription('settlement service run');
        // 设置参数
    }

    protected function execute(Input $input, Output $output)
    {
        $all_agent = Db::name('agent')->field('id, total_price, cash_type, cash_proportion')->where(['status' => '1'])->select();
        if (empty($all_agent)) return;
        $cashType = config('app.cash_setting.cash_type');
        Db::startTrans();
        $day= date('Ymd', strtotime("-1 day"));
        try {
            foreach ($all_agent as $key => $value) {
                $anchorMillet = 0;
                $deductionMillet = 0;
                $rmb = 0;
                $cashProportion = $value['cash_proportion'] ? $value['cash_proportion'] : config('app.cash_setting.cash_proportion'); //结算比例
                //平台结算
                if (($value['cash_type'] == 1) || (!empty($cashType) && $value['cash_type'] != 2)) {
                    $agentmillet = $this->getPlatformMilletSum($value['id'], 'd', $day);
                    if (!empty($agentmillet)) {
                        foreach ($agentmillet as $k => $v) {
                            $anchorMillet = $anchorMillet + $v['total_millet'];
                            $cash_rate = config('app.cash_setting.cash_rate');
                            if (!empty($v['cash_rate']) && $v['cash_rate'] != '0.00') $cash_rate = $v['cash_rate'];
                            $rmb =  $rmb + ($v['total_millet'] * ($cashProportion - $cash_rate));
                            $deductionMillet = $deductionMillet + ($v['total_millet'] * $cash_rate);
                        }
                    }
                }

                //公会结算
                if (($value['cash_type'] == 2) || ($value['cash_type'] != 1 && $cashType == 0)) {
                    $cashMilletType = config('app.cash_setting.cash_millet_type') ? config('app.cash_setting.cash_millet_type') : 0;
                    if ($cashMilletType == 0) $anchorMillet = $this->getConsSum($value['id'], 'd', $day);
                    if ($cashMilletType == 1) $anchorMillet = $this->getMilletSum($value['id'], 'd', $day);
                    $rmb = $anchorMillet * $cashProportion;
                }

                $res = $this->applyData($value, $anchorMillet, $rmb, $deductionMillet, $cashProportion);

            }
        } catch (Exception $e) {
            Db::rollback();
            return;
        }

        Db::commit();
    }

    protected function applyData($agent_info, $millet = 0, $rmb = 0, $deductionMillet = 0, $cashProportion = 0)
    {
        $cash_no = get_order_no('agent_cash');
        $data['cash_no'] = $cash_no;
        $data['agent_id'] = $agent_info['id'];
        $data['millet'] = $millet;
        $data['deduction_millet'] = $deductionMillet;
        $data['old_rmb'] = $millet * $cashProportion;
        $data['rmb'] = round($rmb, 2);
        $data['audit_status'] = 1;
        $now = time();
        $data['year'] = date('Y', $now);
        $data['month'] = date('Ym', $now);
        $data['day'] = date('Ymd', $now);
        $data['fnum'] = DateTools::getFortNum($now);
        $data['week'] = DateTools::getWeekNum($now);
        $data['create_time'] = $now;
        $res = Db::name('agent_settlement')->insertGetId($data);
        $priceLogService = new AgentPrice();
        $res_og = $priceLogService->inc(['total' => $rmb, 'agent_id' => $agent_info['id'], 'trade_type' => 'settlement', 'trade_no' => $cash_no]);
        return true;
    }

    protected function getPlatformMilletSum($agent_id, $unit, $num)
    {
        $prifit = config('app.live_setting.bag_prifit_status');
        $db = Db::name('anchor')->alias('anchor');
        $db->join('bx_kpi_millet kpi', 'kpi.get_uid=anchor.user_id', 'LEFT');
        $db->where('kpi.day', $num)->field('sum(millet) as total_millet, anchor.cash_rate, anchor.user_id');
        $data[] = ['kpi.agent_id', 'eq', $agent_id];
        if (empty($prifit)) {
            $data[] = ['kpi.is_prifit', 'eq', 0];
        }
        $res = $db->where($data)->group('kpi.get_uid')->select();
        return $res?: 0;
    }

    protected function getMilletSum($agent_id, $unit, $num)
    {
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
        return $sum;
    }

    protected function getConsSum($agent_id, $unit, $num, $range = 'all')
    {

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
        return $sum;
    }

    protected function getCashMilletSum($agent_id, $unit, $num, $type = 'wait')
    {

        $db = Db::name('millet_cash');
        $this->setTimeRange($db, $unit, $num);

        $data = [
            ['agent_id', 'eq', $agent_id],
            ['status', 'eq', $type],
        ];

        $db->where($data);
        $sum = $db->sum('millet');
        return $sum;
    }

    protected function setTimeRange(&$db, $unit, $num)
    {
        $num = $num ? str_replace('-', '', $num) : '';
        if (!empty($num)) {
            if ($unit == 'm') {
                $db->where('month', $num);
            } else if ($unit == 'f') {
                $db->where('fnum', $num);
            } else if ($unit == 'd') {
                $db->where('day', $num);
            } else if ($unit == 'w') {
                $db->where('week', $num);
            }
        }
    }
}
