<?php
namespace app\admin\controller;

class UserBlacklist extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_blacklist:select');
        $get = input();
        $userBlacklistService = new \app\admin\service\UserBlacklist();
        $total = $userBlacklistService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userBlacklistService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}
