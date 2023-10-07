<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/27
 * Time: 11:11
 */
namespace app\taoke\controller;

use think\facade\Request;

class Bussiness extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:bussiness:index');

        $get = input();
        $busService = new \app\taoke\service\Bussiness();
        $total = $busService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $busService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taoke:bussiness:add');
        if (Request::isGet()) {
            $busCate = new \app\taoke\service\BussinessCate();
            $total = $busCate->getTotal(["status" => 1]);
            $cateList = $busCate->getList(["status" => 1], 0, $total);
            $this->assign('cate_list', $cateList);
            return $this->fetch();
        } else {
            $busService = new \app\taoke\service\Bussiness();
            $post = input();
            $data['cate_id'] = $post['cate_id'];
            $data['title'] = $post['title'];
            $data['thumb_image'] = empty($post['thumb_image']) ? "" : $post['thumb_image'];
            $data['author'] = $post['author'];
            $data['video_url'] = $post['video_url'];
            if(!empty($post['video_url']) && empty($data['thumb_image'])){
                $data['thumb_image'] = $post['video_url']."?vframe/jpg/offset/1";
            }
            $data['content'] = $post['content'];
            $data['is_top'] = $post['is_top'];
            $data['is_hot'] = $post['is_hot'];
            $data['like_num'] = $post['like_num'];
            $data['view_num'] = $post['view_num'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $busService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.bussiness.add", "新增商学院 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:bussiness:update');
        $busService = new \app\taoke\service\Bussiness();
        if (Request::isGet()) {
            $id = input('id');
            $info = $busService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('分类不存在');
            $this->assign('_info', $info);

            $busCate = new \app\taoke\service\BussinessCate();
            $total = $busCate->getTotal(["status" => 1]);
            $cateList = $busCate->getList(["status" => 1], 0, $total);
            $this->assign('cate_list', $cateList);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['cate_id'] = $post['cate_id'];
            $data['title'] = $post['title'];
            $data['thumb_image'] = empty($post['thumb_image']) ? "" : $post['thumb_image'];
            $data['video_url'] = $post['video_url'];
            if(!empty($post['video_url']) && empty($data['thumb_image'])){
                $data['thumb_image'] = $post['video_url']."?vframe/jpg/offset/1";
            }
            $data['author'] = $post['author'];
            $data['content'] = $post['content'];
            $data['is_top'] = $post['is_top'];
            $data['is_hot'] = $post['is_hot'];
            $data['like_num'] = $post['like_num'];
            $data['view_num'] = $post['view_num'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $busService->updateInfo($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.bussiness.edit", "编辑商学院 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:bussiness:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $busService = new \app\taoke\service\Bussiness();
        $num = $busService->updateInfo(["id" => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.bussiness.edit", "编辑商学院 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function setHot()
    {
        $this->checkAuth('taoke:bussiness:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('is_hot');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $busService = new \app\taoke\service\Bussiness();
        $num = $busService->updateInfo(["id" => $ids], ['is_hot' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.bussiness.edit", "编辑商学院 ID：".implode(",", $ids)."<br>切换热门状态 ：".($status == 1 ? "热门" : "普通"));
        $this->success('切换成功');
    }

    public function setTop()
    {
        $this->checkAuth('taoke:bussiness:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('is_top');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $busService = new \app\taoke\service\Bussiness();
        $num = $busService->updateInfo(["id" => $ids], ['is_top' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.bussiness.edit", "编辑商学院 ID：".implode(",", $ids)."<br>切换置顶状态 ：".($status == 1 ? "置顶" : "普通"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taoke:bussiness:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择广告');
        $where[] = ['id', "in", $ids];
        $busService = new \app\taoke\service\Bussiness();
        $num = $busService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.bussiness.del", "删除商学院 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}