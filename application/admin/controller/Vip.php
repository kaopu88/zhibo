<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Vip extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:vip:select');
        $get = input();
        $vipService = new \app\admin\service\Vip();
        $total = $vipService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $vipService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:vip:add');
        if (Request::isGet()){
            return $this->fetch();
        }else{
            $vipService = new \app\admin\service\Vip();
            $post = input();
            $result = $vipService->add($post);
            if (!$result) $this->error($vipService->getError());
            alog("manager.vip.add", '新增VIP套餐 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:vip:update');
        if (Request::isGet()){
            $id = input('id');
            $info = Db::name('vip')->where('id', $id)->find();
            if (empty($info)) $this->error('套餐不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        }else{
            $vipService = new \app\admin\service\Vip();
            $post = input();
            $result = $vipService->update($post);
            if (!$result) $this->error($vipService->getError());
            alog("manager.vip.edit", '编辑VIP套餐 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:vip:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('vip')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("manager.vip.del", '删除VIP套餐 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','vip/index');
    }

    public function change_status()
    {
        $this->checkAuth('admin:vip:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $vipService = new \app\admin\service\Vip();
        $num = $vipService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("manager.vip.edit", '编辑VIP套餐 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_list()
    {
        $result = Db::name('vip')->field('id value,name')->select();
        $this->success('获取成功', $result);
    }
}

