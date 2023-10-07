<?php

namespace app\agent\controller;
use bxkj_common\DateTools;
use think\facade\Request;

class KpiMillet extends Controller
{
    public function index()
    {
        $get = input();
        $get = array_merge([
            'agent_id' => AGENT_ID,
        ], $get);
        $kpiMilletService = new \app\agent\service\KpiMillet();
        $total = $kpiMilletService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiMilletService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('millet_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }
}
