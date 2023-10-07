<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/27
 * Time: 10:24
 */
namespace app\taoke\controller;

use think\facade\Request;

class BussinessCate extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:bussiness_cate:index');

        $get = input();
        $busService = new \app\taoke\service\BussinessCate();
        $total = $busService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $busService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeg:bussiness_cate:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $busService = new \app\taoke\service\BussinessCate();
            $post = input();
            $data['name'] = $post['name'];
            $data['img'] = empty($post['img']) ? "" : $post['img'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $busService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.bussiness_cate.add", "新增商学院分类 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:bussiness_cate:update');
        $busService = new \app\taoke\service\BussinessCate();
        if (Request::isGet()) {
            $id = input('id');
            $info = $busService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('分类不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['img'] = empty($post['img']) ? "" : $post['img'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $busService->updateInfo($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.bussiness_cate.edit", "编辑商学院分类 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:bussiness_cate:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $busService = new \app\taoke\service\BussinessCate();
        $num = $busService->updateInfo(["id" => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.bussiness_cate.edit", "编辑商学院分类 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taoke:bussiness_cate:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择广告');
        $where[] = ['id', "in", $ids];
        $busService = new \app\taoke\service\BussinessCate();
        $num = $busService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.bussiness_cate.del", "删除商学院分类 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}