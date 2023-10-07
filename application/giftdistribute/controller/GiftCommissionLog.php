<?php

namespace app\giftdistribute\controller;

use app\giftdistribute\service\MilletCommisonCash;
use bxkj_common\RedisClient;
use bxkj_payment\AlipayAdminPayMethod;
use think\Db;
use think\facade\Request;

class GiftCommissionLog extends Controller
{
    protected $status = [0, 1 ,2];

    public function index()
    {
        $this->checkAuth('giftdistribute:commisonlog:index');
        $get = input();
        $giftCommissionLogModel = new \app\giftdistribute\service\GiftCommissionLog();
        $total = $giftCommissionLogModel->getTotal($get);
        $page = $this->pageshow($total);
        $list = $giftCommissionLogModel->getList($get, $page->firstRow, $page->listRows);

        $this->assign('get', $get);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function cash()
    {
        $this->checkAuth('giftdistribute:commisonlog:cash');
        $get = input();
        \app\admin\service\Work::read(AID, 'commison_withdrawal');
        $commisonCashgModel = new MilletCommisonCash();
        $summary = $commisonCashgModel->getSummary($get);
        $total = $commisonCashgModel->getTotal($get);
        $page = $this->pageshow($total);
        $list = $commisonCashgModel->getList($get, $page->firstRow, $page->listRows);
        $this->assign('summary', $summary);
        $this->assign('get', $get);
        $this->assign('cash_list', $list);
        return $this->fetch();
    }

    //TODO
    public function change_status()
    {
        $id = input('id');
        if (empty($id)) return $this->error('请选择提现记录');
        $cashRes = Db::name('millet_commison_cash')->where('id', $id)->find();
        if (Request::isGet()) {
            if (empty($cashRes)) return $this->error('提现记录不存在');
            return json_success($cashRes, '获取成功');
        } else {
            $post = Request::post();
            if (!in_array($post['pay_status'], $this->status) || !isset($post['pay_status'])) return $this->error('请选择打款方式');
            if ($cashRes['rmb'] <= 0) $this->error('提现金额必须大于0');

            $post['status'] = 1;
            if ($post['pay_status'] == 2) $post['status'] = 0;

            $commisonCashgModel = new MilletCommisonCash();
            if ($post['pay_status'] == 1 && $post['status'] == 1) {
                $cashAccount = new \app\admin\service\CashAccount();
                $cashdetail = $cashAccount->find(['id' => $cashRes['cash_account']]);
                if ($cashdetail['account_type'] == 0) {
                    $data['cash_no'] = $cashRes['cash_no'];
                    $data['rmb'] = $cashRes['rmb'];
                    $data['username'] = $cashdetail['name'];
                    $data['account'] = $cashdetail['account'];
                    $aliPayAdmin = new AlipayAdminPayMethod();
                    $rest = $aliPayAdmin->aliTransferAccounts($data, '\app\giftdistribute\service\MilletCommisonCash');
                    if ($rest['code'] == 200) {
                        $num = $commisonCashgModel->update($post);
                        if ($num['code'] != 200) return $this->error($num['msg']);
                    } else {
                        return $this->error($rest['msg']);
                    }
                } else {
                    //微信转账
                    return $this->error('微信提现没有开通');
                }

            }  else {
                $num = $commisonCashgModel->update($post);
                if ($num['code'] != 200) $this->error($num['msg']);
            }
            $paytype = '';
            if ($post['status'] == 1) {
                if ($post['pay_status'] == 1) $paytype = '自动打款';
                if ($post['pay_status'] == 0) $paytype = '手动打款';
                $text = '申请'. config('giftdistribute.name') .'提现记录 ID：' . $post['id'] . " 审核打款；备注：" . $post['describe'];
            } else {
                $text = '申请'. config('giftdistribute.name') .'提现记录 ID：' . $post['id'] . " 审核拒绝；备注：" . $post['describe'];
            }

            alog("user.millet_cash.edit", $text . $paytype);
            $this->success('操作成功', [
                'next'    => [],
                'last_id' => $post['id']
            ]);
        }
    }
}