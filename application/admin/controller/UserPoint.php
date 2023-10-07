<?php


namespace app\admin\controller;


class UserPoint extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_point:select');
        $taskService = new \app\admin\service\UserPoint();
        $get = input();
        $total = $taskService->getTotal($get);
        $page = $this->pageshow($total);
        $lists = $taskService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $lists);
        return $this->fetch();
    }
}