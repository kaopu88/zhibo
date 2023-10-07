<?php

namespace app\admin\controller;

class MusicUseLog extends Controller
{
    public function index(){
        $this->checkAuth('admin:music_use_log:select');
        $get = input();
        $musicUseLogService = new \app\admin\service\MusicUseLog();
        $total = $musicUseLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $musicUseLogService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}
