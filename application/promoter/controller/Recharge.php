<?php

namespace app\promoter\controller;

use bxkj_common\DateTools;
use think\facade\Request;

class Recharge extends Controller
{
    public function index()
    {
        $get = input();
        $get['pay_status']='1';
        $get = array_merge([
            'promoter_uid' => $this->admin['promoter_uid'],
        ], $get);
        $kpiConsService = new \app\promoter\service\RechargeOrder();
        $total = $kpiConsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiConsService->getList($get, $page->firstRow, $page->listRows);
        $summary = $kpiConsService->getSummary($get);
        $this->assign('summary', $summary);
        $this->assign('recharge_list', $list);
        $this->assign('is_root',$this->admin['is_root']);
        return $this->fetch();
    }
}
