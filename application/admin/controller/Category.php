<?php

namespace app\admin\controller;

use app\admin\service\DataVersion;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class Category extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:category:select');
        $pm = input('pm');
        $pid = input('pid');
        $categoryService = new \app\admin\service\Category();
        if (isset($pm)) {
            $gm = $categoryService->getIdByMark($pm);
            if (!empty($gm)) $pid = $gm;
        }
        //默认值
        $get = array_merge(array('pid' => '0'), input());
        $page = $this->pageshow($categoryService->getTotal($get));
        $list = $categoryService->getList($get, $page->firstRow, $page->listRows);
        //父级路径
        $path = $categoryService->setFieldOptions('name,id,pid')->getParentPath((int)$pid);
        $dataVersion = new DataVersion();
        $this->assign('data_v', $dataVersion->check('category'));
        $this->assign('_path', $path);
        $this->assign('_list', $list);
        $this->assign('_pid', $pid);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:category:add');
        if (Request::isPost()) {
            $post = Request::post();
            unset($post['id']);
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('category');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $catService = new \app\admin\service\Category();
            $id = $catService->add($post);
            if (!$id) $this->error($catService->getError());
            $dataVersion->update('category');
            alog("system.category.add", '新增类目 ID：'.$id);
            $this->success('添加成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:category:update');
        if (Request::isPost()) {
            $post = Request::post();
            $dataVersion = new DataVersion();
            $dataV = $dataVersion->check('category');
            if ($dataV != 'last') $this->error('请更新至' . $dataV);
            $catService = new \app\admin\service\Category();
            $num = $catService->update($post);
            if (!$num) $this->error($catService->getError());
            $dataVersion->update('category');
            alog("system.category.edit", '编辑类目 ID：'.$post['id']);
            $this->success('更新成功');
        }
    }

    //更新状态
    public function change_status()
    {
        $this->checkAuth('admin:category:update');
        $ids = get_request_ids();
        if (count($ids) <= 0) $this->error("请选择要操作的记录");
        $where[] = array('id', 'in', $ids);
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('category');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $result = Db::name('category')->where($where)->update(array("status" => input("status")));
        if (!$result) $this->error("更新状态失败");
        $catService = new \app\admin\service\Category();
        foreach ($ids as $id) {
            $catService->clearCache($id);
        }
        $dataVersion->update('category');
        alog("system.category.edit", '编辑类目 ID：'.$id."<br>修改状态: ".((input("status") == 1) ? "启用" : "禁用"));
        $this->success("更新状态成功");
    }

    //删除分类
    public function delete()
    {
        $this->checkAuth('admin:category:delete');
        $ids = get_request_ids();
        if (count($ids) <= 0) $this->error("请选择要操作的记录");
        $where[] = ['id', "in", $ids];
        $dataVersion = new DataVersion();
        $dataV = $dataVersion->check('category');
        if ($dataV != 'last') $this->error('请更新至' . $dataV);
        $catService = new \app\admin\service\Category();
        $result = $catService->delete($ids);
        if (!$result) $this->error('删除失败');
        $dataVersion->update('category');
        alog("system.category.del", '删除类目 ID：'.implode(",", $ids));
        $this->success('删除成功，共计删除了' . $result . '记录');
    }

    //获取分类树
    public function get_tree()
    {
        $this->checkAuth('admin:category:select');
        $order['sort'] = 'desc';
        $order['create_time'] = 'asc';
        $cateService = new \app\admin\service\Category();
        $result = $cateService->setOrderOptions($order)->setFieldOptions('id,pid,name')->typeControllerTree(input(), 'root', true, null);
        if (empty($result)) $this->error('获取列表失败');
        return json_success($result, '获取列表成功');
    }

    public function get_info()
    {
        $this->checkAuth('admin:category:select');
        $id = input('id');
        if (empty($id)) $this->error('请选择分类');
        $info = Db::name('category')->where(array('id' => $id))->find();
        if (empty($info)) $this->error('分类不存在');
        return json_success($info, '获取成功');
    }
}
