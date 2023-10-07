<?php
namespace app\admin\controller;

class UserPackage extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_package:select');
        $userPackageService = new \app\admin\service\UserPackage();
        $get = input();
        $total = $userPackageService->getTotal($get);
        $page = $this->pageshow($total);
        $lists = $userPackageService->getList($get, $page->firstRow, $page->listRows);
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($get['user_id']);
        $this->assign('user', $user);
        $this->assign('_list', $lists);
        $this->assign('get', $get);
        return $this->fetch();
    }
}