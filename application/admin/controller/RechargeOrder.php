<?php

namespace app\admin\controller;

use think\facade\Request;

class RechargeOrder extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:recharge_order:select');
        $get = input();
        $rechargeService = new \app\admin\service\RechargeOrder();
        $total = $rechargeService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $rechargeService->getList($get, $page->firstRow, $page->listRows);
        $summary = $rechargeService->getSummary($get);
        $this->assign('summary', $summary);
        $this->assign('recharge_list', $list);
        return $this->fetch();
    }
}
