<?php
namespace app\admin\controller;

class VipOrder extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:vip_order:select');
        $vipOrder = new \app\admin\service\VipOrder();
        $get = input();
        $total = $vipOrder->getTotal($get);
        $page = $this->pageshow($total);
        $list = $vipOrder->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
