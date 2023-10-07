<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class MusicCategory extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:music_category:select');
        $musicCategoryService = new \app\admin\service\MusicCategory();
        $get = input();
        $total = $musicCategoryService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $musicCategoryService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $musicCategoryService = new \app\admin\service\MusicCategory();
        $result = $musicCategoryService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:music_category:select');
        $musicCategoryService = new \app\admin\service\MusicCategory();
        $get = input();
        $total = $musicCategoryService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $musicCategoryService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function change_recommend_status()
    {
        $this->checkAuth('admin:music_category:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $is_recommend = input('is_recommend');
        if (!in_array($is_recommend, ['0', '1'])) $this->error('状态值不正确');
        $musicCategoryService = new \app\admin\service\MusicCategory();
        $num = $musicCategoryService->changeStatus($ids, $is_recommend);
        if (!$num) $this->error('切换状态失败');
        alog("video.music_category.edit", '编辑音乐分类 ID：'.implode(",", $ids)." 修改状态：".($is_recommend == 1 ? "推荐" : "普通"));
        $this->success('切换成功');
    }

    public function add()
    {
        $this->checkAuth('admin:music_category:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $musicCategoryService = new \app\admin\service\MusicCategory();
            $post = input();
            $result = $musicCategoryService->add($post);
            if (!$result) $this->error($musicCategoryService->getError());
            alog("video.music_category.add", '新增音乐分类 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:music_category:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('music_category')->where('id', $id)->find();
            if (empty($info)) $this->error('分类不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $musicCategoryService = new \app\admin\service\MusicCategory();
            $post = input();
            $result = $musicCategoryService->update($post);
            if (!$result) $this->error($musicCategoryService->getError());
            alog("video.music_category.edit", '编辑音乐分类 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:music_category:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('music_category')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("video.music_category.del", '删除音乐分类 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','music_category/index');
    }

}
