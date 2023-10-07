<?php
namespace app\admin\controller;

class VideoUserReward extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:video_user_reward:select');
        $get = input();
        $videoUserRewardService = new \app\admin\service\VideoUserReward();
        $total = $videoUserRewardService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $videoUserRewardService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
