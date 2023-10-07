<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class AdSpace extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:ad_space:select');
        $adService = new \app\admin\service\AdSpace();
        $get = input();
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:ad_space:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $artService = new \app\admin\service\AdSpace();
            $post = input();
            $post['aid'] = AID;
            $result = $artService->add($post);
            if (!$result) $this->error($artService->getError());
            alog("operate.ad_space.add", "新增广告内位 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:ad_space:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('ad_space')->where('id', $id)->find();
            if (empty($info)) $this->error('广告位不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $artService = new \app\admin\service\AdSpace();
            $post = input();
            $result = $artService->update($post);
            if (!$result) $this->error($artService->getError());
            alog("operate.ad_space.edit", "编辑广告位 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:ad_space:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $adService = new \app\admin\service\AdSpace();
        $num = $adService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("operate.ad_space.del", "删除广告位 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:ad_space:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $spaces = Db::name('ad_space')->whereIn('id', $ids)->select();
        $num = Db::name('ad_space')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        foreach ($spaces as $space) {
            if ($space['mark']) \bxkj_module\service\AdContent::clearCache($space['mark']);
        }
        alog("operate.ad_space.edit", "编辑广告位 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_spaces()
    {
        $this->checkAuth('admin:ad_space:select');
        $ad = new \app\admin\service\AdSpace();
        $spaces = $ad->getSpaces();
        $arr = [];
        foreach ($spaces as $space) {
            $arr[] = ['name' => $space['name'], 'value' => $space['id']];
        }
        return json_success($arr, '获取广告位成功');
    }

}
