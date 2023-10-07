<?php
namespace app\admin\controller;

class BeanReward extends Controller
{
    public function index(){
        $this->checkAuth('admin:bean_reward:select');
        $get = input();
        $beanRewardService = new \app\admin\service\BeanReward();
        $total = $beanRewardService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $beanRewardService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);
        return $this->fetch();
    }
}