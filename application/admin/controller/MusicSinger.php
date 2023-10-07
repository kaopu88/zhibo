<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class MusicSinger extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:music_singer:select');
        $MusicSingerService = new \app\admin\service\MusicSinger();
        $get = input();
        $total = $MusicSingerService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $MusicSingerService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $MusicSingerService = new \app\admin\service\MusicSinger();
        $result = $MusicSingerService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:music_singer:select');
        $MusicSingerService = new \app\admin\service\MusicSinger();
        $get = input();
        $total = $MusicSingerService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $MusicSingerService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:music_singer:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $MusicSingerService = new \app\admin\service\MusicSinger();
            $post = input();
            $result = $MusicSingerService->add($post);
            if (!$result) $this->error($MusicSingerService->getError());
            alog("video.music_singer.add", '新增歌手 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:music_singer:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('music_singer')->where('id', $id)->find();
            if (empty($info)) $this->error('歌手不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $MusicSingerService = new \app\admin\service\MusicSinger();
            $post = input();
            $result = $MusicSingerService->update($post);
            if (!$result) $this->error($MusicSingerService->getError());
            alog("video.music_singer.edit", '编辑歌手 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:music_singer:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('music_singer')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("video.music_singer.del", '删除歌手 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','music_singer/index');
    }

}
