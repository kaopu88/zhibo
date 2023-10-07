<?php

namespace app\agent\controller;

use \app\agent\service\AgentPriceLog as AgentPriceLogModel;

class AgentPriceLog extends Controller
{
    public function index()
    {
        $get = input();
        $get = array_merge(['agent_id' => AGENT_ID], $get);
        $agentPriceLogModel = new AgentPriceLogModel;
        $total = $agentPriceLogModel->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentPriceLogModel->getList($get, $page->firstRow, $page->listRows);
        $this->assign('list', $list);
        return $this->fetch();
    }
}