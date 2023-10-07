<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/23
 * Time: 16:31
 */
namespace app\taoke\controller;

use think\facade\Request;

class ModulePosition extends Controller
{

    public function index()
    {
        $this->checkAuth('taoke:module_position:index');
        $get = input();
        $modulePosiService = new \app\taoke\service\ModulePosition();
        $total = $modulePosiService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $modulePosiService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeg:module_position:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $modulePosiService = new \app\taoke\service\ModulePosition();
            $post = input();
            $data['name'] = $post['name'];
            $data['desc'] = $post['desc'];
            $data['img'] = $post['img'];
            $data['status'] = $post['status'];
            $data['type'] = $post['type'];
            $data['sort'] = $post['sort'];
            $data['application_index'] = $post['application_index'];
            $result = $modulePosiService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.module_position.add", "新增模块位 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:module_position:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $modulePosiService = new \app\taoke\service\ModulePosition();
        $num = $modulePosiService->update(["id"=>$ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.module_position.edit", "编辑模块位 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function edit()
    {
        $this->checkAuth('taoke:module_position:update');
        $modulePosiService = new \app\taoke\service\ModulePosition();
        if (Request::isGet()) {
            $id = input('id');
            $info = $modulePosiService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('广告位不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['img'] = $post['img'];
            $data['desc'] = $post['desc'];
            $data['type'] = $post['type'];
            $data['sort'] = $post['sort'];
            $data['status'] = $post['status'];
            $data['application_index'] = $post['application_index'];
            $result = $modulePosiService->update($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.module_position.edit", "编辑模块位 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

}