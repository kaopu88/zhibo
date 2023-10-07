<?php

namespace app\admin\controller;

use app\admin\service\Work;
use think\facade\Request;

class Creation extends Controller
{
    public function index(){
        $this->checkAuth('admin:creation:select');
        $get = input();
        $creationService = new \app\admin\service\Creation();
        $total = $creationService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $creationService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list',$list);
        return $this->fetch();
    }

    public function check(){
        $this->checkAuth('admin:creation:audit');
        $get = input();
        $get['aid'] = AID;
        if ($get['status'] == '0') {
            Work::read(AID, 'audit_creation');
        }
        $creationService = new \app\admin\service\Creation();
        $total = $creationService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $creationService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function handler()
    {
        $this->checkAuth('admin:creation:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $creationService = new \app\admin\service\Creation();
        $result = $creationService->handler($post, AID);
        if (!$result) return $this->error($creationService->getError());
        alog("operate.creation.edit", "编辑创作号 ID：".$post['id']);
        $this->success('处理成功');
    }
}
