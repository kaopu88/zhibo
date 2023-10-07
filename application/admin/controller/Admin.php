<?php

namespace app\admin\controller;

use bxkj_module\service\Tree;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class Admin extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:admin:select');
        $get = input();
        $adminService = new \app\admin\service\Admin();
        $total = $adminService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adminService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $groupList = Db::name('admin_group')->field('id,name')->order('create_time desc')->select();
        $this->assign('group_list', $groupList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:admin:add');
        if (Request::isPost()) {
            $adminService = new \app\admin\service\Admin();
            $id = $adminService->add(Request::post());
            if (!$id) $this->error($adminService->getError());
            alog("system.admin.add", '新增管理员 ID：'.$id);
            $this->success('添加成功');
        } else {
            $adminGroup=new \app\admin\service\AdminGroup();
            $groupList = $adminGroup->getAdminGroups(AID);
            $this->assign('group_list', $groupList);
            return $this->fetch();
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:admin:update');
        $id = input('id');
        $adminService = new \app\admin\service\Admin();
        if (Request::isPost()) {
            if ($id == ROOT_UID) $this->error('超级管理员账号受保护');
            $num = $adminService->update(Request::post());
            if (!$num) $this->error($adminService->getError());
            alog("system.admin.edit", '编辑管理员 ID：'.$id);
            $this->success('编辑成功');
        } else {
            if ($id == ROOT_UID) $this->error('超级管理员账号受保护');
            $adminGroup=new \app\admin\service\AdminGroup();
            $groupList = $adminGroup->getAdminGroups(AID);
            $this->assign('group_list', $groupList);
            $info = $adminService->getInfo($id);
            $this->assign('_info', $info);
            return $this->fetch('add');
        }
    }

    public function del()
    {
        $this->checkAuth('admin:admin:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择管理员');
        if (in_array(ROOT_UID, $ids)) $this->error('超级管理员账号受保护');
        $adminService = new \app\admin\service\Admin();
        $num = $adminService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("system.admin.del", '删除管理员 ID：'.implode(",", $ids));
        $this->success('删除成功');
    }

    public function change_status()
    {
        $this->checkAuth('admin:admin:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择管理员');
        $status = input('status');
        if (in_array(ROOT_UID, $ids)) $this->error('超级管理员账号受保护');
        $result = Db::name('admin')->whereIn('id', $ids)->update(array('status' => $status));
        if (!$result) $this->error('更新失败');
        alog("system.admin.edit", '编辑管理员 ID：'.implode(",", $ids). "<br>修改状态: ".(($status == 1) ? "启用" : "禁用"));
        $this->success('更新成功');
    }

}
