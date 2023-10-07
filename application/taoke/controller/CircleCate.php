<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/28
 * Time: 8:59
 */
namespace app\taoke\controller;

use think\facade\Request;

class CircleCate extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:circle_cate:index');

        $get = input();
        $circleService = new \app\taoke\service\CircleCate();
        $total = $circleService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $circleService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeg:circle_cate:add');
        $circleService = new \app\taoke\service\CircleCate();
        if (Request::isGet()) {
            $where['pid'] = 0;
            $total = $circleService->getTotal($where);
            $plist = $circleService->getList($where, 0, $total);
            $this->assign('plist', $plist);
            return $this->fetch();
        } else {
            $post = input();
            $data['name'] = $post['name'];
            $data['pid'] = $post['pid'];
            $data['type'] = $post['type'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $circleService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.circle_cate.add", "新增发圈分类 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:circle_cate:update');
        $circleService = new \app\taoke\service\CircleCate();
        if (Request::isGet()) {
            $id = input('id');
            $info = $circleService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('分类不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['type'] = $post['type'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $circleService->updateInfo($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.circle_cate.edit", "编辑发圈分类 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:circle_cate:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $circleService = new \app\taoke\service\CircleCate();
        $num = $circleService->updateInfo(["id" => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.circle_cate.edit", "编辑发圈分类 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taoke:circle_cate:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择分类');
        $circleService = new \app\taoke\service\CircleCate();
        $hasChild = false;
        foreach ($ids as $id){
            $childCircle = $circleService->getList(["pid" => $id], 0, 10);
            if(!empty($childCircle)){
                $hasChild = true;
            }
        }
        if($hasChild){
            $this->error('所选分类有下级分类，请先删除下级分类');
        }
        $where[] = ['id', "in", $ids];
        $num = $circleService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.circle_cate.del", "删除发圈分类 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}