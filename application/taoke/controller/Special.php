<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/14
 * Time: 15:42
 */
namespace app\taoke\controller;

use think\Db;
use think\facade\Request;

class Special extends Common
{

    public function index()
    {
        $this->checkAuth('taoke:special:index');

        $get = input();
        $specialService = new \app\taoke\service\Special();
        $total = $specialService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $specialService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function edit()
    {
        $this->checkAuth('taoke:special:update');
        $specialService = new \app\taoke\service\Special();
        if (Request::isGet()) {
            $id = input('id');
            $info = $specialService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('记录不存在');
            $pageList = [];
            if($info['page_id']) {
                $pageInfo = Db::name("taoke_page")->where(["id" => $info['page_id']])->find();
                $info['page_cid'] = $pageInfo['cid'];

                $pageList = Db::name("taoke_page")->field("id,name")->where(["type"=>3,"cid"=>$pageInfo['cid']])->select();
            }
            $this->assign('page_list', $pageList);
            $this->assign('_info', $info);
            return $this->fetch('edit');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['banner_status'] = $post['banner_status'];
            $data['status'] = $post['status'];
            $data['intro'] = trim($post['intro']);
            $data['banner'] = $post['banner'];
            $data['open_type'] = $post['open_type'];
            if($post['open_type'] == 2) {
                $openUrl = $post['open_url'];
                $params = $post['params'];
                $pageId = 0;
            }elseif ($post['open_type'] == 1){
                $pageId = $post['page_id'];
                $openUrl = "";
                $params = "";
            }else{
                $pageId = 0;
                $openUrl = "";
                $params = "";
            }
            $data['open_url'] = $openUrl;
            $data['params'] = $params;
            $data['page_id'] = $pageId;
            $result = $specialService->updateInfo($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.special.edit", "编辑特殊专题 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:special:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $specialService = new \app\taoke\service\Special();
        $num = $specialService->updateInfo(["id" => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.special.edit", "编辑特殊专题 ID：".implode(",", $ids)."<br>切换显示状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function changeBannerStatus()
    {
        $this->checkAuth('taoke:special:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $bannerStatus = input('banner_status');
        if (!in_array($bannerStatus, ['0', '1'])) $this->error('状态值不正确');
        $specialService = new \app\taoke\service\Special();
        $num = $specialService->updateInfo(["id" => $ids], ['banner_status' => $bannerStatus]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.special.edit", "编辑特殊专题 ID：".implode(",", $ids)."<br>切换banner图状态 ：".($bannerStatus == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }


    public function getPage()
    {
        if (Request::isPost()) {
            $type = input('type');
            $pageList = Db::name("taoke_page")->field("id,name")->where(["type"=>3,"cid"=>$type])->select();
            $this->success('获取成功', $pageList);
        }
    }

}