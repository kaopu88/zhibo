<?php

namespace app\admin\controller;

class UserThird extends Controller
{
    public function index(){
        $this->checkAuth('admin:user_third:select');
        $get = input();
        $userThirdService = new \app\admin\service\UserThird();
        $total = $userThirdService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userThirdService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}
