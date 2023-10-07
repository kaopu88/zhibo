<?php

namespace app\admin\controller;

use app\admin\service\DataVersion;
use bxkj_module\service\Tree;
use think\Db;
use think\facade\Request;

class AdminRule extends Controller
{
    public function index()
    {
        $dataVersion = new DataVersion();
        $this->assign('data_v', $dataVersion->check('admin_rule'));
        return $this->fetch();
    }

    //获取分类树
    public function get_tree()
    {
        $this->checkAuth('admin:admin_rule:select');
        $cid = input('cid');
        $ruleService = new \app\admin\service\AdminRule();
        if ($cid == '0') {
            $tree = $ruleService->getRulesByCategory();
        } else {
            list($type, $pid) = explode('_', $cid);
            $tree = $type == 'cat' ? $ruleService->getRulesByCategory((int)$pid) : array();
        }
        return $this->success('', $tree ? $tree : array());
    }

    public function get_cat_tree()
    {
        $this->checkAuth('admin:admin_rule:select');
        $categoryTree = new Tree('category', 'pid', 'id');
        $result = $categoryTree->setOrderOptions('sort desc,create_time asc')->typeControllerTree(input(), 'rule_group', false, 2);
        $this->success('', $result);
    }

    public function add()
    {
        $this->checkAuth('admin:admin_rule:add');
        if (Request::isPost()) {
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('admin_rule');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $ruleService = new \app\admin\service\AdminRule();
            $id = $ruleService->add(Request::post());
            if (!$id) $this->error($ruleService->getError());
            $dataVersion->update('admin_rule');
            alog("system.admin_rule.add", '新增权限 ID：'.$id);
            return json_success($id, '新增成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:admin_rule:update');
        if (Request::isPost()) {
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('admin_rule');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $ruleService = new \app\admin\service\AdminRule();
            $num = $ruleService->update(Request::post());
            if (!$num) $this->error($ruleService->getError());
            $dataVersion->update('admin_rule');
            alog("system.admin_rule.edit", '编辑权限 ID：'.input("id"));
            return json_success($num, '编辑成功');
        }
    }

    public function del()
    {
        $this->checkAuth('admin:admin_rule:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择要删除的规则');
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('admin_rule');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $result = Db::name('admin_rule')->whereIn('id', $ids)->delete();
        if (!$result) $this->error('删除失败');
        $ruleService = new \app\admin\service\AdminRule();
        $ruleService->clearAllGroupRules($ids);
        $dataVersion->update('admin_rule');
        alog("system.admin_rule.del", '删除权限 ID：'.implode(",", $ids));
        $this->success('删除成功');
    }

    //选择权限
    public function selector()
    {
        $this->checkAuth('admin:admin_rule:select,admin:admin_group:update');
        $ruleService = new \app\admin\service\AdminRule();
        $tree = $ruleService->getRulesByCategory();
        $this->assign('tree', $tree);
        return $this->fetch();
    }

}
