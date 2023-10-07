<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/12
 * Time: 16:09
 */
namespace app\taoke\controller;

use app\taoke\service\Estimate;
use app\taoke\service\Rebate;

class Order extends Common
{
    public function index()
    {
        $this->checkAuth('taoke:order:index');

        $get = input();
        $orderService = new \app\taoke\service\Order();
        $total = $orderService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $orderService->getOrderList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('taoke:order:delete');

        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $orderService = new \app\taoke\service\Order();
        $where[] = ['id', "in", $ids];
        $num = $orderService->delOrder($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.common.del_order", "删除淘客订单 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function getCommission()
    {
        $get = input();
        $rebate = $get['rebate'];
        $where['goods_order'] = $get['goods_order'];
        $where['goods_sonorder'] = $get['goods_sonorder'];
        if($rebate == 1){
            $rebateService = new Rebate();
            $info = $rebateService->getLog($where);
        }else{
            $estService = new Estimate();
            $info = $estService->getLog($where);
        }
        $data = [];
        if($info){
            if($info['value']) {
                $data = json_decode($info['value'], true);
            }
        }
        $this->assign('data', $data);
        return $this->fetch('order/commission');
    }
}