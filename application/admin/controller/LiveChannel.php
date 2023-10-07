<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class LiveChannel extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:live_channel:select');
        $liveChannelService = new \app\admin\service\LiveChannel();
        $get = input();
        $total = $liveChannelService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $liveChannelService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:live_channel:add');
        if (Request::isGet()) {
            $list = Db::name('live_channel')->field('id,name')->where('parent_id','=','0')->select();
            $this->assign('_list', $list);
            return $this->fetch();
        } else {
            $liveChannelService = new \app\admin\service\LiveChannel();
            $post = input();
            $result = $liveChannelService->add($post);
            if (!$result) $this->error($liveChannelService->getError());
            alog("live.channel.add", '新增频道 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:live_channel:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('live_channel')->where('id', $id)->find();
            if (empty($info)) $this->error('频道不存在');
            $list = Db::name('live_channel')->field('id,name')->where('parent_id','=','0')->select();
            $this->assign('_list', $list);
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $liveChannelService = new \app\admin\service\LiveChannel();
            $post = input();
            $result = $liveChannelService->update($post);
            if (!$result) $this->error($liveChannelService->getError());
            alog("live.channel.edit", '编辑频道 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:live_channel:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $liveChannelService = new \app\admin\service\LiveChannel();
        $num = $liveChannelService->delete($ids);
        if (!$num) $this->error($liveChannelService->getError());
        alog("live.channel.del", '删除频道 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','live_channel/index');
    }

    public function change_status()
    {
        $this->checkAuth('admin:live_channel:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $liveChannelService = new \app\admin\service\LiveChannel();
        $num = $liveChannelService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("live.channel.edit", '编辑频道 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }


}
