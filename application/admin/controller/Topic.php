<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Topic extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:topic:select');
        $liveFilmService = new \app\admin\service\Topic();
        $get = input();
        $total = $liveFilmService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $liveFilmService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        $this->assign('get',Request::param());
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:topic:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $params = input();
            $liveFilm = new \app\admin\service\Topic();
            $result = $liveFilm->add($params);
            if (!$result) $this->error($liveFilm->getError());
            alog("video.topic.add", '新增话题 ID：'.$result);
            $this->success('新增成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:topic:update');
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择话题');
            $topic = new \app\admin\service\Topic();
            $info = $topic->getInfo($id);
            if (empty($info)) $this->error('话题不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $params = input();
            $topic = new \app\admin\service\Topic();
            $result = $topic->update($params);
            if (!$result) $this->error($topic->getError());
            alog("video.topic.edit", '编辑话题 ID：'.$params['id']);
            $this->success('更新成功');
        }
    }

    public function del()
    {
        $this->checkAuth('admin:topic:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择话题');
        $topic = new \app\admin\service\Topic();
        $num = $topic->delete($ids);
        if (!$num) $this->error($topic->getError());
        alog("video.topic.del", '删除话题 ID：'.implode(",", $ids));
        $this->success('删除成功，共计删除了' . $num . '条');
    }


}
