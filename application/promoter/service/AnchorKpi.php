<?php

namespace app\promoter\service;

use bxkj_module\service\KpiQuery;
use think\Db;

class AnchorKpi extends KpiQuery
{
    protected $myAnchor;

    public function __construct()
    {
        parent::__construct();
        $this->myAnchor = new Anchor();
    }

    public function getTotal($get)
    {
        $total = $this->myAnchor->getIndexTotal($get);
        return (int)$total;
    }

    public function getMilletList($get, $offset = 0, $length = 20)
    {
        $index = $this->myAnchor->getIndex($get, $offset, $length);
        $this->extendMilletByIndex($get, $index);
        return $index;
    }

    protected function extendMilletByIndex($get, &$index)
    {
        list($agentIds) = self::getIdsByList($index, 'agent_id', true);
        $agentService = new Agent();
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($index as &$anchor) {
            $anchor['millet'] = 0;
            if ($get['runit'] == 'total') {
            } else if (in_array($get['runit'], ['d', 'f', 'm'])) {
                $anchor['millet'] = $this->getMilletSum($anchor['agent_id'], $anchor['user_id'], $get['runit'], $get['rnum']);
            }
            $anchor['agent_info'] = self::getItemByList($anchor['agent_id'], $agentList, 'id');
        }
    }

    public function getMilletSum($agent_id, $user_id, $unit, $num)
    {
        $prefix = "anchor:{$agent_id}:{$user_id}:millet";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('kpi_millet');
            $this->setTimeRange($db, $unit, $num);
            $db->where([
                ['get_uid', 'eq', $user_id],
                ['agent_id', 'eq', $agent_id],
            ]);
            $sum = $db->sum('millet');
            self::setCache($prefix, $unit, $num, $sum);
        }
        $num = trim($num ? str_replace('-', '', $num) : '');
        if ($user_id == '72082') {
            if ($unit == 'm' && $num == '201810') {
                $sum -= 309730;
            } else if ($unit == 'f' && $num == '2018101') {
                $sum -= 128030;
            } else if ($unit == 'f' && $num == '2018102') {
                $sum -= 181700;
            }
        }
        return $sum;
    }


}