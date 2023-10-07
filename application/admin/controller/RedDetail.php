<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class RedDetail extends Controller
{

    //红包管理
    public function index()
    {
        $this->checkAuth('admin:reddetail:index');
        $get = input();
        $redDetail= new \app\admin\service\RedDetail();
        $total = $redDetail->getTotal($get);
        $page = $this->pageshow($total);
        $list = $redDetail->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}