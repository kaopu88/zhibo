<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/13
 * Time: 19:56
 */
namespace app\taoke\controller;

class Profit extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:profit:index');

        $get = input();
        $commissionLogService = new \app\taoke\service\CommissionLog();
        $total = $commissionLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $commissionLogService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('taoke:profit:delete');

        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $commissionLogService = new \app\taoke\service\CommissionLog();
        $where[] = ['id', "in", $ids];
        $num = $commissionLogService->delLog($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.common.del_profit", "删除淘客收益记录 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

}