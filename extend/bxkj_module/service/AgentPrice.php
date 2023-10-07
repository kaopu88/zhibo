<?php

namespace bxkj_module\service;

use think\Db;
use think\facade\Request;

class AgentPrice extends Service
{
    protected static $typeNamge = ['inc' => '收入', 'exp' => '支出'];

    //收入
    public function inc($inputData)
    {
        return $this->change('inc', $inputData);
    }

    //支出
    public function exp($inputData)
    {
        return $this->change('exp', $inputData);
    }

    protected function change($type, $inputData)
    {
        $agent_id = $inputData['agent_id'];
        $total = $inputData['total'];
        $tradeType = $inputData['trade_type'];
        $tradeNo = $inputData['trade_no'];
        $remark = isset($inputData['remark']) ? $inputData['remark'] : '';
        if (!array_key_exists($type, self::$typeNamge)) $this->setError('变更类型不正确');
        if (empty($tradeType)) return $this->setError('交易类型不能为空');
        if ($total <= 0) return $this->setError( '数额不正确');
        $agent = Db::name('agent')->where(['id' => $agent_id])->find();
        if ($type == 'exp') {
            if ($agent['total_price'] < $total) return $this->setError('金额不足');
            $last_total_price = $agent['total_price'] - $total;
            $res_update = Db::name('agent')->where(['id' => $agent_id])->setDec('total_price', $total);
        } else {
            $last_total_price = $agent['total_price'] + $total;
            $res_update = Db::name('agent')->where(['id' => $agent_id])->setInc('total_price', $total);
        }
        if (!$res_update) return $this->setError('操作失败');
        $last_price = $agent['total_price'];

        $log['log_no'] = get_order_no('log');
        $log['agent_id'] = $agent_id;
        $log['type'] = $type;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_price'] = $last_total_price;
        $log['price'] = $last_total_price;
        $log['last_price'] = $last_price;
        $log['client_ip'] = Request::ip();
        $log['create_time'] = time();
        $log['remark'] = $remark;
        $id = Db::name('agent_price_log')->insert($log);
        if (!$id) return $this->setError('插入失败');
        return true;
    }
}