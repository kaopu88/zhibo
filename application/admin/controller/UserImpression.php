<?php
namespace app\admin\controller;

class UserImpression extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_impression:select');
        $get = input();
        $userImpressionService = new \app\admin\service\UserImpression();
        $total = $userImpressionService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userImpressionService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
