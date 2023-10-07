<?php
namespace app\admin\controller;

class Follow extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:follow:select');
        $get = input();
        $followService = new \app\admin\service\Follow();
        $total = $followService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $followService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}
