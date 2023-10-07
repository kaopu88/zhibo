<?php

namespace app\admin\service;

use bxkj_module\service\KpiQuery;
use think\Db;

class PromoterKpi extends KpiQuery
{
    protected $myPromoter;

    public function __construct()
    {
        parent::__construct();
        $this->myPromoter = new Promoter();
    }

    public function getTotal($get)
    {
        $total = $this->myPromoter->getIndexTotal($get);
        return (int)$total;
    }

    public function getConsList($get, $offset = 0, $length = 20)
    {
        $index = $this->myPromoter->getIndex($get, $offset, $length);
        $this->extendConsByIndex($get, $index);
        return $index;
    }

    protected function extendConsByIndex($get, &$index)
    {
        $agentService = new Agent();
        list($agentIds) = self::getIdsByList($index, 'agent_id', true);
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($index as &$promoter) {
            $promoter['cons'] = 0;
            if ($get['runit'] == 'total') {
                $promoter['cons'] = $promoter['total_cons'];
            } else if (in_array($get['runit'], ['d', 'f', 'm'])) {
                $promoter['cons'] = $this->getConsSum($promoter['user_id'], $get['runit'], $get['rnum'],$promoter['agent_id']);
            }
            $promoter['agent_info'] = self::getItemByList($promoter['agent_id'], $agentList, 'id');
        }
    }

    public function getConsSum($user_id, $unit, $num, $range)
    {
        $prefix = "promoter:{$range}:{$user_id}:cons";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('kpi_cons');
            $this->setTimeRange($db, $unit, $num);
            $db->where([
                ['promoter_uid', 'eq', $user_id],
                ['agent_id', 'eq', $range],
            ]);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }


}