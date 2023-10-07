<?php
namespace app\admin\controller;

use think\Db;

class Location extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:location:select');
        $locationService = new \app\admin\service\Location();
        $get = input();
        $total = $locationService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $locationService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function cover()
    {
        $this->checkAuth('admin:location:select');
        $cover = Db::name('location')->where('id',input('id'))->value('photos');
        $cover = json_decode($cover,true);
        $this->assign('_list', $cover);
        return $this->fetch();
    }
}
