<?php

namespace app\admin\controller;

use bxkj_module\service\Tree;
use Qiniu\Auth;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class Packages extends Controller
{
    public function home()
    {
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:packages:select');
        $packagesService = new \app\admin\service\Packages();
        $get = input();
        $total = $packagesService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $packagesService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:packages:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $packagesService = new \app\admin\service\Packages();
            $post = input();
            $post['aid'] = AID;
            $result = $packagesService->add($post);
            if (!$result) $this->error($packagesService->getError());
            alog("system.packages.add", '新增版本 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:packages:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('packages')->where('id', $id)->find();
            if (empty($info)) $this->error('安装包不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $packagesService = new \app\admin\service\Packages();
            $post = input();
            $result = $packagesService->update($post);
            if (!$result) $this->error($packagesService->getError());
            alog("system.packages.edit", '编辑版本 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:packages:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $packagesService = new \app\admin\service\Packages();
        $num = $packagesService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("system.packages.del", '删除版本 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:packages:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('packages')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("system.packages.edit", '编辑版本 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_min_version()
    {
        $this->checkAuth('admin:packages:update');
        $ids = get_request_ids();
        $id = $ids[0];
        if (empty($id)) $this->error('请选择记录');
        $min_version = input('min_version');
        if (!in_array($min_version, ['0', '1'])) $this->error('状态值不正确');
        if ($min_version=='1')
        {
            //查询id渠道和平台
            $item = Db::name('packages')->field('os,channel')->where('id', $id)->find();
            //查询同渠道和平台
            $find = Db::name('packages')->field('id')->where(array('os'=>$item['os'],'channel'=>$item['channel'],'min_version'=>1))->find();
            if ($find)
            {
                Db::name('packages')->where(array('os'=>$item['os'],'channel'=>$item['channel'],'min_version'=>1))->update(['min_version' => '0']);
            }
        }

        $num = Db::name('packages')->where('id', $id)->update(['min_version' => $min_version]);
        if (!$num) $this->error('切换状态失败');
        alog("system.packages.edit", '编辑版本 ID：'.implode(",", $ids)." 修改最低版本：".($min_version == 1 ? "是" : "否"));
        $this->success('切换成功');
    }

}
