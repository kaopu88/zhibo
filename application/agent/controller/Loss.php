<?php

namespace app\agent\controller;

class Loss extends Controller
{
    public function index()
    {
        $lossService = new \app\agent\service\Loss();
        $get = input();
        $total = $lossService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $lossService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        $loss_after_months = config('app.loss_after_months');
        $this->assign('loss_after_months', $loss_after_months);
        return $this->fetch();
    }
}
