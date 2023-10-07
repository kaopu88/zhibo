<?php

namespace app\agent\controller;

use bxkj_common\DateTools;
use think\facade\Request;

class Recharge extends Controller
{
    public function index()
    {
        $get = input();
        $get['pay_status']='1';
        $kpiConsService = new \app\agent\service\RechargeOrder();
        $total = $kpiConsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiConsService->getList($get, $page->firstRow, $page->listRows);
        $summary = $kpiConsService->getSummary($get);
        $this->assign('summary', $summary);
        $this->assign('recharge_list', $list);
        return $this->fetch();
    }
}
