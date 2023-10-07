<?php

namespace app\admin\controller;

use app\admin\service\Work;
use think\facade\Request;

class Viewback extends Controller
{
	public function index(){
		$this->checkAuth('admin:viewback:select');
		$get = input();
		$viewbackService = new \app\admin\service\Viewback();
		$total = $viewbackService->getTotal($get);
		$page = $this->pageshow($total);
		$list = $viewbackService->getList($get,$page->firstRow,$page->listRows);
		$this->assign('_list',$list);
		return $this->fetch();
	}

	public function check(){
		$this->checkAuth('admin:viewback:audit');
		$get = input();
        $get['aid'] = AID;
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'viewback');
        }
        $viewbackService = new \app\admin\service\Viewback();
		$total = $viewbackService->getTotal($get);
		$page = $this->pageshow($total);
		$list = $viewbackService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
	}

    public function handler()
    {
        $this->checkAuth('admin:viewback:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $viewbackService = new \app\admin\service\Viewback();
        $result = $viewbackService->handler($post, AID);
        if (!$result) return $this->error($viewbackService->getError());
        alog("operate.viewback.audit", "用户反馈审核 ID:".$post['id']);
        $this->success('处理成功');
    }
}