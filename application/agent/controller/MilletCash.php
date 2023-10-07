<?php

namespace app\agent\controller;
use think\Db;
use think\facade\Request;

class MilletCash extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $cashType = config('app.cash_setting');
        $this->assign('cash_type', $cashType['cash_type'] ? $cashType['cash_type'] : 0);
    }

    public function index()
    {
        $get = input();
        $milletCashService = new \app\agent\service\MilletCash();
        $summary = $milletCashService->getSummary($get);
        $total = $milletCashService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $milletCashService->getList($get, $page->firstRow,$page->listRows);
        $agentInfo = Db::name('agent')->where('id', AGENT_ID)->find();
        $this->assign('agent_info', $agentInfo);
        $this->assign('summary', $summary);
        $this->assign('get',$get);
        $this->assign('cash_list',$list);
        return $this->fetch();
    }

    public function change_status()
    {
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择提现记录');
            $cashRes  = Db::name('millet_cash')->where(['id' => $id, 'agent_id' => AGENT_ID])->find();;
            if (empty($cashRes)) $this->error('提现记录不存在');
            return json_success($cashRes, '获取成功');
        } else {
            $post = Request::post();
            $milletCashService = new \app\agent\service\MilletCash();
            $num = $milletCashService->update($post);
            if ($num['code'] != 200) $this->error($num['msg']);
            $this->success('操作成功', [
                'next' => [],
                'last_id' => $post['id']
            ]);
        }
    }
}
