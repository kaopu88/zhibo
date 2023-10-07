<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class PropsBean extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:props_bean:select');
        $propsBeanService = new \app\admin\service\PropsBean();
        $get = input();
        $total = $propsBeanService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $propsBeanService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:props_bean:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $propsBeanService = new \app\admin\service\PropsBean();
        $num = $propsBeanService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        $this->success('切换成功');
    }

    public function add()
    {
        $this->checkAuth('admin:props_bean:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $propsBeanService = new \app\admin\service\PropsBean();
            $post = input();
            $find = Db::name('props_bean')->where(array('props_id'=>$post['props_id'], 'length'=>$post['length'], 'unit'=>$post['unit']))->find();
            if ($find) $this->error('该道具相关套餐已存在，请勿重复添加！');
            $result = $propsBeanService->add($post);
            if (!$result) $this->error($propsBeanService->getError());
            alog("live.props_bean.add", '新增座驾价格 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:props_bean:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('props_bean')->where('id', $id)->find();
            $info['prop'] = Db::name('props')->where('id', $info['props_id'])->value('name');
            if (empty($info)) $this->error('价格不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $propsBeanService = new \app\admin\service\PropsBean();
            $post = input();
            $find = Db::name('props_bean')->where(array('props_id'=>$post['props_id'], 'length'=>$post['length'], 'unit'=>$post['unit']))->find();
            if ($find && $find['id'] != $post['id']) $this->error('该道具相关套餐已存在，请勿重复添加！');
            $result = $propsBeanService->update($post);
            if (!$result) $this->error($propsBeanService->getError());
            alog("live.props_bean.edit", '编辑座驾价格 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:props_bean:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('props_bean')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("live.props_bean.del", '删除座驾价格 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','props_bean/index');
    }

}

