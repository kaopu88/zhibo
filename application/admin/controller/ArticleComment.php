<?php

namespace app\admin\controller;

use app\admin\service\Work;
use think\Db;
use think\facade\Request;

class ArticleComment extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:article_comment:select');
        $artCommentService = new \app\admin\service\ArticleComment();
        $get = input();
        $total = $artCommentService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $artCommentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function topping()
    {
        $this->checkAuth('admin:article_comment:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('is_top');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('article_comment')->whereIn('id', $ids)->update(['is_top' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("content.article_comment.edit", "编辑文章评论 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "置顶" : "普通"));
        $this->success('切换成功');
    }

    public function reply()
    {
        $this->checkAuth('admin:article_comment:reply');
        $post = Request::post();
        $artCommentService = new \app\admin\service\ArticleComment();
        $result = $artCommentService->reply($post);
        if (!$result) $this->error($artCommentService->getError());
        alog("content.article_comment.reply", "回复文章评论 ID：".$post['id']);
        $this->success('回复成功');
    }

    public function del()
    {
        $this->checkAuth('admin:article_comment:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $artCommentService = new \app\admin\service\ArticleComment();
        $num = $artCommentService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("content.article_comment.del", "删除文章评论 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }


    public function audit_list()
    {
        $this->checkAuth('admin:article_comment:audit');
        $get = input();
        $get['aid'] = AID;
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'audit_wxapp_comment');
        }
        $artCommentService = new \app\admin\service\ArticleComment();
        $total = $artCommentService->getAuditTotal($get);
        $page = $this->pageshow($total);
        $artList = $artCommentService->getAuditList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function audit_norm()
    {
        return $this->fetch();
    }

    public function audit()
    {
        $this->checkAuth('admin:article_comment:audit');
        if (Request::isPost()) {
            $auditStatus = input('audit_status');
            $artService = new \app\admin\service\ArticleComment();
            if ($auditStatus == '1') {
                $ids = get_request_ids();
                if (empty($ids)) $this->error('请选择记录');
                $num = $artService->pass($ids, AID);
                if (!$num) $this->error($artService->getError());
                alog("content.article_comment.edit", "审核文章评论 ID：".implode(",", $ids)." 通过");
                $this->success('审核通过成功');
            } else if ($auditStatus == '2') {
                $post = input();
                $num = $artService->turnDown($post, AID);
                if (!$num) $this->error($artService->getError());
                alog("content.article_comment.edit", "审核文章评论 ID：".$post['id']." 驳回");
                $this->success('审核驳回成功');
            } else {
                $this->error('请选择审核状态');
            }
        }
    }


}
