<?php

namespace app\admin\controller;

use app\admin\service\DataVersion;
use bxkj_module\service\Auth;
use think\Db;
use think\facade\Request;

class AdminGroup extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:admin_group:select');
        $get = input();
        $adminGroup = new \app\admin\service\AdminGroup();
        $total = $adminGroup->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adminGroup->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $dataVersion = new DataVersion();
        $this->assign('data_v', $dataVersion->check('admin_group'));
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:admin_group:add');
        if (Request::isPost()) {
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('admin_group');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $adminGroup = new \app\admin\service\AdminGroup();
            $id = $adminGroup->add(Request::post());
            if (!$id) $this->error($adminGroup->getError());
            $dataVersion->update('admin_group');
            alog("system.admin_group.add", '新增权限组 ID：'.$id);
            $this->success('添加成功');
        } else {
            $adminRule = new \app\admin\service\AdminRule();
            $tree = $adminRule->getRulesByCategory(null, array(), false);
            $this->assign('tree', $tree);
            $this->assign('_info', array('rules_list' => array()));
//            $workTypes = config('enum.work_types');
            $workTypes = Db::name('work_types')->field('name, type as value, default_aid')->select();
            $this->assign('work_types', $workTypes);
            return $this->fetch();
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:admin_group:update');
        $adminGroup = new \app\admin\service\AdminGroup();
        if (Request::isPost()) {
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('admin_group');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $num = $adminGroup->update(Request::post());
            if (!$num) $this->error($adminGroup->getError());
            $dataVersion->update('admin_group');
            alog("system.admin_group.edit", '编辑权限组 ID：'.input('id'));
            $this->success('编辑成功');
        } else {
            $info = $adminGroup->getInfo(input('id'));
            $this->assign('tree', $info['tree']);
            if (empty($info)) $this->error('分组不存在');
//            $workTypes = config('enum.work_types');
            $workTypes = Db::name('work_types')->field('name, type as value, default_aid')->select();
            $this->assign('work_types', $workTypes);
            $this->assign('_info', $info);
            return $this->fetch('add');
        }
    }

    //删除
    public function del()
    {
        $this->checkAuth('admin:admin_group:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择分组');
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('admin_group');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $adminGroup = new \app\admin\service\AdminGroup();
        $result = $adminGroup->del($ids);
        if (!$result) $this->error('删除失败');
        $dataVersion->update('admin_group');
        alog("system.admin_group.del", '删除权限组 ID：'.implode(",", $ids));
        $this->success('删除成功', $result);
    }

    //切换状态
    public function change_status()
    {
        $this->checkAuth('admin:admin_group:update');
        $ids = get_request_ids();
        $status = input('status');
        if (empty($ids)) $this->error('请选择分组');
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('admin_group');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $adminGroup = new \app\admin\service\AdminGroup();
        $result = $adminGroup->changeStatus($ids, $status);
        if (!$result) $this->error($adminGroup->getError());
        $dataVersion->update('admin_group');
        alog("system.admin_group.edit", '编辑权限组 ID：'.implode(",", $ids). "<br>修改状态: ".(($status == 1) ? "启用" : "禁用"));
        $this->success('更新成功');
    }

    //自动保存权限
    public function save_rules()
    {
        $this->checkAuth('admin:admin_group:update');
        $id = input('id');
        $rules = input('rules');
        if (empty($id)) $this->error('分组不存在');
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('admin_group');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $adminGroup = new \app\admin\service\AdminGroup();
        if (!$adminGroup->validateRules($rules)) $this->error('权限不正确');
        $num = Db::name('admin_group')->where(array('id' => $id))->update(array('rules' => $rules ? $rules : ''));
        if (!$num) $this->error('权限自动保存失败');
        $key = Auth::getAdminGroupRulesKey($id);
        cache($key, null);
        $dataVersion->update('admin_group');
        $this->success('权限自动保存成功');
    }


}
