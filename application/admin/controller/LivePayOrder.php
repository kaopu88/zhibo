<?php
namespace app\admin\controller;

class LivePayOrder extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:live_pay_order:select');
        $livePayOrderService = new \app\admin\service\LivePayOrder();
        $get = input();
        $total = $livePayOrderService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $livePayOrderService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
