<?php

namespace app\admin\controller;

use app\admin\service\Work;
use think\facade\Request;

class RechargeApp extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:recharge_app:audit');
        $get = input();
        $get['audit_aid'] = AID;
        $get['rec_type'] = 'user';
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'audit_recharge');
        }
        $recService = new \app\admin\service\RechargeLog();
        $total = $recService->getAuditTotal($get);
        $page = $this->pageshow($total);
        $list = $recService->getAuditList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function handler()
    {
        $this->checkAuth('admin:recharge_app:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $recService = new \app\admin\service\RechargeLog();
        $result = $recService->handler($post, AID);
        if (!$result) return $this->error($recService->getError());
        alog("manager.recharge.audit", "审核申请记录 ID:".$post['id']);
        $this->success('处理成功');
    }

    public function all_list()
    {
        $this->checkAuth('admin:recharge_app:select');
        $get = input();
        $get['rec_type'] = 'user';
        $recService = new \app\admin\service\RechargeLog();
        $total = $recService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $recService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }


}
