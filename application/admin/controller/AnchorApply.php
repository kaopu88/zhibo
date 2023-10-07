<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class AnchorApply extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:anchor_apply:index');
        $get = input();
        $status = $get['status'] ? $get['status'] : 0;
        $anchorApplyService = new \app\admin\service\AnchorApply();
        $total = $anchorApplyService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $anchorApplyService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $list);
        $this->assign('status', $status);
        return $this->fetch();
    }

    /**
     * 主播平台审核
     */
    public function review()
    {
        $id = input('id');
        $status = input('status');
        $reason = input('reason');
        if (empty($id)) $this->error('请选择一条审核记录');
        $anchorApplyService = new \app\admin\service\AnchorApply();
        $reslut = $anchorApplyService->approved($id, $status, $reason);
        if (!$reslut) $this->error($anchorApplyService->getError());
        alog("user.anchor.audit", "审核主播 ID：".$id);
        return $this->success('审核完成');
    }
    /**
     * 主播平台批量审核
     */
    public function reviews()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择一条审核记录');
        $status = input('status');
        $reason = input('reason');
        if (!in_array($status, ['0', '1', '2'])) $this->error('状态值不正确');
        $anchorApplyService = new \app\admin\service\AnchorApply();
        foreach($ids as $v){
            $reslut = $anchorApplyService->approved($v, $status, $reason);
            if (!$reslut) $this->error($anchorApplyService->getError());
            alog("user.anchor.audit", "审核主播 ID：".$v);
        }        
        return $this->success('审核完成');
    }
}
