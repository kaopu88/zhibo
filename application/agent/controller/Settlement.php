<?php

namespace app\agent\controller;

use app\agent\service\AgentSettlement;
use think\Db;

class Settlement extends Controller
{
    public function index()
    {
        $get = input();
        $get = array_merge([
            'agent_id' => AGENT_ID,
        ], $get);
        $settlementSevice = new AgentSettlement();
        $total = $settlementSevice->getTotal($get);
        $page = $this->pageshow($total);
        $list = $settlementSevice->getList($get, $page->firstRow, $page->listRows);
        $this->assign('list', $list);
        return $this->fetch();
    }
}