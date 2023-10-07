<?php

namespace app\admin\controller;

class LocationFavorites extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:location_favorites:select');
        $locationFavoritesService = new \app\admin\service\LocationFavorites();
        $get = input();
        $total = $locationFavoritesService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $locationFavoritesService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}