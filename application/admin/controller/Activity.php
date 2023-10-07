<?php

namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 活动管理
 * Class Activity
 * @package app\admin\controller
 */
class Activity extends Controller
{

    /**
     * 活动
     * @return mixed
     */
    public function index()
    {
        $this->checkAuth('admin:activity:select');
        $liveService = new \app\admin\service\Activity();
        $get = input();
        $total = $liveService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $liveService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        return $this->fetch();
    }

    /**
     * 活动详情
     *
     */
    public function details()
    {
        $this->checkAuth('admin:activity:select');
        $get = input();
        if (empty($get['mark'])) return $this->error('错误');
        //驼峰规则
        $className = parse_name($get['mark'], 1);

        $className = '\\app\\admin\\service\\activity\\'.$className;

        if (!class_exists($className)) return $this->error('当前活动未实现');

        $liveService = new $className();

        $total = $liveService->getTotal($get);

        $page = $this->pageshow($total);

        $packages = $liveService->getList($get, $page->firstRow, $page->listRows);

        $this->assign('_list', $packages);

        return $this->fetch('activity/'.$get['mark'].'/index');
    }



    public function add(Request $request)
    {
        $this->checkAuth('admin:activity:add');
        if ($request->isGet()) {
            return $this->fetch();
        } else {
            $post = $request->param();
            $adService = new \app\admin\service\AdContent();
            $post['aid'] = AID;
            $result = $adService->add($post);
            if (!$result) $this->error($adService->getError());
            alog("live.activity.add", '新增活动 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }


    public function edit(Request $request)
    {
        $this->checkAuth('admin:activity:update');
        if ($request->isGet()) {
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
            alog("live.activity.edit", '编辑活动 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }


    public function delete()
    {
        $this->checkAuth('admin:activity:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $adService = new \app\admin\service\Activity();
        $num = $adService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("live.activity.del", '删除活动 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }


    public function change_status()
    {
        $this->checkAuth('admin:activity:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, [0, 1])) $this->error('状态值不正确');
        $num = Db::name('activity')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("live.activity.edit", '编辑活动 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }






}