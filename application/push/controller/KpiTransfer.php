<?php

namespace app\push\controller;

use think\Db;

class KpiTransfer extends Api
{

    //异步处理单个业绩转移任务
    public function handler()
    {
        $this->persistent();
        $id = input('id');
        if (empty($id)) return json_error('请选择记录');
        $log = Db::name('kpi_transfer_log')->where('id', $id)->find();
        if (empty($log)) return json_error('记录不存在');
        if ($log['status'] != '0') return json_error('记录已处理');
        $startTime = $log['start_time'];
        $endTime = $log['end_time'];
        $user = [
            'user_id' => $log['user_id'],
            'agent_id' => $log['agent_id'],
            'promoter_uid' => 0,
        ];
        $kpiTransfer = new \bxkj_module\service\KpiTransfer();
        $transferRes = $kpiTransfer
            ->timeLimit($startTime, $endTime)
            ->setUser($user)
            ->setReceiver($log['agent_id'], $log['promoter_uid'])
            ->transfer();

        if ($transferRes) {
            Db::name('kpi_transfer_log')->where('id', $id)->update(['status' => '1']);
        }
        return json_success(1, 'ok');
    }

    //处理所有未完成的业绩转移任务
    public function handler_logs()
    {
        $this->persistent();
        $logs = Db::name('kpi_transfer_log')->where('status', '0')->select();
        $total = 0;
        foreach ($logs as $log) {
            $startTime = $log['start_time'];
            $endTime = $log['end_time'];
            $user = [
                'user_id' => $log['user_id'],
                'agent_id' => $log['old_agent_id'],
                'promoter_uid' => $log['old_promoter_uid'],
            ];
            $kpiTransfer = new \bxkj_module\service\KpiTransfer();
            $transferRes = $kpiTransfer->timeLimit($startTime, $endTime)->setUser($user)->setReceiver($log['agent_id'], $log['promoter_uid'])->transfer();
            if ($transferRes) {
                $total++;
                Db::name('kpi_transfer_log')->where('id', $log['id'])->update(['status' => '1']);
            }
        }
        return json_success($total, 'ok');
    }


}
