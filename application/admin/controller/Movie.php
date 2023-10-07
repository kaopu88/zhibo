<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Movie extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:movie:select');
        $movService = new \app\admin\service\Movie();
        $get = input();
        $total = $movService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $movService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:movie:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $movService = new \app\admin\service\Movie();
            $post = input();
            $post['aid'] = AID;
            $result = $movService->add($post);
            if (!$result) $this->error($movService->getError());
            alog("content.movie.add", "新增电影 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:movie:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('movie')->where('id', $id)->find();
            if (empty($info)) $this->error('电影不存在');
            $info['length'] = $info['length'] ? $info['length'] / 60 : '';
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $mvService = new \app\admin\service\Movie();
            $post = input();
            $result = $mvService->update($post);
            if (!$result) $this->error($mvService->getError());
            alog("content.movie.edit", "编辑电影 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function subscription()
    {
        $this->checkAuth('admin:movie:raise');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('movie')->where('id', $id)->find();
            if (empty($info)) $this->error('电影不存在');
            $info['length'] = $info['length'] ? $info['length'] / 60 : '';
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $mvService = new \app\admin\service\Movie();
            $post = input();
            $result = $mvService->raise($post);
            if (!$result) $this->error($mvService->getError());
            alog("content.movie.edit", "编辑电影 ID：".$post['id']."<br>设置电影描述");
            $this->success('设置成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:movie:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('movie')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        Db::name('movie_progress')->whereIn('mid', $ids)->delete();
        alog("content.movie.del", "删除电影 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:movie:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('movie')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("content.movie.edit", "编辑电影 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function progress()
    {
        $this->checkAuth('admin:movie_progress:select');
        $this->assignMovieInfo();
        $movService = new \app\admin\service\MovieProgress();
        $get = input();
        $total = $movService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $movService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add_progress()
    {
        $this->checkAuth('admin:movie_progress:add');
        $this->assignMovieInfo();
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $movService = new \app\admin\service\MovieProgress();
            $post = input();
            $post['aid'] = AID;
            $result = $movService->add($post);
            if (!$result) $this->error($movService->getError());
            alog("content.movie.add_progress", "新增电影进度 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit_progress()
    {
        $this->checkAuth('admin:movie_progress:update');
        $this->assignMovieInfo();
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('movie_progress')->where('id', $id)->find();
            if (empty($info)) $this->error('电影进展不存在');
            $this->assign('_info', $info);
            return $this->fetch('add_progress');
        } else {
            $mvService = new \app\admin\service\MovieProgress();
            $post = input();
            $result = $mvService->update($post);
            if (!$result) $this->error($mvService->getError());
            alog("content.movie.edit_progress", "编辑电影进度 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del_progress()
    {
        $this->checkAuth('admin:movie_progress:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('movie_progress')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("content.movie.del_progress", "删除电影进度 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    private function assignMovieInfo()
    {
        $mid = input('mid');
        if (empty($mid)) $this->error('请选择电影');
        $info = Db::name('movie')->where(['id' => $mid])->field('id,title')->find();
        if (empty($info)) $this->error('电影不存在');
        $this->assign('movie', $info);
    }

}
