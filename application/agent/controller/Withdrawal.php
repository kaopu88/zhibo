<?php

namespace app\agent\controller;

use app\agent\service\AgentCashAccount;
use app\agent\service\AgentKpi;
use bxkj_module\service\Work;
use think\Db;
use think\facade\Request;

class Withdrawal extends Controller
{
    protected $agent;

    public function __construct()
    {
        parent::__construct();
        $cashOn = config('app.cash_setting.agent_cash_on');
        if (empty($cashOn)) {
            return $this->error('提现未开启');
        }
        $this->agent = Db::name('agent')->where(['id' => AGENT_ID])->find();
        if ($this->agent['cash_on'] != 1) {
            return $this->error('提现禁用');
        }
    }

    public function index()
    {
        $get = input();
        $get = array_merge([
            'agent_id' => AGENT_ID,
        ], $get);
        $agentWithdrawalService = new \app\agent\service\AgentWithdrawal();
        $total = $agentWithdrawalService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentWithdrawalService->getList($get, $page->firstRow, $page->listRows);
        if (!empty($list)) {
            foreach ($list as $key => &$value) {
                $cash_account = json_decode($value['cash_account'], true);
                $value['account'] = $cash_account['account'];
                $value['name'] = $cash_account['name'];
                $value['card_name'] = $cash_account['card_name'];
                $value['contact_phone'] = $cash_account['contact_phone'];
            }
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 提现申请
     */
    public function apply()
    {
        if (empty($this->agent)) return $this->error('代理不存在');
        $cash_account = Db::name('agent_cash_account')->where(['agent_id' => AGENT_ID])->order('is_default','desc')->select();
        if (Request::isPost()) {
            $submit = submit_verify('agent_withdraw' . AGENT_ID);
            if (!$submit) return $this->error('您提交太频繁了~~~');
            $post = Request::post();
            $agentWithdrawalService = new \app\agent\service\AgentWithdrawal();
            $res = $agentWithdrawalService->applyData($post);
            if ($res['code'] != 200) return $this->error($res['msg']);
            $workService = new Work();
            $data['audit_aid'] = $workService->allocation('agent_withdrawal');
            return json_success($res, '提现成功');
        } else {
            $this->assign('total', $this->agent['total_price'] ?: 0);
            $this->assign('cash_account', $cash_account);
            return $this->fetch();
        }
    }

    /**
     * 结算申请
     * 废弃
     */
    private function applyold()
    {
        $agentWithdrawalService = new \app\agent\service\AgentWithdrawal();

        if (empty($this->agent)) return $this->error('代理不存在');
        $cashProportion = $this->agent['cash_proportion'] ? $this->agent['cash_proportion'] : config('app.cash_setting.cash_proportion');

        $agentKpi = new AgentKpi();
        $month = date('Ym', strtotime("-1 month"));
        //平台结算的话需要扣除主播提现  并且只能按照收益结算
        $waitMillet = $sucessMillet = 0;
        $cashType = config('app.cash_setting.cash_type');
        //平台结算
        $unit = APP_MILLET_NAME;
        if (($this->agent['cash_type'] == 1) || (!empty($cashType) && $this->agent['cash_type'] != 2)) {
            $agentmillet = $agentKpi->getMilletSum(AGENT_ID, 'm', $month); //收益
            $waitMillet = $agentKpi->getCashMilletSum(AGENT_ID, 'm', $month);
            $sucessMillet = $agentKpi->getCashMilletSum(AGENT_ID, 'm', $month, 'success');
        }
        //公会结算
        if (($this->agent['cash_type'] == 2) || ($this->agent['cash_type'] != 1 && $cashType == 0)) {
            $cashMilletType = config('app.cash_setting.cash_millet_type') ? config('app.cash_setting.cash_millet_type') : 0;
            $unit = $cashMilletType ? APP_MILLET_NAME : APP_BEAN_NAME;
            if ($cashMilletType == 0) $agentmillet = $agentKpi->getConsSum(AGENT_ID, 'm', $month);
            if ($cashMilletType == 1) $agentmillet = $agentKpi->getMilletSum(AGENT_ID, 'm', $month);
        }

        $millet = $agentmillet - $waitMillet - $sucessMillet;
        $withdraw = $agentWithdrawalService->getOne(['agent_id' => AGENT_ID, 'month' => date('Ym', time())]);
        if (Request::isPost()) {
            $submit = submit_verify('agent_withdraw' . AGENT_ID);
            if (!$submit) return $this->error('您提交太频繁了~~~');
            $post = Request::post();
            if (!empty($withdraw) && ($withdraw['audit_status'] == 0 || $withdraw['audit_status'] == 1)) return $this->error('您本月已经申请过啦~~~');
            if ($millet <= 0) return $this->error('你暂无收益可提~');
            $res = $agentWithdrawalService->applyData($post, $millet, $cashProportion);
            if ($res['code'] != 200) return $this->error($res['msg']);

            $workService = new Work();
            $data['audit_aid'] = $workService->allocation('agent_withdrawal');
            return json_success($res, '提现成功');
        } else {
            if (!empty($withdraw['cash_account'])) $account = json_decode($withdraw['cash_account'], true);
            if (empty($withdraw) || $withdraw['audit_status'] == 2) $total = $millet;
            if (!empty($withdraw) && ($withdraw['audit_status'] == 0 || $withdraw['audit_status'] == 1)) $total = 0;
            $this->assign('unit', $unit);
            $this->assign('account', $account);
            $this->assign('total', $total);
            return $this->fetch();
        }
    }

    public function cash_account()
    {
        $get = input();
        $get = array_merge(['agent_id' => AGENT_ID], $get);
        $cashAccountService = new AgentCashAccount();
        $total = $cashAccountService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $cashAccountService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function cash_account_add(Request $request)
    {
        $params = $request::param();
        if ($request::isPost()) {

            if (empty($params['name']))  return $this->error('请填写姓名');
            if (empty($params['account']))  return $this->error('请填写账户');
            $cashAccountService = new AgentCashAccount();
            $res = $cashAccountService->add($params);
            if ($res['code'] != 200) return $this->error($res['msg']);
            return $this->success('添加成功');
        }
        return $this->fetch();
    }

    public function cash_account_edit(Request $request)
    {
        $params = $request::param();
        $id = input('id');
        $cashAccountService = new AgentCashAccount();
        if (Request::isPost()) {
            if (empty($params['name']))  return $this->error('请填写姓名');
            if (empty($params['account']))  return $this->error('请填写账户');
            $cashAccountService = new AgentCashAccount();
            $res = $cashAccountService->edit($params, $id);
            if ($res['code'] != 200) return $this->error($res['msg']);
            return $this->success('更新成功');
        } else {
            $info = Db::name('agent_cash_account')->where(array('id' => $id))->find();
            $this->assign('account', $info);
            return $this->fetch('cash_account_add');
        }
    }

    public function cash_account_del()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择管理员');
        $cashAccountService = new AgentCashAccount();
        $num = $cashAccountService->delete($ids);
        if (!$num) $this->error('删除失败');
        $this->success('删除成功');
    }
}
