<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class AdContent extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:ad_content:select');
        $adService = new \app\admin\service\AdContent();
        $get = input();
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $spaceService = new \app\admin\service\AdSpace();
        $spaces = $spaceService->getSpaces();
        $this->assign('spaces', $spaces);
        $info = ['space_id' => $get['space_id'] ? $get['space_id'] : ''];
        $this->assign('_info', $info);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:ad_content:add');
        if (Request::isGet()) {
            $this->assignCommon();
            $get = input();
            $this->assign('_info', [
                'space_id' => $get['space_id'] ? $get['space_id'] : ''
            ]);
            return $this->fetch();
        } else {
            $post = input();
            $adService = new \app\admin\service\AdContent();
            $post['aid'] = AID;
            $result = $adService->add($post);
            if (!$result) $this->error($adService->getError());
            alog("operate.ad_content.add", "新增广告内容 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    private function assignCommon()
    {
        $spaceService = new \app\admin\service\AdSpace();
        $spaces = $spaceService->getSpaces();
        $this->assign('spaces', $spaces);
    }

    public function edit()
    {
        $this->checkAuth('admin:ad_content:update');
        if (Request::isGet()) {
            $id = input('id');
            $adContent = new \app\admin\service\AdContent();
            $info = $adContent->getInfo($id);
            if (empty($info)) $this->error('广告内容不存在');
            $this->assignCommon();
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $adService = new \app\admin\service\AdContent();
            $post = input();
            $result = $adService->update($post);
            if (!$result) $this->error($adService->getError());
            alog("operate.ad_content.edit", "编辑广告内容 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:ad_content:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $adService = new \app\admin\service\AdContent();
        $num = $adService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("operate.ad_content.del", "删除广告内容 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:ad_content:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('ad_content')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        \app\admin\service\AdContent::clearCacheByIds(is_array($ids) ? $ids : explode(',', $ids));
        alog("operate.ad_content.edit", "编辑广告内容 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

}
