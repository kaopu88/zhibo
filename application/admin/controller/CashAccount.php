<?php

namespace app\admin\controller;

class CashAccount extends Controller
{
    public function index(){
        $this->checkAuth('admin:cash_account:select');
        $get = input();
        $cashAccountService = new \app\admin\service\CashAccount();
        $total = $cashAccountService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $cashAccountService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}
