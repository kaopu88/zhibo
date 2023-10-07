<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/22
 * Time: 11:38
 */
namespace app\taoke\controller;

use think\facade\Request;

class Ad extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:ad:index');
        $get = input();
        $adService = new \app\taoke\service\Ad();
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $adPosition = new \app\taoke\service\AdPosition();
        $positionList = $adPosition->getList( ["status"=>1], 0, 100);
        $this->assign('position', $positionList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeg:ad:add');
        if (Request::isGet()) {
            $adPosition = new \app\taoke\service\AdPosition();
            $positionList = $adPosition->getList( ["status"=>1], 0, 100);
            $this->assign('position', $positionList);
            return $this->fetch();
        } else {
            $adService = new \app\taoke\service\Ad();
            $post = input();
            $data['position_id'] = $post['position_id'];
            $data['title'] = $post['title'];
            $data['image'] = empty($post['image']) ? "" : $post['image'];
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
            $data['open_url'] = $post['open_url'];
            $data['params'] = $post['params'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $adService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.ad.add", "新增淘客广告 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:ad:update');
        $adService = new \app\taoke\service\Ad();
        if (Request::isGet()) {
            $id = input('id');
            $info = $adService->getInfo(["ad_id" => $id]);
            if (empty($info)) $this->error('广告不存在');
            $this->assign('_info', $info);
            $adPosition = new \app\taoke\service\AdPosition();
            $positionList = $adPosition->getList( ["status"=>1], 0, 100);
            $this->assign('position', $positionList);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['ad_id'] = $post['ad_id'];
            $data['position_id'] = $post['position_id'];
            $data['title'] = $post['title'];
            $data['image'] = empty($post['image']) ? "" : $post['image'];
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
            $data['open_url'] = $post['open_url'];
            $data['params'] = $post['params'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $adService->update($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.ad.edit", "编辑淘客广告 ID：".$post['ad_id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:ad:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $adService = new \app\taoke\service\Ad();
        $num = $adService->update(["ad_id"=>$ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.ad.edit", "编辑淘客广告 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taoke:ad:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择广告');
        $where[] = ['ad_id', "in", $ids];
        $adService = new \app\taoke\service\Ad();
        $num = $adService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.ad.del", "删除淘客广告 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}