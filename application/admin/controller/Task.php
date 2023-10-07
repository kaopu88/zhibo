<?php


namespace app\admin\controller;


class Task extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:task:select');
        $taskService = new \app\admin\service\Task();
        $get = input();
        $total = $taskService->getTotal($get);
        $page = $this->pageshow($total);
        $lists = $taskService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $lists);
        return $this->fetch();
    }
}