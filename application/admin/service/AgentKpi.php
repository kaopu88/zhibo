<?php

namespace app\admin\service;

use bxkj_module\service\KpiQuery;
use think\Db;

class AgentKpi extends KpiQuery
{
    protected $myAgent;

    public function __construct()
    {
        parent::__construct();
        $this->myAgent = new Agent();
    }

    public function getTotal($get)
    {
        $total = $this->myAgent->getIndexTotal($get);
        return (int)$total;
    }

    public function getConsList($get, $offset = 0, $length = 20)
    {
        $index = $this->myAgent->getIndex($get, $offset, $length);
        $this->extendConsByIndex($get, $index);
        $this->extendMilletByIndex($get, $index);
        $this->extendUnallocatedConsByIndex($get, $index);
        $this->extendCashMilletByIndex($get, $index);
        return $index;
    }

    public function getMilletList($get, $offset = 0, $length = 20)
    {
        $index = $this->myAgent->getIndex($get, $offset, $length);
        $this->extendMilletByIndex($get, $index);
        return $index;
    }

    protected function extendConsByIndex($get, &$index)
    {
        foreach ($index as &$agent) {
            $agent['cons'] = 0;
            if ($get['runit'] == 'total') {
                $agent['cons'] = $agent['total_cons'];
            } else if ($get['runit'] == 'during') {
                $agent['cons'] = $this->getConsSum($agent['id'], 'f', '2019072')
                    - $this->getConsSum($agent['id'], 'd', '2019-07-29')
                    - $this->getConsSum($agent['id'], 'd', '2019-07-30')
                    - $this->getConsSum($agent['id'], 'd', '2019-07-31');
            } else if (in_array($get['runit'], ['d', 'w', 'f', 'm'])) {
                $agent['cons'] = $this->getConsSum($agent['id'], $get['runit'], $get['rnum']);
                $agent['recharge_num'] = $this->getRecharge($agent['id'], $get['runit'], $get['rnum']);
                $agent['apple_recharge_num'] = $this->getAppleRrecharege($agent['id'], $get['runit'], $get['rnum']);
            }
        }
    }

    protected function extendMilletByIndex($get, &$index)
    {
        foreach ($index as &$agent) {
            $agent['millet'] = 0;
            if ($get['runit'] == 'total') {
                $agent['millet'] = $agent['total_millet'];
            } else if ($get['runit'] == 'during') {
                $agent['millet'] = $this->getMilletSum($agent['id'], 'f', '2019072')
                    - $this->getMilletSum($agent['id'], 'd', '2019-07-29')
                    - $this->getMilletSum($agent['id'], 'd', '2019-07-30')
                    - $this->getMilletSum($agent['id'], 'd', '2019-07-31');
            }   else if (in_array($get['runit'], ['d', 'w', 'f', 'm'])) {
                $agent['millet'] = $this->getMilletSum($agent['id'], $get['runit'], $get['rnum']);
            }
        }
    }

    protected function extendUnallocatedConsByIndex($get, &$index)
    {
        foreach ($index as &$agent) {
            $agent['unallocated_cons'] = 0;
            if ($get['runit'] == 'total') {
                $agent['unallocated_cons'] = 0;
            } else if ($get['runit'] == 'during') {
                $agent['unallocated_cons'] = $this->getUnallocatedConsSum($agent['id'], 'f', '2019072')
                    - $this->getUnallocatedConsSum($agent['id'], 'd', '2019-07-29')
                    - $this->getUnallocatedConsSum($agent['id'], 'd', '2019-07-30')
                    - $this->getUnallocatedConsSum($agent['id'], 'd', '2019-07-31');
            }  else if (in_array($get['runit'], ['d', 'w', 'f', 'm'])) {
                $agent['unallocated_cons'] = $this->getUnallocatedConsSum($agent['id'], $get['runit'], $get['rnum']);
            }
        }
    }

    protected function extendCashMilletByIndex($get, &$index)
    {
        $cashType = config('app.cash_setting.cash_type');
        foreach ($index as &$agent) {
            $agent['cash_millet'] = 0;
            $agent['not_cash_millet'] = 0;

            if ((empty($cashType) && $agent['cash_type'] == 1) || (!empty($cashType) && $agent['cash_type'] != 2)) {
                if ($get['runit'] == 'total') {
                    $agent['cash_millet'] = Db::name('millet_cash')->where([['agent_id', 'eq', $agent['id']], ['status', 'eq', 'success']])->sum('millet');
                    $agent['not_cash_millet'] = Db::name('millet_cash')->where([['agent_id', 'eq', $agent['id']], ['status', 'eq', 'wait']])->sum('millet');
                } else if ($get['runit'] == 'during') {
                    $agent['cash_millet'] = $this->getCashMilletSum($agent['id'], 'f', '2019072', 'success')
                        - $this->getCashMilletSum($agent['id'], 'd', '2019-07-29', 'success')
                        - $this->getCashMilletSum($agent['id'], 'd', '2019-07-30', 'success')
                        - $this->getCashMilletSum($agent['id'], 'd', '2019-07-31', 'success');

                    $agent['cash_millet'] = $this->getCashMilletSum($agent['id'], 'f', '2019072', 'wait')
                        - $this->getCashMilletSum($agent['id'], 'd', '2019-07-29', 'wait')
                        - $this->getCashMilletSum($agent['id'], 'd', '2019-07-30', 'wait')
                        - $this->getCashMilletSum($agent['id'], 'd', '2019-07-31', 'wait');
                } else if (in_array($get['runit'], ['d', 'w', 'f', 'm'])) {
                    $agent['cash_millet'] = $this->getCashMilletSum($agent['id'], $get['runit'], $get['rnum'], 'success');
                    $agent['not_cash_millet'] = $this->getCashMilletSum($agent['id'], $get['runit'], $get['rnum'], 'wait');
                }
            }
        }
    }

    public function getConsSum($agent_id, $unit, $num)
    {
        $prefix = "agent:all:{$agent_id}:cons";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
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
            $db = Db::name('kpi_cons');
            $this->setTimeRange($db, $unit, $num);
            $db->where($data);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public function getUnallocatedConsSum($agent_id, $unit, $num)
    {
        $prefix = "agent:all:{$agent_id}:un_cons";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $prifit = config('app.live_setting.bag_prifit_status');
            $data = [
                ['agent_id', 'eq', $agent_id],
                ['promoter_uid', 'eq', 0],
            ];
            if (empty($prifit)) {
                $data = [
                    ['agent_id', 'eq', $agent_id],
                    ['is_prifit', 'eq', 0],
                    ['promoter_uid', 'eq', 0],
                ];
            }
            $db = Db::name('kpi_cons');
            $this->setTimeRange($db, $unit, $num);
            $db->where($data);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public function getMilletSum($agent_id, $unit, $num)
    {
        $prefix = "agent:all:{$agent_id}:millet";
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
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
            $db = Db::name('kpi_millet');
            $this->setTimeRange($db, $unit, $num);
            $db->where($data);
            $sum = $db->sum('millet');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public function getRecharge($agent_id, $unit, $num)
    {
        $prefix = "agent:all:{$agent_id}:recharge";
        $sum = self::getCache($prefix, $unit, $num);
        $where = [['pr.agent_id', 'eq', $agent_id]];
        if (!isset($sum)) {
            $pay_methods = enum_array("pay_methods");
            $pay_methods = array_column($pay_methods,'value');
            $pay_methods = array_merge(array_diff($pay_methods, array('system_free', 'applepay_app')));

            $db = Db::name('recharge_order');
            $db->alias('recharge');
            $db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
            $db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
            $db->field('user.user_id');
            $db->field('recharge.id,recharge.user_id,recharge.pay_method,recharge.pay_status,recharge.pay_time,recharge.create_time,recharge.total_fee');
            $this->setTimeRange($db, $unit, $num);
            $where[] = ['recharge.pay_status', '=', '1'];
            $where[] = ['recharge.isvirtual', '=', '0'];
            $db->where($where);
            $db->whereIn('pay_method',$pay_methods);
            $sum = $db->sum('total_fee');

            self::setCache($prefix, $unit, $num, $sum);
        }

        return $sum;
    }

    public function getAppleRrecharege($agent_id, $unit, $num)
    {
        $prefix = "agent:apple:{$agent_id}:recharge";
        $sum = self::getCache($prefix, $unit, $num);
        $where = [['pr.agent_id', 'eq', $agent_id]];
        if (!isset($sum)) {
            $pay_methods = array('applepay_app');
            $db = Db::name('recharge_order');
            $db->alias('recharge');
            $db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
            $db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
            $db->field('user.user_id');
            $db->field('recharge.id,recharge.user_id,recharge.pay_method,recharge.pay_status,recharge.pay_time,recharge.create_time,recharge.total_fee');
            $this->setTimeRange($db, $unit, $num);
            $where[] = ['recharge.pay_status', '=', '1'];
            $where[] = ['recharge.isvirtual', '=', '0'];
            $db->where($where);
            $db->whereIn('pay_method',$pay_methods);
            $sum = $db->sum('total_fee');

            self::setCache($prefix, $unit, $num, $sum);
        }

        return $sum;
    }

    public function getCashMilletSum($agent_id, $unit, $num, $type = 'wait')
    {
        $prefix = "agent:all:{$agent_id}:{$type}cash_millet";
        $sum = self::getCache($prefix, $unit, $num);

        if (!isset($sum)) {
            $db = Db::name('millet_cash');
            $this->setTimeRange($db, $unit, $num);

            $data = [
                ['agent_id', 'eq', $agent_id],
                ['status', 'eq', $type],
            ];

            $db->where($data);
            $sum = $db->sum('millet');
            self::setCache($prefix, $unit, $num, $sum);
        }

        return $sum;
    }

    public function getTotalRecharge($unit, $num, $un_pay_method = ['system_free'])
    {
        $pay_methods = enum_array("pay_methods");
        $pay_methods = array_column($pay_methods,'value');
        $pay_methods = array_merge(array_diff($pay_methods, $un_pay_method));
        $db = Db::name('recharge_order');

        if ($unit != 'total') {
            $this->setTimeRange($db, $unit, $num);
        }
        $where[] = ['pay_status', '=', '1'];
        $where[] = ['isvirtual', '=', '0'];
        $db->where($where);
        $db->whereIn('pay_method',$pay_methods);
        $sum = $db->sum('total_fee');
        return $sum;
    }

}