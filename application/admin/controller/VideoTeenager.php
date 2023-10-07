<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/8/1
 * Time: 9:56
 */
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class VideoTeenager extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:video_teenager:index');
        $videoTeenagerService = new \app\admin\service\VideoTeenager();
        $get = input();
        $total = $videoTeenagerService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $videoTeenagerService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:video_teenager:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $post = input();
            $data['title'] = $post['title'];
            $data['author'] = $post['author'];
            $data['video_url'] = $post['video_url'];
            if(empty($post['video_url'])){
                $this->error('视频链接不能为空');
            }
            if(!empty($post['video_url'])) {
                $data['cover_url'] = $post['video_url'] . "?vframe/jpg/offset/1";
            }
            $data['desc'] = $post['desc'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $data['add_time'] = time();
            $result = Db::name("video_teenager")->insertGetId($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("video.teenager.add", '新增青少年视频 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:video_teenager:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name("video_teenager")->where(["id" => $id])->find();
            if (empty($info)) $this->error('视频不存在');
            $this->assign('_info', $info);

            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $data['title'] = $post['title'];
            $data['video_url'] = $post['video_url'];
            if(empty($post['video_url'])){
                $this->error('视频链接不能为空');
            }
            if(!empty($post['video_url'])) {
                $data['cover_url'] = $post['video_url'] . "?vframe/jpg/offset/1";
            }
            $data['author'] = $post['author'];
            $data['desc'] = $post['desc'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = Db::name("video_teenager")->where($where)->update($data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("video.teenager.edit", '编辑青少年视频 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('admin:video_teenager:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name("video_teenager")->where(["id" => $ids])->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("video.teenager.edit", '编辑青少年视频 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('admin:video_teenager:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择视频');
        $where[] = ['id', "in", $ids];
        $num = Db::name("video_teenager")->where($where)->delete();
        if (!$num) $this->error('删除失败');
        alog("video.teenager.del", '删除青少年视频 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

}