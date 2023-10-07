<?php
namespace app\admin\controller;

class CoverStarVote extends Controller
{
    public function index(){
        $this->checkAuth('admin:cover_star_vote:select');
        $get = input();
        $coverStarVoteService = new \app\admin\service\CoverStarVote();
        $total = $coverStarVoteService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $coverStarVoteService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}
