<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class RechargeBean extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:recharge_bean:select');
        $get = input();
        $rechargeService = new \app\admin\service\RechargeBean();
        $total = $rechargeService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $rechargeService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:recharge_bean:add');
        $name = APP_BEAN_NAME;
        if (Request::isGet()) {
            $info['name'] = $name;
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $rechargeService = new \app\admin\service\RechargeBean();
            $post = input();
            $post['name'] = $name;
            $result = $rechargeService->add($post);
            if (!$result) $this->error($rechargeService->getError());
            alog("manager.recharge.add", '新增兑换规则 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:recharge_bean:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('recharge_bean')->where('id', $id)->find();
            if (empty($info)) $this->error('套餐不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $rechargeService = new \app\admin\service\RechargeBean();
            $post = input();
            $result = $rechargeService->update($post);
            if (!$result) $this->error($rechargeService->getError());
            alog("manager.recharge.edit", '编辑兑换规则 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:recharge_bean:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $rechargeService = new \app\admin\service\RechargeBean();
        $num = $rechargeService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("manager.recharge.del", '删除兑换规则 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录", '', 'recharge_bean/index');
    }

    public function change_status()
    {
        $this->checkAuth('admin:recharge_bean:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $rechargeService = new \app\admin\service\RechargeBean();
        $num = $rechargeService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("manager.recharge.edit", '编辑兑换规则 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }
}