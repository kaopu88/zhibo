<?php

namespace app\admin\controller;

use bxkj_module\service\Tree;
use think\Db;
use think\facade\Request;

class Help extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:help:select');
        $helpService = new \app\admin\service\Help();
        $categoryTree = new Tree('category', 'pid', 'id');
        $result = $categoryTree->typeControllerTree(input(), 'help_category', false, 2);
        $list = $helpService->getTotalList($result);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:help:add');
        if (Request::isGet()) {
            $info = [];
            $get = input();
            if ($get['pcat_id'] != '') $info['pcat_id'] = $get['pcat_id'];
            if ($get['cat_id'] != '') $info['cat_id'] = $get['cat_id'];
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $helpService = new \app\admin\service\Help();
            $post = input();
            $post['aid'] = AID;
            $result = $helpService->add($post);
            if (!$result) $this->error($helpService->getError());
            alog("system.help.add", "新增帮助 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:help:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('help')->where('id', $id)->find();
            if (empty($info)) $this->error('文章不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $helpService = new \app\admin\service\Help();
            $post = input();
            $result = $helpService->update($post);
            if (!$result) $this->error($helpService->getError());
            alog("system.help.add", "编辑帮助 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function get_tree()
    {
        $this->checkAuth('admin:article:select,admin:category:select');
        $categoryTree = new Tree('category', 'pid', 'id');
        $result = $categoryTree->typeControllerTree(input(), 'help_category', false, 2);
        $this->success('', $result);
    }

    public function detail()
    {
        if (Request::isGet()) {
            $mark = input('mark');
            $id = input('id');
            if (empty($mark)) {
                $where = 'id = '.intval($id);
            }else{
                $where = 'mark = "'.$mark.'"';
            }
            $info = Db::name('help')->where($where)->find();
            if (empty($info)) $this->error('文档不存在');
            $pcat_id = $info['pcat_id'];
            $cat_id = $info['cat_id'];
            $pcat_name = Db::name('category')->where('id', $pcat_id)->find();
            $cat_name = Db::name('category')->where('id', $cat_id)->find();
            $this->assign('pcat_name', $pcat_name);
            $this->assign('cat_name', $cat_name);
            $this->assign('_info', $info);
            return $this->fetch('detail');
        }
    }

    public function more()
    {
        if (Request::isGet()) {
            $pcat_id = input('pcat_id');
            $cat_id = input('cat_id');
            $total = Db::name('help')->where(array('pcat_id' => $pcat_id, 'cat_id' => $cat_id))->count();
            $page = $this->pageshow($total);
            $list = Db::name('help')->where(array('pcat_id' => $pcat_id, 'cat_id' => $cat_id))->order('sort desc,create_time desc')->limit($page->firstRow, $page->listRows)->select();
            $pcat_name = Db::name('category')->where('id', $pcat_id)->find();
            $cat_name = Db::name('category')->where('id', $cat_id)->find();
            $this->assign('pcat_name', $pcat_name);
            $this->assign('cat_name', $cat_name);
            $this->assign('_list', $list);
            return $this->fetch('more');
        }
    }

    public function search(){
    	if (Request::isGet()) {
    		$keyword = input('keyword');
    		$where = 'h.title like "%'.$keyword.'%" or h.content like "%'.$keyword.'%"';
    		$total = Db::name('help')
                ->field('h.*,pcat.name as pcat_name,cat.name as cat_name')->alias('h')
                ->join('__CATEGORY__ pcat', 'pcat.id=h.pcat_id')
                ->join('__CATEGORY__ cat', 'cat.id=h.cat_id')
                ->where($where)
                ->count();
        	$page = $this->pageshow($total);
        	$list = Db::name('help')
                ->field('h.*,pcat.name as pcat_name,cat.name as cat_name')->alias('h')
                ->join('__CATEGORY__ pcat', 'pcat.id=h.pcat_id')
                ->join('__CATEGORY__ cat', 'cat.id=h.cat_id')
                ->where($where)
                ->order('h.sort desc,h.create_time desc')
                ->limit($page->firstRow, $page->listRows)
                ->select();
        	$this->assign('keyword', $keyword);
        	$this->assign('_list', $list);
    	}
    	return $this->fetch();
    }

    public function delete(){
        $this->checkAuth('admin:help:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('help')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("system.help.del", "删除帮助 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','help/index');
    }

}
