<?php

namespace app\push\section;

use bxkj_module\service\KpiRedis;
use bxkj_module\service\Service;
use bxkj_common\SectionMarkExecuter;
use think\Db;

class RefreshPromoterKpi extends SectionMarkExecuter
{

    protected $lockName = 'refresh_promoter';

    public function complete($data)
    {
        if ($data['status'] == 0) {
            $this->manager->goOn(PUSH_URL . '/refresh_kpi/refresh_anchor_kpi?safety=1');
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
        $list = Db::name('promoter')->where($where)->order('id asc')->limit($length)->select();
        if (empty($list)) {
            Service::rollback();
            $this->unlock();
            return $this->success(true, $total);
        }
        $promoterIds = [];
        foreach ($list as $item) {
            $promoterIds[] = $item['id'];
        }
        $promoterIds = array_unique($promoterIds);
        if (!empty($promoterIds)) {
            Db::name('promoter')->whereIn('id', $promoterIds)->update([
                'refresh_date' => $date
            ]);
        }
        Service::commit();
        $this->unlock();
        foreach ($list as $item) {
            if (empty($item['delete_time'])) {
                $kpiRedis = new KpiRedis();
                $userId = $item['user_id'];
                $userType = 'promoter';
                $indicator = 'cons';
                $agentMark = $item['agent_id'];
                $lastDate = $item['refresh_date'] ? $item['refresh_date'] : date('Ymd', time() - 86400);
                $res3 = $kpiRedis->setUser($userType, $userId)->setAgent('all')->setIndicator($indicator)->refresh($lastDate);
                if (!empty($agentMark) && $res3) {
                    $kpiRedis->setUser($userType, $userId)->setAgent($agentMark)->setIndicator($indicator)->refresh($lastDate);
                }
            }
            $total++;
        }
        return $this->success(false, $total);
    }


}