<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class VideoComment extends Controller
{
	public function index()
	{
		$this->checkAuth('admin:film_comment:select');
        $get = input();
        $this->assign('get', $get);
        if ($get['master_id']) {
            $this->assign('raty', '3%');
        }else{
            $this->assign('raty', '5%');
        }
        $userId = Db::name('video')->where(array('id' => $get['video_id']))->value('user_id');
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($userId);
        $videoCommentService = new \app\admin\service\VideoComment();
        $total = $videoCommentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $videoCommentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('user', $user);
        $this->assign('get', $get);
        $this->assign('_list', $list);
        return $this->fetch();
	}

	public function _list()
    {
        $this->checkAuth('admin:film_comment:select');
        $get = input();
        $this->assign('get', $get);
        if ($get['master_id']) {
            $this->assign('raty', '3%');
        }else{
            $this->assign('raty', '6%');
        }
        $userId = Db::name('video')->where(array('id' => $get['video_id']))->value('user_id');
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($userId);
        $videoCommentService = new \app\admin\service\VideoComment();
        $total = $videoCommentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $videoCommentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('user', $user);
        $this->assign('get', $get);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('admin:film_comment:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $videoCommentService = new \app\admin\service\VideoComment();
        $num = $videoCommentService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("video.film_comment.del", '删除视频评论 ID：'.implode(",", $ids));
        $this->success('删除成功');
    }

    public function change_hot_status()
    {
        $this->checkAuth('admin:film_comment:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $is_hot = input('is_hot');
        if (!in_array($is_hot, ['0', '1'])) $this->error('状态值不正确');
        $videoCommentService = new \app\admin\service\VideoComment();
        $num = $videoCommentService->changeHotStatus($ids, $is_hot);
        if (!$num) $this->error('切换状态失败');
        alog("video.film_comment.edit", '编辑视频评论 ID：'.implode(",", $ids)." 修改状态：".($is_hot == 1 ? "热门" : "普通"));
        $this->success('切换成功');
    }

    public function change_delicate_status()
    {
        $this->checkAuth('admin:film_comment:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $is_delicate = input('is_delicate');
        if (!in_array($is_delicate, ['0', '1'])) $this->error('状态值不正确');
        $videoCommentService = new \app\admin\service\VideoComment();
        $num = $videoCommentService->changeDelicateStatus($ids, $is_delicate);
        if (!$num) $this->error('切换状态失败');
        alog("video.film_comment.edit", '编辑视频评论 ID：'.implode(",", $ids)." 修改状态：".($is_delicate == 1 ? "精选" : "普通"));
        $this->success('切换成功');
    }

    public function change_top_status()
    {
        $this->checkAuth('admin:film_comment:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $is_top = input('is_top');
        if (!in_array($is_top, ['0', '1'])) $this->error('状态值不正确');
        $videoCommentService = new \app\admin\service\VideoComment();
        $num = $videoCommentService->changeTopStatus($ids, $is_top);
        if (!$num) $this->error('切换状态失败');
        alog("video.film_comment.edit", '编辑视频评论 ID：'.implode(",", $ids)." 修改状态：".($is_top == 1 ? "置顶" : "普通"));
        $this->success('切换成功');
    }

    public function change_sensitive_status()
    {
        $this->checkAuth('admin:film_comment:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $is_sensitive = input('is_sensitive');
        if (!in_array($is_sensitive, ['0', '1'])) $this->error('状态值不正确');
        $videoCommentService = new \app\admin\service\VideoComment();
        $num = $videoCommentService->changeSensitiveStatus($ids, $is_sensitive);
        if (!$num) $this->error('切换状态失败');
        alog("video.film_comment.edit", '编辑视频评论 ID：'.implode(",", $ids)." 修改状态：".($is_sensitive == 1 ? "敏感" : "普通"));
        $this->success('切换成功');
    }

    public function update()
    {
        $this->checkAuth('admin:film_comment:update');
        $id = input('id');
        if (Request::isGet()) {
            $comment = Db::name('video_comment')->where('id',$id)->find();
            if (empty($comment)) $this->error('评论不存在');
            $this->success('获取成功', $comment);
        } else {
            $content = input('content');
            $update = [
                'content' => $content ? $content : ''
            ];
            $num = Db::name('video_comment')->where(['id' => $id])->update($update);
            if (!$num) $this->error('保存失败');
            alog("video.film_comment.edit", '编辑视频评论 ID：'.$id);
            $this->success('保存成功');
        }
    }
}