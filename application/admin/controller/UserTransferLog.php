<?php
namespace app\admin\controller;

class UserTransferLog extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_transfer_log:select');
        $get = input();
        $userTransferLogService = new \app\admin\service\UserTransferLog();
        $total = $userTransferLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userTransferLogService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}

