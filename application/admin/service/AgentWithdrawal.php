<?php

namespace app\admin\service;

use bxkj_module\service\AgentPrice;
use bxkj_module\service\Service;
use think\Db;
use think\Model;

class AgentWithdrawal extends Model
{
    public function getTotal($get)
    {
        $where = $this->setWhere($get);
        $count = $this->where($where)->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $where = $this->setWhere($get);
        $result = $this->where($where)->order('id','desc')->limit($offset, $length)->select();
        $cashAgentIds = Service::getIdsByList($result, 'agent_id');
        $cashAgent = [];
        if (!empty($cashAgentIds)) {
            $cashAgent = Db::name('agent')->field('id, logo,name,level')->where(array('id' => $cashAgentIds))->limit(count($cashAgentIds))->select();
        }
        if (!empty($result)) {
            foreach ($result as &$value) {
                $value['agent_name'] = Service::getItemByList($value['agent_id'], $cashAgent, 'id', 'name');
                $value['level'] = Service::getItemByList($value['agent_id'], $cashAgent, 'id', 'level');
                $value['logo'] = Service::getItemByList($value['agent_id'], $cashAgent, 'id', 'logo');
                $value['descr'] = "【{$value['cash_no']}】 提现{$value['millet']}" ;
            }
        }
        return $result;
    }

    protected function setWhere($get)
    {
        if ($get['agent_id'] != '') {
            $where[] = ['agent_id', '=', $get['agent_id']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['audit_status', '=', $get['audit_status']];
        }

        return $where;
    }

    public function updateData($post)
    {
        if (empty($post['id'])) return ['code' => 102, 'msg' => '请选择记录'];
        if (!in_array($post['audit_status'], [1,2]))  return ['code' => 101, 'msg' => '操作不当'];
        $agent_withdraw_res = $this->where('id', $post['id'])->find();
        if (empty($agent_withdraw_res)) return ['code' => 104, 'msg' => '该记录不存在'];
        if ($post['audit_status'] == 2) {
            //退款操作
            $priceLogService = new AgentPrice();
            $res_og = $priceLogService->inc(['total' => $agent_withdraw_res['millet'], 'agent_id' => $agent_withdraw_res['agent_id'], 'trade_type' => 'cash', 'trade_no' => $agent_withdraw_res['cash_no'], 'remark' => '提现失败返还']);
            if (!$res_og) return ['code' => 105, 'msg' => $priceLogService->getError()];
        }
        $num = $this->where(['id' => $post['id']])->update(['audit_status' => $post['audit_status'], 'handler_time' => time(), 'admin_remark' => $post['admin_remark']]);
        if (!$num) return ['code' => 103, 'msg' => '操作失败2'];
        return ['code' => 200];
    }

    public function find($where){
        return  Db::name('agent_withdrawal')->where($where)->find();
    }

    public static function updatewithorder($update){
        return  Db::name('agent_withdrawal')->where(['cash_no' => $update['order']])->update(['thirdNo' => $update['thirdNo']]);
    }
}