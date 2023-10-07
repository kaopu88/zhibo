<?php

namespace app\agent\service;

use bxkj_common\DateTools;
use bxkj_module\service\AgentPrice;
use bxkj_module\service\Service;
use think\Db;
use think\Exception;
use think\Model;

class AgentWithdrawal extends Model
{
    public function getTotal($get)
    {
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $start_time = strtotime($get['start_time']);
            $end_time = strtotime($get['end_time']);
            $where1[] = ['create_time', 'between', [$start_time, $end_time]];
        }

        $where = $this->setWhere($get);
        $count = $this->where($where)->where($where1)->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $start_time = strtotime($get['start_time']);
            $end_time = strtotime($get['end_time']);
            $where1[] = ['create_time', 'between', [$start_time, $end_time]];
        }

        $where = $this->setWhere($get);
        $result = $this->where($where)->where($where1)->order('id', 'desc')->limit($offset, $length)->select();

        $cashAgentIds = Service::getIdsByList($result, 'agent_id');
        $cashAgent = [];
        if (!empty($cashAgentIds)) {
            $cashAgent = Db::name('agent')->field('id, logo,name,level')->where(array('id' => $cashAgentIds))->limit(count($cashAgentIds))->select();
        }
        if (!empty($result)) {
            foreach ($result as &$value) {
                $value['descr'] = "【{$value['cash_no']}】 提现{$value['millet']}";
            }
        }
        return $result;
    }

    public function getOne(array $get = [])
    {
        $result = $this->where($get)->order('id', 'desc')->find();
        return $result ? $result : '';
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

    public function applyData($post)
    {
        try {
            Db::startTrans();
            $now = time();
            $price = $post['price'];
            apiAsserts(empty($post['cash_accout_id']), '请选择提现账户');
            $cash_account = Db::name('agent_cash_account')->where(['agent_id' => AGENT_ID, 'id' => $post['cash_accout_id']])->order('is_default','desc')->find();
            apiAsserts(empty($cash_account), '账户不存在');
            $agent_cash_config = config('app.cash_setting');
            $agent_cash_min =  isset($agent_cash_config['agent_cash_min']) ? $agent_cash_config['agent_cash_min'] :0;
            $agent_cash_monthlimit = isset($agent_cash_config['agent_cash_monthlimit']) ? $agent_cash_config['agent_cash_monthlimit'] :0;
            $agent_cash_fee = isset($agent_cash_config['agent_cash_fee']) ? $agent_cash_config['agent_cash_fee'] :0;
            $agent_cash_taxes = isset($agent_cash_config['agent_cash_taxes']) ? $agent_cash_config['agent_cash_taxes'] :0;
            apiAsserts($price <= 0, '提现金额不合法');
            apiAsserts( $price < $agent_cash_min, '最小提现金额' . $agent_cash_min);
            $withdrawal_count = Db::name('agent_withdrawal')->where(['agent_id' => AGENT_ID, 'month' => date('Ym', $now)])->count();
            apiAsserts (!empty($agent_cash_monthlimit) && $agent_cash_monthlimit <= $withdrawal_count, '您本月已经提现' . $withdrawal_count . '次');
            $cash_no = get_order_no('agent_cash');
            $data['cash_no'] = $cash_no;
            $data['agent_id'] = AGENT_ID;
            $data['millet'] = $price; //实际提现金额
            $data['cash_fee'] = $agent_cash_fee;
            $data['cash_taxes'] = round($price * $agent_cash_taxes, 2); //实际提现金额
            $data['rmb'] = round($price - $agent_cash_fee - $data['cash_taxes'], 2); //打款金额
            $data['cash_account'] =  json_encode([
                'account' => $cash_account['account'],
                'name' => $cash_account['name'],
                'card_name' => $cash_account['card_name'],
                'contact_phone' => $post['contact_phone']]);
            $data['casy_type'] = $cash_account['account_type'];
            $data['year'] = date('Y', $now);
            $data['month'] = date('Ym', $now);
            $data['day'] = date('Ymd', $now);
            $data['fnum'] = DateTools::getFortNum($now);
            $data['week'] = DateTools::getWeekNum($now);
            $data['create_time'] = $now;
            $res = $this->insertGetId($data);
            apiAsserts(!$res, '提现失败');
            $priceLogService = new AgentPrice();
            $res_og = $priceLogService->exp(['total' => $price, 'agent_id' => AGENT_ID, 'trade_type' => 'cash', 'trade_no' => $cash_no]);
            apiAsserts(!$res_og, $priceLogService->getError());
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 101, 'msg' =>$e->getMessage()];
        }
        Db::commit();
        return ['code' => 200];
    }

    public function applyDataold($post, $millet = 0, $cashProportion = 0)
    {
        if (empty($post['alipay_name'])) return ['code' => 101, 'msg' => '支付宝姓名不能为空'];
        if (empty($post['alipay_account'])) return ['code' => 102, 'msg' => '支付宝账号不能为空'];
        if (empty($post['contact_phone']) || !preg_match("/^1[3456789]\d{9}$/", $post['contact_phone'])) return ['code' => 103, 'msg' => '联系电话有误'];
        if (!in_array($post['casy_type'], ['0'])) return ['code' => 104, 'msg' => '提现方式错误'];
        if ($millet <= 0) return ['code' => 106, 'msg' => '金额有误'];
        $data['cash_no'] = get_order_no('agent_cash');
        $data['agent_id'] = AGENT_ID;
        $data['millet'] = $millet;
        $data['rmb'] = round($millet * $cashProportion, 2);
        $data['audit_status'] = 0;
        $data['cash_account'] = json_encode([
            'alipay_account' => $post['alipay_account'],
            'alipay_name' => $post['alipay_name'],
            'contact_phone' => $post['contact_phone']]);
        $data['casy_type'] = $post['casy_type'];
        $now = time();
        $data['year'] = date('Y', $now);
        $data['month'] = date('Ym', $now);
        $data['day'] = date('Ymd', $now);
        $data['fnum'] = DateTools::getFortNum($now);
        $data['week'] = DateTools::getWeekNum($now);
        $data['create_time'] = $now;
        $res = $this->insertGetId($data);
        if (!$res) return ['code' => 105, 'msg' => '提现失败'];
        return ['code' => 200, 'msg' => '提现成功'];
    }
}