<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/23
 * Time: 16:22
 */
namespace app\taokeshop\controller;

use app\taokeshop\service\AnchorGoods;
use think\Db;

class Goods extends Controller
{
    public function index()
    {
        $this->checkAuth("taokeshop:goods:index");

        $get = input();
        $AnchorGoodsService = new \app\taokeshop\service\AnchorGoods();
        $total = $AnchorGoodsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $AnchorGoodsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('taokeshop:goods:delete');
        $ids = get_request_ids("id");
        if (empty($ids)) $this->error('请选择商品');
        $anchorGoods = new AnchorGoods();
        $num = 0;
        foreach ($ids as $id){
            $info = [];
            $info = $anchorGoods->getGoodsInfo(["id"=>$id]);
            $count = Db::name("live_goods")->where("live_status > -1")->where(["goods_id"=>$info['goods_id'], "user_id"=>$info['user_id']])->count();
            if($count > 0){
                continue;
            }
            $res = $anchorGoods->delGoods(['id' => $id]);
            if($res !== false){
                $num++;
            }
        }
        alog("taokeshop.goods.del", '删除橱窗商品 GOODS_ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}