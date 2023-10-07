<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/8
 * Time: 9:37
 */
namespace app\taoke\controller;

class UpgradeLog extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:upgrade_log:index');

        $get = input();
        $get['type'] = "taoke";
        $upService = new \app\taoke\service\UpgradeLog();
        $total = $upService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $upService->getList($get, $page->firstRow, $page->listRows);
        if($list){
            foreach ($list as $key => $value){
                $list[$key]['upgrade_condition'] = json_decode($value['upgrade_condition'], true);
            }
        }
        $this->assign('_list', $list);
        return $this->fetch();
    }
}