<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/22
 * Time: 11:38
 */
namespace app\taoke\controller;

use think\Db;
use think\facade\Request;

class Module extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:module:index');
        $get = input();
        $moduleService = new \app\taoke\service\Module();
        $total = $moduleService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $moduleService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $modulePosition = new \app\taoke\service\ModulePosition();
        $positionList = $modulePosition->getList( ["status"=>1, "order"=>"sort asc"], 0, 100);
        $this->assign('position', $positionList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeg:module:add');
        if (Request::isGet()) {
            $modulePosition = new \app\taoke\service\ModulePosition();
            $positionList = $modulePosition->getList( ["status"=>1, "order"=>"sort asc"], 0, 100);
            $this->assign('position', $positionList);
            return $this->fetch();
        } else {
            $moduleService = new \app\taoke\service\Module();
            $post = input();
            $data['position_id'] = $post['position_id'];
            $data['title'] = $post['title'];
            $data['image'] = empty($post['image']) ? "" : $post['image'];
            $data['selected_image'] = empty($post['selected_image']) ? "" : $post['selected_image'];
            $data['desc'] = $post['desc'];
            $bgColor = $post['bg_color'];
            $color = [];
            foreach ($bgColor as $value) {
                if (!empty($value)) {
                    $color[] = $value;
                }
            }
            if(!empty($color)){
                $data['bg_color'] = json_encode($color, true);
            }
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
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $moduleService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.module.add", "新增模块 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:module:update');
        $moduleService = new \app\taoke\service\Module();
        if (Request::isGet()) {
            $id = input('id');
            $info = $moduleService->getInfo(["module_id" => $id]);
            if (empty($info)) $this->error('模块不存在');
            $pageList = [];
            if($info['page_id']) {
                $pageInfo = Db::name("taoke_page")->where(["id" => $info['page_id']])->find();
                $info['page_cid'] = $pageInfo['cid'];

                $pageList = Db::name("taoke_page")->field("id,name")->where(["type"=>3,"cid"=>$pageInfo['cid']])->select();
            }
            $this->assign('page_list', $pageList);
            $this->assign('_info', $info);
            $modulePosition = new \app\taoke\service\ModulePosition();
            $positionList = $modulePosition->getList(["status"=>1,  "order"=>"sort asc"], 0, 100);
            $this->assign('position', $positionList);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['module_id'] = $post['module_id'];
            $data['position_id'] = $post['position_id'];
            $data['title'] = $post['title'];
            $data['image'] = empty($post['image']) ? "" : $post['image'];
            $data['selected_image'] = empty($post['selected_image']) ? "" : $post['selected_image'];
            $data['desc'] = $post['desc'];
            $bgColor = $post['bg_color'];
            $color = [];
            foreach ($bgColor as $value) {
                if (!empty($value)) {
                    $color[] = $value;
                }
            }
            if(!empty($color)){
                $data['bg_color'] = json_encode($color, true);
            }
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
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $moduleService->update($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.module.edit", "编辑模块 ID：".$post['module_id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:module:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $adService = new \app\taoke\service\Module();
        $num = $adService->update(["module_id"=>$ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.module.edit", "编辑模块 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taoke:module:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择广告');
        $where[] = ['module_id', "in", $ids];
        $adService = new \app\taoke\service\Module();
        $num = $adService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.module.del", "删除模块 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
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