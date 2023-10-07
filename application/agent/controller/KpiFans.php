<?php

namespace app\agent\controller;
use bxkj_common\DateTools;
use think\facade\Request;

class KpiFans extends Controller
{
    public function index()
    {
        $get = input();
        $get = array_merge([
            'agent_id' => AGENT_ID,
        ], $get);
        $kpiFansService = new \app\agent\service\KpiFans();
        $total = $kpiFansService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiFansService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('fans_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }
}
