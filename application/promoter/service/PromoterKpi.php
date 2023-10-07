<?php

namespace app\promoter\service;

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
            } else if ($get['runit'] == 'during') {
                $promoter['cons'] = $this->getConsSum($promoter['user_id'], 'f', '2019072',$promoter['agent_id'])
                    - $this->getConsSum($promoter['user_id'], 'd', '2019-07-29',$promoter['agent_id'])
                    - $this->getConsSum($promoter['user_id'], 'd', '2019-07-30',$promoter['agent_id'])
                    - $this->getConsSum($promoter['user_id'], 'd', '2019-07-31',$promoter['agent_id']);
            } else if (in_array($get['runit'], ['d', 'w', 'f', 'm'])) {
                $promoter['cons'] = $this->getConsSum($promoter['user_id'], $get['runit'], $get['rnum'],$promoter['agent_id']);
            }
            $promoter['agent_info'] = self::getItemByList($promoter['agent_id'], $agentList, 'id');
        }
    }

    public function getConsSum($user_id, $unit, $num, $range)
    {
        $aid = AID;
        $is_root = Db::name('agent_admin')->where('id', $aid)->value('is_root');
        $prefix = $is_root ? "promoter:{$range}:{$user_id}:cons" : "promoters:{$range}:{$aid}:cons";
        $promoter_uids = explode(',', $user_id);
        if (count($promoter_uids) > 1)
        {
            $where = [['promoter_uid', 'in', $user_id],['agent_id', 'eq', $range]];
        }else{
            $where = [['promoter_uid', 'eq', $user_id],['agent_id', 'eq', $range]];
        }
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('kpi_cons');
            $this->setTimeRange($db, $unit, $num);
            $db->where($where);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }


    public function getRechargeSum($agent_id, $unit, $num)
    {
        $prefix = "promoter:all:{$agent_id}:recharge";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum) || $sum == 0) {
            $pay_methods = enum_array("pay_methods");
            $pay_methods = array_column($pay_methods,'value');
            $pay_methods = array_merge(array_diff($pay_methods, array('system_free')));
            $db = Db::name('recharge_order');
            $db->alias('recharge');
            $db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
            $db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
            $db->field('user.user_id');
            $db->field('recharge.id,recharge.user_id,recharge.pay_method,recharge.pay_status,recharge.pay_time,recharge.create_time,recharge.total_fee');
            Agent::agentWhere($where, ['agent_id' => $agent_id], 'pr.');
            $this->setPayTimeRange($db, $unit, $num);
            $db->where($where);
            $db->whereIn('pay_method',$pay_methods);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }


    public static function setPayTimeRange(&$db, $unit, $num,$range = "")
    {
        $num = $num ? str_replace('-', '', $num) : '';
        if (!empty($num)) {
            if($unit == 'd'){
                $db->whereBetweenTime('pay_time',$num);
            }else if($unit == 'm'){
                $db->whereTime('pay_time','month');
            }

        }
    }


}