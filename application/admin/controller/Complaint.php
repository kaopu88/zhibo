<?php

namespace app\admin\controller;

use app\admin\service\Work;
use think\Db;
use think\facade\Request;

class Complaint extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:complaint:select');
        $get = input();
        $complaintService = new \app\admin\service\Complaint();
        $total = $complaintService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $complaintService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function check()
    {
        $this->checkAuth('admin:complaint:audit');
        $get = input();
        $get['aid'] = AID;
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'complaint');
        }
        $complaintService = new \app\admin\service\Complaint();
        $total = $complaintService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $complaintService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function handler_user()
    {
        $this->checkAuth('admin:complaint:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $complaintService = new \app\admin\service\Complaint();
        $result = $complaintService->handler_user($post, AID);
        if (!$result) return $this->error($complaintService->getError());
        alog("manager.complaint.edit", '编辑举报类型 ID：'.$post['id']);
        $this->success('处理成功');
    }

    public function handler_film()
    {
        $this->checkAuth('admin:complaint:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $complaintService = new \app\admin\service\Complaint();
        $result = $complaintService->handler_film($post, AID);
        if (!$result) return $this->error($complaintService->getError());
        alog("manager.complaint.edit", '编辑举报类型 ID：'.$post['id']);
        $this->success('处理成功');
    }

    public function handler_music()
    {
        $this->checkAuth('admin:complaint:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $complaintService = new \app\admin\service\Complaint();
        $result = $complaintService->handler_music($post, AID);
        if (!$result) return $this->error($complaintService->getError());
        alog("manager.complaint.audit", '编辑举报类型 ID：'.$post['id']);
        $this->success('处理成功');
    }

    public function handler_comment()
    {
        $this->checkAuth('admin:complaint:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $complaintService = new \app\admin\service\Complaint();
        $result = $complaintService->handler_comment($post, AID);
        if (!$result) return $this->error($complaintService->getError());
        alog("manager.complaint.edit", '编辑举报类型 ID：'.$post['id']);
        $this->success('处理成功');
    }

    public function category()
    {
        $this->checkAuth('admin:complaint_category:select');
        $get = input();
        $complaintService = new \app\admin\service\Complaint();
        $total = $complaintService->getCategoryTotal($get);
        $page = $this->pageshow($total);
        $list = $complaintService->getCategory($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function category_add()
    {
        $this->checkAuth('admin:complaint_category:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $complaintService = new \app\admin\service\Complaint();
            $post = input();
            $result = $complaintService->category_add($post);
            if (!$result) $this->error($complaintService->getError());
            alog("manager.complaint.add", '新增举报类型 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function category_edit()
    {
        $this->checkAuth('admin:complaint_category:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('complaint_category')->where('id', $id)->find();
            if (empty($info)) $this->error('类型不存在');
            $this->assign('_info', $info);
            return $this->fetch('category_add');
        } else {
            $complaintService = new \app\admin\service\Complaint();
            $post = input();
            $result = $complaintService->category_edit($post);
            if (!$result) $this->error($complaintService->getError());
            alog("manager.complaint.edit", '编辑举报类型 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function category_delete()
    {
        $this->checkAuth('admin:complaint_category:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('complaint_category')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("manager.complaint.del", '删除举报类型 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:complaint_category:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('complaint_category')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("manager.complaint.edit", '编辑举报类型 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_category()
    {
        $where = array();
        $target_type = input('target_type');
        $where['status'] = '1';
        $where['target'] = $target_type;
        $result = Db::name('complaint_category')->where($where)->field('id value,name')->select();
        $result = $result ? $result : array();
        $this->success('获取成功', $result);
    }
}