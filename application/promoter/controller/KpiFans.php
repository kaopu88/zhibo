<?php

namespace app\promoter\controller;
use bxkj_common\DateTools;
use think\facade\Request;

class KpiFans extends Controller
{
    public function index()
    {
        $get = input();
        $get = array_merge([
            'agent_id' => AGENT_ID,
            'promoter_uid' => $this->admin['promoter_uid'],
        ], $get);
        $kpiFansService = new \app\promoter\service\KpiFans();
        $total = $kpiFansService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiFansService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('fans_list', $list);
        $this->assign('get', $get);
        $this->assign('is_root',$this->admin['is_root']);
        return $this->fetch();
    }
}
