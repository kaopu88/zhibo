<?php

namespace app\push\section;

use bxkj_module\service\Kpi;
use bxkj_module\service\KpiRedis;
use bxkj_module\service\Service;
use bxkj_common\SectionMarkExecuter;
use think\Db;

class RebuildMilletKpi extends SectionMarkExecuter
{
    protected $lockName = 'millet';

    public function complete($data)
    {
        if ($data['status'] == 0) {
        }
    }

    public function handler($length = 10)
    {
        $total = 0;
        $rebuildCode = 1;
        //$where = [['rebuild', 'neq', $rebuildCode], ['id', '<', 1298120 + 1]];
        $where = [['rebuild', 'neq', $rebuildCode]];
        $this->wait();
        $this->lock();
        Service::startTrans();
        $list = Db::name('kpi_millet')->where($where)->order('create_time asc,id asc')->limit($length)->select();
        if (empty($list)) {
            Service::rollback();
            $this->unlock();
            return $this->success(true, $total);
        }
        $ids = [];
        foreach ($list as $item) {
            $ids[] = $item['id'];
        }
        $ids = array_unique($ids);
        if (!empty($ids)) {
            $num = Db::name('kpi_millet')->whereIn('id', $ids)->update([
                'rebuild' => $rebuildCode
            ]);
            if ($num != count($ids)) {
                Service::rollback();
                $this->unlock();
                return $this->success(false, 0);
            }
        }
        Service::commit();
        $this->unlock();
        foreach ($list as $item) {
            $kpi = new Kpi($item['create_time'], true, [
                'prefix' => 'kpi',
                'probability' => 0,
            ]);
            $kpi->milletRebuildByLog($item);
            $total++;
        }
        return $this->success(false, $total);
    }


}