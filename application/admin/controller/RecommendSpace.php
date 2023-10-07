<?php

namespace app\admin\controller;

use bxkj_module\service\Tree;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class RecommendSpace extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:recommend_space:select');
        $adService = new \app\admin\service\RecommendSpace();
        $get = input();
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:recommend_space:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $artService = new \app\admin\service\RecommendSpace();
            $post = input();
            $post['aid'] = AID;
            $result = $artService->add($post);
            if (!$result) $this->error($artService->getError());
            alog("manager.recommend_space.add", '新增推荐位 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:recommend_space:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('recommend_space')->where('id', $id)->find();
            if (empty($info)) $this->error('广告位不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $artService = new \app\admin\service\RecommendSpace();
            $post = input();
            $result = $artService->update($post);
            if (!$result) $this->error($artService->getError());
            alog("manager.recommend_space.edit", '编辑推荐位 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:recommend_space:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $adService = new \app\admin\service\RecommendSpace();
        $num = $adService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("manager.recommend_space.del", '删除推荐位 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:recommend_space:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('recommend_space')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("manager.recommend_space.edit", '编辑推荐位 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_spaces()
    {
        $this->checkAuth('admin:recommend_space:select');
        $ad = new \app\admin\service\RecommendSpace();
        $spaces = $ad->getSpaces();
        $arr = [];
        foreach ($spaces as $space) {
            $arr[] = ['name' => $space['name'], 'value' => $space['id']];
        }
        return json_success($arr, '获取广告位成功');
    }

}
