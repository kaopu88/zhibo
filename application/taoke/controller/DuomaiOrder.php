<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/3
 * Time: 11:52
 */
namespace app\taoke\controller;

class DuomaiOrder extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:duomai_order:index');

        $get = input();
        $orderService = new \app\taoke\service\DuomaiOrder();
        $total = $orderService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $orderService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('taoke:duomai_order:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $orderService = new \app\taoke\service\DuomaiOrder();
        $where[] = ['id', "in", $ids];
        $num = $orderService->delOrder($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.duomai.del_order", "删除多麦订单 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

}