<?php
namespace app\admin\controller;

class UserProps extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_props:select');
        $get = input();
        $userPropsService = new \app\admin\service\UserProps();
        $total = $userPropsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userPropsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
