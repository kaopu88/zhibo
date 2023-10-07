<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class MusicAlbum extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:music_album:select');
        $MusicAlbumService = new \app\admin\service\MusicAlbum();
        $get = input();
        $total = $MusicAlbumService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $MusicAlbumService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $MusicAlbumService = new \app\admin\service\MusicAlbum();
        $result = $MusicAlbumService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:music_album:select');
        $MusicAlbumService = new \app\admin\service\MusicAlbum();
        $get = input();
        $total = $MusicAlbumService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $MusicAlbumService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:music_album:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $MusicAlbumService = new \app\admin\service\MusicAlbum();
            $post = input();
            $result = $MusicAlbumService->add($post);
            if (!$result) $this->error($MusicAlbumService->getError());
            alog("video.music_album.add", '新增专辑 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:music_album:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('music_album')->where('id', $id)->find();
            if (empty($info)) $this->error('专辑不存在');
            if ($info['singer_id']) $info['singer_name'] = Db::name('music_singer')->where('id', $info['singer_id'])->value('name');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $MusicAlbumService = new \app\admin\service\MusicAlbum();
            $post = input();
            $result = $MusicAlbumService->update($post);
            if (!$result) $this->error($MusicAlbumService->getError());
            alog("video.music_album.edit", '编辑专辑 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:music_album:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('music_album')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("video.music_album.del", '删除专辑 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','music_album/index');
    }

}
