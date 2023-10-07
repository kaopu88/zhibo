<?php

namespace app\admin\controller;

class Sms extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:sms:select');
        $smsService = new \app\admin\service\Sms();
        $get = input();
        var_dump($get);
        $total = $smsService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $smsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        $this->assign('get', $get);
        return $this->fetch();
    }

}
