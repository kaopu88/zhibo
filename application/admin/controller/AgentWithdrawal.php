<?php

namespace app\admin\controller;

use app\admin\service\AgentKpi;
use bxkj_common\DateTools;
use bxkj_payment\AlipayAdminPayMethod;
use think\Db;
use think\facade\Request;

class AgentWithdrawal extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config = config("payment.alipay");
    }

    public function index()
    {
        $this->checkAuth('admin:agent_withdrawal:select');
        $get = input();
        $agentWithdrawalService = new \app\admin\service\AgentWithdrawal();
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
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function all_list()
    {
        $this->checkAuth('admin:agent_withdrawal:audit_status');
        $get = input();
        if ($get['audit_status'] == '0') {
            \app\admin\service\Work::read(AID, 'agent_withdrawal');
        }
        $agentWithdrawalService = new \app\admin\service\AgentWithdrawal();
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
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:agent_withdrawal:audit_status');
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) return $this->error('请选择提现记录');
            $cashRes = Db::name('agent_withdrawal')->where('id', $id)->find();
            if (empty($cashRes)) return $this->error('提现记录不存在');
            return json_success($cashRes, '获取成功');
        } else {
            $post = Request::post();
            if ($post['pay_status'] == 2) {
                $post['audit_status'] = 2;
            } else {
                $post['audit_status'] = 1;
            }
            $agentWithdrawalService = new \app\admin\service\AgentWithdrawal();
            if ($post['pay_status'] == 1 && $post['audit_status'] == 1) {
                $milletCashDetail = $agentWithdrawalService->find(['id' => $post['id']]);
                $cashdetail = json_decode($milletCashDetail['cash_account'], true);
                if ($milletCashDetail['rmb'] <= 0) return $this->error('提现金额必须大于0');

                if ($milletCashDetail['casy_type'] == 0) {
                    //支付宝转账
                    $data['cash_no'] = $milletCashDetail['cash_no'];
                    $data['rmb'] = $milletCashDetail['rmb'];
                    $data['username'] = $cashdetail['name'];
                    $data['account'] = $cashdetail['account'];
                    $aliPayAdmin = new AlipayAdminPayMethod();
                    $rest = $aliPayAdmin->aliTransferAccounts($data, '\app\admin\service\AgentWithdrawal');
                    if ($rest['code'] == 200) {
                        $num = $agentWithdrawalService->updateData($post);
                        if ($num['code'] != 200) return $this->error($num['msg']);
                    } else {
                        return $this->error($rest['msg']);
                    }
                } else {
                    //微信转账
                    return $this->error('暂不支持该类型自动打款');
                }
            } else {
                $num = $agentWithdrawalService->updateData($post);
                if ($num['code'] != 200) return $this->error($num['msg']);
            }
            if ($post['audit_status'] == 1) {
                if ($post['pay_status'] == 1) {
                    $paytype = '自动打款';
                } else {
                    $paytype = '手动打款';
                }
            } else {
                $paytype = '';
            }
            alog("sociaty.agent.withdraw", '编辑公会提现 ID：' . $post['id'] . " 修改状态：" . ($post['audit_status'] == 1 ? "通过" : "拒绝") . $paytype);
            $this->success('操作成功', [
                'next' => [],
                'last_id' => $post['id']
            ]);
        }
    }
}
