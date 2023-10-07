<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Liang extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:liang:index');
        $propsService = new \app\admin\service\Liang();
        $get = input();
        $total = $propsService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $propsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function find()
    {
        $this->checkAuth('admin:liang:index');
        $propsService = new \app\admin\service\Liang();
        $get = input();
        $total = $propsService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $propsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:liang:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $info = Db::name('liang')->where('status',2)->whereIn('id',$ids)->find();
        if($info)$this->error('已售出靓号不允许修改状态');
        $propsService = new \app\admin\service\Liang();
        $num = $propsService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("live.props.edit", '编辑靓号 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function add()
    {
        $this->checkAuth('admin:liang:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $propsService = new \app\admin\service\Liang();
            $post = input();
            $name = input('name');
            $info = Db::name('liang')->where('name', $name)->find();
            if($info)$this->error('靓号已存在,不允许重复添加');
            
            $result = $propsService->add($post);
            if (!$result) $this->error($propsService->getError());
            alog("live.props.add", '新增靓号 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:liang:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('liang')->where('id', $id)->find();
            if (empty($info)) $this->error('靓号不存在');
            if($info['status']==2)$this->error('已售出靓号不允许修改');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $propsService = new \app\admin\service\Liang();
            $post = input();
            $id = input('id');
            $info = Db::name('liang')->where('id','<>', $id)->where('name', $name)->find();
            if($info)$this->error('靓号已存在,不允许重复添加');
            $result = $propsService->update($post);
            if (!$result) $this->error($propsService->getError());
            alog("live.props.edit", '编辑靓号 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:liang:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $info = Db::name('liang')->whereIn('id', $ids)->where('status',2)->find();
        if($info)$this->error('已售出靓号不允许删除');
        
        $num = Db::name('liang')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("live.props.del", '删除靓号 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','liang/index');
    }

    public function get_list()
    {
        $result = Db::name('props')->field('id value,name')->select();
        $this->success('获取成功', $result);
    }

}

