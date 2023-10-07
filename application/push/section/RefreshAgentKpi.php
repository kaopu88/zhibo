<?php

namespace app\push\section;

use bxkj_module\service\KpiRedis;
use bxkj_module\service\Service;
use bxkj_common\SectionMarkExecuter;
use think\Db;

class RefreshAgentKpi extends SectionMarkExecuter
{
    protected $lockName='refresh_agent';

    public function complete($data)
    {
        if ($data['status'] == 0) {
        }
    }

    public function handler($length = 10)
    {
        $total = 0;
        $date = date('Ymd');
        $where = [['refresh_date', 'neq', $date]];
        $this->wait();
        $this->lock();
        Service::startTrans();
        $list = Db::name('agent')->where($where)->order('id asc')->limit($length)->select();
        if (empty($list)) {
            Service::rollback();
            $this->unlock();
            return $this->success(true, $total);
        }
        $agentIds = [];
        foreach ($list as $item) {
            $agentIds[] = $item['id'];
        }
        $agentIds = array_unique($agentIds);
        if (!empty($agentIds)) {
            Db::name('agent')->whereIn('id', $agentIds)->update([
                'refresh_date' => $date
            ]);
        }
        Service::commit();
        $this->unlock();
        foreach ($list as $item) {
            if (empty($item['delete_time'])) {
                //客消
                $kpiRedis = new KpiRedis();
                $userId = $item['id'];
                $userType = 'agent';
                $indicator = 'cons';
                $agentMark = $item['pid'] ? $item['pid'] : '';
                $lastDate = $item['refresh_date'] ? $item['refresh_date'] : date('Ymd', time() - 86400);
                $res3 = $kpiRedis->setUser($userType, $userId)->setAgent('all')->setIndicator($indicator)->refresh($lastDate);
                if (!empty($agentMark) && $res3) {
                    $kpiRedis->setUser($userType, $userId)->setAgent($agentMark)->setIndicator($indicator)->refresh($lastDate);
                }
                //谷子
                $kpiRedis2 = new KpiRedis();
                $indicator2 = 'millet';
                $res4 = $kpiRedis2->setUser($userType, $userId)->setAgent('all')->setIndicator($indicator2)->refresh($lastDate);
                if (!empty($agentMark) && $res4) {
                    $kpiRedis2->setUser($userType, $userId)->setAgent($agentMark)->setIndicator($indicator2)->refresh($lastDate);
                }
            }
            $total++;
        }
        return $this->success(false, $total);
    }


}