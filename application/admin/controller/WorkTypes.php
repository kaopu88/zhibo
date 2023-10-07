<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class WorkTypes extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:work_types:select');
        $workTypeService = new \app\admin\service\WorkTypes();
        $get = input();
        $total = $workTypeService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $workTypeService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        $this->assign('get',Request::param());
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:work_types:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $params = input();
            $workTypeService = new \app\admin\service\WorkTypes();
            $result = $workTypeService->add($params);
            if (!$result) $this->error($workTypeService->getError());
            alog("system.work_types.add", '新增类型 ID：'.$result);
            $this->success('新增成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:work_types:update');
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择工作类型');
            $workTypeService = new \app\admin\service\WorkTypes();
            $info = $workTypeService->getInfo($id);
            if (empty($info)) $this->error('工作类型不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $params = input();
            $workTypeService = new \app\admin\service\WorkTypes();
            $result = $workTypeService->update($params);
            if (!$result) $this->error($workTypeService->getError());
            alog("system.work_types.edit", '编辑类型 ID：'.$params['id']);
            $this->success('更新成功');
        }
    }

    public function del()
    {
        $this->checkAuth('admin:work_type:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择工作类型');
        $workTypeService = new \app\admin\service\WorkTypes();
        $num = $workTypeService->delete($ids);
        if (!$num) $this->error($workTypeService->getError());
        alog("system.work_types.del", '删除类型 ID：'.implode(",", $ids));
        $this->success('删除成功，共计删除了' . $num . '条');
    }
}