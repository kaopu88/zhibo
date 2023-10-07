<?php
namespace app\agent\controller;

class LiveHistory extends Controller
{
    public function index()
    {
        $liveHistoryService = new \app\agent\service\LiveHistory();
        $get = input();
        $total = $liveHistoryService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $liveHistoryService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}

