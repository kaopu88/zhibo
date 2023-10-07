<?php

namespace app\admin\controller;

class GiftLog extends Controller
{
    public function index(){
        $this->checkAuth('admin:gift_log:select');
        $get = input();
        $giftLogService = new \app\admin\service\GiftLog();
        $total = $giftLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $giftLogService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}