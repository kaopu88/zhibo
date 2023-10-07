<?php

namespace app\admin\controller;

use think\facade\Request;
use think\Db;

class Notice extends Controller
{
	public function index()
    {
        $this->checkAuth('admin:notice:select');
    	$where = '1=1';
    	$get = input();
    	if (isset($get['visible']) && $get['visible'] !== '') {
    		$where .= ' and visible='.$get['visible'];
    	}
    	if (isset($get['barrage']) && $get['barrage'] !== '') {
    		$where .= ' and barrage='.$get['barrage'];
    	}
    	if (isset($get['status']) && $get['status'] !== '') {
    		$where .= ' and status='.$get['status'];
    	}
    	if (isset($get['keyword'])) {
    		$where .= ' and (title like "%'.trim($get['keyword']).'%" or id = '.intval(trim($get['keyword'])).')';
    	}
    	$total = Db::name('admin_notice')->where($where)->count();
    	$page = $this->pageshow($total);
    	$list = Db::name('admin_notice')->where($where)->order('create_time desc,id desc')->limit($page->firstRow, $page->listRows)->select();
    	$this->assign('_list', $list);
    	return $this->fetch();
    }

	public function add()
    {
    	$this->checkAuth('admin:notice:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $noticeService = new \app\admin\service\AdminNotice();
            $post = input();
            $post['aid'] = AID;
            $result = $noticeService->add($post);
            if (!$result) $this->error($noticeService->getError());
            alog("content.notice.add", '新增公告 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit(){
    	$this->checkAuth('admin:notice:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('admin_notice')->where('id', $id)->find();
            if (empty($info)) $this->error('公告不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $noticeService = new \app\admin\service\AdminNotice();
            $post = input();
            $result = $noticeService->update($post);
            if (!$result) $this->error($noticeService->getError());
            alog("content.notice.edit", '编辑公告 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:notice:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('admin_notice')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("content.notice.del", '删除公告 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function detail(){
    	$id = input('id');
        $info = Db::name('admin_notice')->where('id', $id)->find();
        if (empty($info)) $this->error('公告不存在');
        $this->assign('_info', $info);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:notice:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('admin_notice')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("content.notice.edit", '编辑公告 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }
}