<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Music extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:music:select');
        $musicService = new \app\admin\service\Music();
        $get = input();
        $total = $musicService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $musicService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:music:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $musicService = new \app\admin\service\Music();
        $num = $musicService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("video.music.edit", '编辑音乐 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function add()
    {
        $this->checkAuth('admin:music:add');
        if (Request::isGet()) {
            $this->assign('display', 'none');
            return $this->fetch();
        } else {
            $musicService = new \app\admin\service\Music();
            $post = input();
            $result = $musicService->add($post);
            if (!$result) $this->error($musicService->getError());
            alog("video.music.add", '新增音乐 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:music:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('music')->where('id', $id)->find();
            if (empty($info)) $this->error('歌曲不存在');
            if ($info['user_id']) $info['user_name'] = Db::name('user')->where('user_id', $info['user_id'])->value('nickname');
            $this->assign('display',  $info['is_original'] ? 'table-row' : 'none');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $musicService = new \app\admin\service\Music();
            $post = input();
            $result = $musicService->update($post);
            if (!$result) $this->error($musicService->getError());
            alog("video.music.edit", '编辑音乐 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:music:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('music')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("video.music.del", '删除音乐 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','music/index');
    }
}