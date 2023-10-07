<?php

namespace app\admin\controller;

use app\admin\service\Work;
use think\facade\Request;

class UserVerified extends Controller
{
    public function index(){
        $this->checkAuth('admin:user_verified:select');
        $get = input();
        $userVerifiedService = new \app\admin\service\UserVerified();
        $total = $userVerifiedService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userVerifiedService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }

    public function audit(){
        $this->checkAuth('admin:user_verified:audit');
        $get = input();
        $get['aid'] = AID;
        if ($get['status'] == '0') {
            Work::read(AID, 'user_verified');
        }
        $userVerifiedService = new \app\admin\service\UserVerified();
        $total = $userVerifiedService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userVerifiedService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }

    public function handler()
    {
        $this->checkAuth('admin:user_verified:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $userVerifiedService = new \app\admin\service\UserVerified();
        $result = $userVerifiedService->handler($post);
        if (!$result) return $this->error($userVerifiedService->getError());
        alog("user.user.verified", "用户实名审核 USER_ID：".$post['id']);
        $this->success('处理成功');
    }
}
