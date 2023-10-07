<?php

namespace app\admin\controller;

use bxkj_payment\AlipayAdminPayMethod;
use bxkj_payment\WxPaymoney;
use think\db;
use think\facade\Request;

header("Content-type: text/html; charset=utf-8");

class MilletCash extends Controller
{
    protected  $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = config("payment.alipay");
    }

    public function index()
    {
        $this->checkAuth('admin:millet_cash:select');
        $get = input();
        $milletCashService = new \app\admin\service\MilletCash();
        $summary = $milletCashService->getSummary($get);
        $total = $milletCashService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $milletCashService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('summary', $summary);
        $this->assign('get', $get);
        $this->assign('cash_list', $list);
        return $this->fetch();
    }

    public function change_status()
    {
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) return $this->error('请选择提现记录');
            $cashRes = Db::name('millet_cash')->where('id', $id)->find();;
            if (empty($cashRes)) return $this->error('提现记录不存在');
            return json_success($cashRes, '获取成功');
        } else {
            $post = Request::post();
            if (!in_array($post['pay_status'], [0, 1, 2]) || !isset($post['pay_status'])) return $this->error('请选择打款方式');
            if ($post['pay_status'] == 2) {
                $post['status'] = 0;
            } else {
                $post['status'] = 1;
            }

            $milletCashService = new \app\admin\service\MilletCash();
            if ($post['pay_status'] == 1 && $post['status'] == 1) {
                $milletCashDetail = $milletCashService->find(['id' => $post['id']]);
                $cashAccount = new \app\admin\service\CashAccount();
                $cashdetail = $cashAccount->find(['id' => $milletCashDetail['cash_account']]);
                if ($milletCashDetail['rmb'] <= 0) {
                    return $this->error('提现金额必须大于0');
                }
                if ($cashdetail['account_type'] == 0) {
                    //支付宝转账
                    $data['cash_no'] = $milletCashDetail['cash_no'];
                    $data['rmb'] = $milletCashDetail['rmb'];
                    $data['username'] = $cashdetail['name'];
                    $data['account'] = $cashdetail['account'];
                    $aliPayAdmin = new AlipayAdminPayMethod();
                    $rest = $aliPayAdmin->aliTransferAccounts($data, '\app\admin\service\MilletCash');
                    if ($rest['code'] == 200) {
                        $num = $milletCashService->update($post);
                        if ($num['code'] != 200) return $this->error($num['msg']);
                    } else {
                        return $this->error($rest['msg']);
                    }
                } else {
                    //微信转账
                    $wxPay = new WxPaymoney();
                    $data['cash_no'] = $milletCashDetail['cash_no'];
                    $data['rmb'] = $milletCashDetail['rmb'];
                    $data['openid'] = $cashdetail['open_id'];
                    $respay = $wxPay->transfers($data);
                    if (!$respay) return $this->error($wxPay->getError());
                    $num = $milletCashService->update($post);
                }
            } else {
                $num = $milletCashService->update($post);
                if ($num['code'] != 200) $this->error($num['msg']);
            }
            if ($post['status'] == 1) {
                $text = '申请提现记录 ID：' . $post['id'] . " 审核打款；备注：" . $post['describe'];
            } else {
                $text = '申请提现记录 ID：' . $post['id'] . " 审核拒绝；备注：" . $post['describe'];
            }
            if ($post['status'] == 1) {
                if ($post['pay_status'] == 1) {
                    $paytype = '自动打款';
                } else {
                    $paytype = '手动打款';
                }
            } else {
                $paytype = '';
            }
            alog("user.millet_cash.edit", $text . $paytype);
            $this->success('操作成功', [
                'next' => [],
                'last_id' => $post['id']
            ]);
        }
    }
    public function tx()
    {   
        $ids = get_request_ids();
        // var_dump($ids);
        foreach ($ids as $id){
            $status = 1;
            $data = [
                'pay_status'=>0,
                'status'=>1,
                'id'=>$id
            ];
            $milletCashService = new \app\admin\service\MilletCash();
            $num = $milletCashService->update($data);
            if ($num['code'] != 200) $this->error($num['msg']);
            $text = '申请提现记录 ID：' . $post['id'] . " 审核打款；备注：";
            $paytype = '手动打款';
            alog("user.millet_cash.edit", $text . $paytype);
        }
        $this->success('操作成功', [
                'next' => [],
                // 'last_id' => $post['id']
        ]);
        
    }

}

