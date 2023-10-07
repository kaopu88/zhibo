<?php

namespace app\admin\controller;

class MusicFavorites extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:music_favorites:select');
        $musicFavoritesService = new \app\admin\service\MusicFavorites();
        $get = input();
        $total = $musicFavoritesService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $musicFavoritesService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

}
