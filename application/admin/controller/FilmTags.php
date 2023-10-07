<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class FilmTags extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:film_tags:select');
        $pm = input('pm');
        $pid = input('pid');
        $filmTagsService = new \app\admin\service\FilmTags();
        if (isset($pm)) {
            $gm = $filmTagsService->getIdByMark($pm);
            if (!empty($gm)) $pid = $gm;
        }
        //默认值
        $get = array_merge(array('pid' => '0'), input());
        $page = $this->pageshow($filmTagsService->getTotal($get));
        $list = $filmTagsService->getList($get, $page->firstRow, $page->listRows);
        //父级路径
        $path = $filmTagsService->setFieldOptions('name,id,pid')->getParentPath((int)$pid);
        $this->assign('_path', $path);
        $this->assign('_list', $list);
        $this->assign('_pid', $pid);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:film_tags:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $post = Request::post();
            unset($post['id']);
            $filmTagsService = new \app\admin\service\FilmTags();
            $result = $filmTagsService->add($post);
            if (!$result) $this->error($filmTagsService->getError());
            alog("video.film_tags.add", '新增视频标签 ID：'.$result);
            $this->success('新增成功');
        }
    }

    public function get_info()
    {
        $this->checkAuth('admin:film_tags:select');
        $id = input('id');
        if (empty($id)) $this->error('请选择标签');
        $info = Db::name('video_tags')->where(array('id' => $id))->find();
        if (empty($info)) $this->error('标签不存在');
        return json_success($info, '获取成功');
    }

    public function edit()
    {
        $this->checkAuth('admin:film_tags:update');
        if (Request::isPost()) {
            $params = input();
            $filmTagsService = new \app\admin\service\FilmTags();
            $result = $filmTagsService->update($params);
            if (!$result) $this->error($filmTagsService->getError());
            alog("video.film_tags.edit", '编辑视频标签 ID：'.$params =['id']);
            $this->success('更新成功');
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:film_tags:delete');
        $ids = get_request_ids();
        if (count($ids) <= 0) $this->error("请选择要操作的记录");
        $where[] = ['id', "in", $ids];
        $catService = new \app\admin\service\FilmTags();
        $result = $catService->delete($ids);
        if (!$result) $this->error('删除失败');
        alog("video.film_tags.del", '删除视频标签 ID：'.implode(",", $ids));
        $this->success('删除成功，共计删除了' . $result . '记录');
    }

    //获取分类树
    public function get_tree()
    {
        $this->checkAuth('admin:film_tags:select');
        $order['sort'] = 'desc';
        $order['create_time'] = 'asc';
        $filmTagsService = new \app\admin\service\FilmTags();
        $result = $filmTagsService->setOrderOptions($order)->setFieldOptions('id,pid,name')->typeControllerTree(input(), 'root', true, null);
        if (empty($result)) $this->error('获取列表失败');
        return json_success($result, '获取列表成功');
    }

    //选择标签
    public function selector()
    {
        $this->checkAuth('admin:film_tags:select,admin:film_tags:update');
        $pm = input('pm');
        $pid = input('pid');
        $filmTagsService = new \app\admin\service\FilmTags();
        if (isset($pm)) {
            $gm = $filmTagsService->getIdByMark($pm);
            if (!empty($gm)) $pid = $gm;
        }
        //默认值
        $get = array_merge(array('pid' => '0'), input());
        $page = $this->pageshow($filmTagsService->getTotal($get));
        $list = $filmTagsService->getList($get, $page->firstRow, $page->listRows);
        //父级路径
        $path = $filmTagsService->setFieldOptions('name,id,pid')->getParentPath((int)$pid);
        $this->assign('_path', $path);
        $this->assign('_list', $list);
        $this->assign('_pid', $pid);
        return $this->fetch();
    }


}
