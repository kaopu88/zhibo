<?php
namespace app\admin\controller;

class VideoRewardRank extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:video_reward_rank:select');
        $get = input();
        $videoRewardRankService = new \app\admin\service\VideoRewardRank();
        $total = $videoRewardRankService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $videoRewardRankService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}