<?php

namespace app\admin\controller;

class TopicFavorites extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:topic_favorites:select');
        $topicFavoritesService = new \app\admin\service\TopicFavorites();
        $get = input();
        $total = $topicFavoritesService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $topicFavoritesService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

}
