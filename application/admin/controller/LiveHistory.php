<?php
namespace app\admin\controller;

class LiveHistory extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:live_history:select');
        $liveHistoryService = new \app\admin\service\LiveHistory();
        $get = input();
        $total = $liveHistoryService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $liveHistoryService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}

