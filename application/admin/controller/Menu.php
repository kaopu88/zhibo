<?php

namespace app\admin\controller;

use app\admin\service\DataVersion;
use think\Db;
use think\facade\Request;

class Menu extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:menu:select');
        $dataVersion = new DataVersion();
        $menuModel = new \bxkj_module\service\Menu();
        $tree = $menuModel->getChildrenByMark('admin');
        $this->assign('data_v', $dataVersion->check('menu'));
        $this->assign('tree', $tree);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:menu:add');
        if (Request::isPost()) {
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('menu');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $menuModel = new \app\admin\service\Menu();
            $id = $menuModel->add(input());
            if (!$id) $this->error($menuModel->getError());
            $dataVersion->update('menu');

            alog("system.menu.add", '新增菜单 ID：'.$id);
            $this->success('添加成功', $id);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:menu:update');
        if (Request::isPost()) {
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('menu');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $menuModel = new \app\admin\service\Menu();
            $num = $menuModel->update(input());
            if (!$num) $this->error($menuModel->getError());
            $dataVersion->update('menu');
            alog("system.menu.edit", '编辑菜单 ID：'.input("id"));
            $this->success('编辑成功');
        }
    }

    public function del()
    {
        $this->checkAuth('admin:menu:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择菜单项');
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('menu');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $menuModel = new \app\admin\service\Menu();
        $result = $menuModel->delete($ids);
        if (!$result) $this->error('删除失败');
        $dataVersion->update('menu');
        alog("system.menu.del", '删除菜单 ID：'.implode(",", $ids));
        $this->success("删除了{$result}条", $result);
    }

    public function get_tree()
    {
        $this->checkAuth('admin:menu:select');
        $order['sort'] = 'desc';
        $order['create_time'] = 'asc';
        $ids = array();
        $pid = (int)Request::post('pid');
        $menuModel = new \bxkj_module\service\Menu();
        $result = $menuModel
            ->setOrderOptions($order)
            ->setFieldOptions('id,pid,name,url,param,target,icon,mark,level,descr,rules,sort,status,copy,display,badge')
            ->getChildren($pid, $ids, true, 1);
            // var_dump($result);die;
        $menuModel->checkChildren($result);
        $this->success('', $result ? $result : []);
    }
}
