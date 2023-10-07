<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Impression extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:impression:select');
        $impressionService = new \app\admin\service\Impression();
        $get = input();
        $total = $impressionService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $impressionService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:impression:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $impressionService = new \app\admin\service\Impression();
            $post = input();
            $result = $impressionService->add($post);
            if (!$result) $this->error($impressionService->getError());
            alog("user.impression.add", '新增印象标签 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:impression:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('impression')->where('id', $id)->find();
            if (empty($info)) $this->error('印象不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $impressionService = new \app\admin\service\Impression();
            $post = input();
            $result = $impressionService->update($post);
            if (!$result) $this->error($impressionService->getError());
            alog("user.impression.edit", '编辑印象标签 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:impression:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('impression')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("user.impression.del", '删除印象标签 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','impression/index');
    }

    public function change_status()
    {
        $this->checkAuth('admin:impression:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $impressionService = new \app\admin\service\Impression();
        $num = $impressionService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("user.impression.edit", '编辑印象标签 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_list()
    {
        $this->checkAuth('admin:impression:select');
        $result = Db::name('impression')->field('id value,name')->select();
        $this->success('获取成功', $result);
    }

}

