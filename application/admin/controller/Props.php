<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Props extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:props:select');
        $propsService = new \app\admin\service\Props();
        $get = input();
        $total = $propsService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $propsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function find()
    {
        $this->checkAuth('admin:props:select');
        $propsService = new \app\admin\service\Props();
        $get = input();
        $total = $propsService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $propsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:props:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $propsService = new \app\admin\service\Props();
        $num = $propsService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("live.props.edit", '编辑座驾 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function add()
    {
        $this->checkAuth('admin:props:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $propsService = new \app\admin\service\Props();
            $post = input();
            $result = $propsService->add($post);
            if (!$result) $this->error($propsService->getError());
            alog("live.props.add", '新增座驾 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:props:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('props')->where('id', $id)->find();
            if (empty($info)) $this->error('座驾不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $propsService = new \app\admin\service\Props();
            $post = input();
            $result = $propsService->update($post);
            if (!$result) $this->error($propsService->getError());
            alog("live.props.edit", '编辑座驾 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:props:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('props')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("live.props.del", '删除座驾 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','props/index');
    }

    public function get_list()
    {
        $result = Db::name('props')->field('id value,name')->select();
        $this->success('获取成功', $result);
    }

}

