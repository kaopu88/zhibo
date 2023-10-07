<?php

namespace app\admin\controller;

use bxkj_module\service\Tree;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class Article extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:article:select');
        $artService = new \app\admin\service\Article();
        $get = input();
        $catTree = new Tree('category');
        $catList = $catTree->getCategoryByMark('article_category');
        $this->assign('cat_list', $catList ? $catList : []);
        if (!empty($get['pcat_id'])) {
            $catList2 = Db::name('category')->where('pid', $get['pcat_id'])->field('id,name,mark')->select();
            $this->assign('cat_list2', $catList2 ? $catList2 : []);
        }
        $total = $artService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $artService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function index_wxapp()
    {
        $this->checkAuth('admin:article:select');
        $mark=input('mark');
        $artService = new \app\admin\service\Article();
        $get = input();
        $catTree = new Tree('category');
        $catList = $catTree->getCategoryByMark($mark);
        $this->assign('cat_list', $catList ? $catList : []);
        $get['pcat_id']=$catTree->getIdByMark($mark);
        $total = $artService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $artService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        $this->assign('pcat_id', $get['pcat_id']);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:article:add');
        if (Request::isGet()) {
            $info = [];
            $get = input();
            if ($get['pcat_id'] != '') $info['pcat_id'] = $get['pcat_id'];
            if ($get['cat_id'] != '') $info['cat_id'] = $get['cat_id'];
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $artService = new \app\admin\service\Article();
            $post = input();
            $post['aid'] = AID;
            $result = $artService->add($post);
            if (!$result) $this->error($artService->getError());
            alog("content.article.add", '新增文章 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:article:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('article')->where('id', $id)->find();
            if (empty($info)) $this->error('文章不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $artService = new \app\admin\service\Article();
            $post = input();
            $result = $artService->update($post);
            if (!$result) $this->error($artService->getError());
            alog("content.article.edit", '编辑文章 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:article:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('article')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("content.article.del", '删除文章 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function get_tree()
    {
        $this->checkAuth('admin:article:select,admin:category:select');
        $categoryTree = new Tree('category', 'pid', 'id');
        $result = $categoryTree->typeControllerTree(input(), 'article_category', false, 2);
        $this->success('', $result);
    }

    public function change_status()
    {
        $this->checkAuth('admin:article:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('article')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("content.article.edit", '编辑文章 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function modify_pcat_id()
    {
        $this->checkAuth('admin:article:update');
        $total = 0;
        $articles = Db::name('article')->field('id,cat_id')->select();
        foreach ($articles as $article) {
            if (!empty($article['cat_id'])) {
                $cateInfo = Db::name('category')->where('id', $article['cat_id'])->field('pid')->find();
                $num = Db::name('article')->where('id', $article['id'])->update(['pcat_id' => $cateInfo['pid']]);
                if ($num) $total++;
            }
        }
        $this->success('成功修改了' . $total . '个一级类目');
    }

}
