<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/23
 * Time: 16:31
 */
namespace app\taoke\controller;

use think\facade\Request;

class AdPosition extends Controller
{

    public function index()
    {
        $this->checkAuth('taoke:ad_position:index');
        $get = input();
        $adPosiService = new \app\taoke\service\AdPosition();
        $total = $adPosiService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adPosiService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeg:ad_position:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $adpService = new \app\taoke\service\AdPosition();
            $post = input();
            $data['name'] = $post['name'];
            $data['desc'] = $post['desc'];
            $data['status'] = $post['status'];
            $data['type'] = $post['type'];
            $result = $adpService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.ad_position.add", "新增淘客广告位 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:ad_position:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $adpService = new \app\taoke\service\AdPosition();
        $num = $adpService->update(["id"=>$ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.ad_position.edit", "编辑淘客广告位 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function edit()
    {
        $this->checkAuth('taoke:ad_position:update');
        $adpService = new \app\taoke\service\AdPosition();
        if (Request::isGet()) {
            $id = input('id');
            $info = $adpService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('广告位不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['desc'] = $post['desc'];
            $data['type'] = $post['type'];
            $data['status'] = $post['status'];
            $result = $adpService->update($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.ad_position.edit", "编辑淘客广告位 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

}