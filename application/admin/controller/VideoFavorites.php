<?php

namespace app\admin\controller;

class VideoFavorites extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:video_favorites:select');
        $videoFavoritesService = new \app\admin\service\VideoFavorites();
        $get = input();
        $total = $videoFavoritesService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $videoFavoritesService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

}