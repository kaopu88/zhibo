<?php

namespace app\agent\controller;
use think\Db;

class KpiCons extends Controller
{
    public function index()
    {
        $get = input();
        $get = array_merge([
            'agent_id' => AGENT_ID,
        ], $get);
        $kpiConsService = new \app\agent\service\KpiCons();
        $total = $kpiConsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiConsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('cons_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function get_agent()
    {
        $agent = array(array('value'=>AGENT_ID, 'name'=>'直属'));
        $agents = Db::name('agent')->field('id value,name')->where([['pid', '=', AGENT_ID]])->order(['create_time' => 'desc'])->select();
        $data = array_merge($agent,$agents);
        return json_success($data ? $data : [], '获取成功');
    }
}
