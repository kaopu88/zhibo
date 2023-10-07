<?php
namespace app\admin\controller;

class LivePk extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:live_pk:select');
        $livePkService = new \app\admin\service\LivePk();
        $get = input();
        $total = $livePkService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $livePkService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
