<?php

namespace app\admin\controller;

class MilletLog extends Controller
{
    public function index(){
        $this->checkAuth('admin:millet_log:select');
        $get = input();
        $milletLogService = new \app\admin\service\MilletLog();
        $total = $milletLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $milletLogService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}