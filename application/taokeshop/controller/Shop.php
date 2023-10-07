<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/27
 * Time: 9:59
 */
namespace app\taokeshop\controller;

use app\admin\service\User;
use app\taokeshop\service\DredgeLog;
use app\taokeshop\service\Shop as tkShop;
use think\Db;

class Shop extends Controller
{
    public function index()
    {
        $this->checkAuth('taokeshop:shop:index');

        $get = input();
        $shopService = new tkShop();
        $total = $shopService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $shopService->getList($get, $page->firstRow, $page->listRows);
        if($list) {
            foreach ($list as $key => $value) {
                if($value['imgs']) {
                    $list[$key]['images'] = explode(",", $value['imgs']);
                }
            }
        }
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('taokeshop:shop:delete');
        $ids = get_request_ids();
        $shopListInfo = Db::name('anchor_shop')->whereIn('id', $ids)->select();
        $uids = [];
        foreach ($shopListInfo as $value){
            $uids[] = $value['user_id'];
        }
        if (empty($ids)) $this->error('请选择记录');
        $num1 = Db::name('anchor_goods_cate')->whereIn('shop_id', $ids)->delete();
        if($num1 === false){
            $this->error('小店分类删除失败');
        }
        $num2 = Db::name('anchor_goods')->whereIn('user_id', $uids)->delete();
        if($num2 === false){
            $this->error('小店商品删除失败');
        }
        $num3 = Db::name('anchor_shop')->whereIn('id', $ids)->delete();
        if($num3 === false){
            $this->error('小店删除失败');
        }
        $user = new User();
        foreach ($uids as $uid){
            $user->updateData($uid, ["taoke_shop"=>0,"shop_id"=>0]);
            Db::name('user_taoke_audit')->where(['user_id' => $uid])->delete();
            Db::name('dredge_log')->where(['user_id' => $uid, "type" => "taoke"])->delete();
        }
        alog("taokeshop.shop.del", '删除小店 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num3}条记录");
    }

    public function changeStatus()
    {
        $this->checkAuth('taokeshop:shop:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $user = new User();
        $shopIdStr = "";
        foreach ($ids as $shopid){
            $shopInfo = Db::name("anchor_shop")->where(["id" => $shopid])->find();
            if($shopInfo){
                $num = Db::name('anchor_shop')->where(['id' => $shopid])->update(['status' => $status]);
                if(!$num) {
                    $shopIdStr .= $shopid.",";
                }else{
                    $data["taoke_shop"] = ($status == 1) ? 1 : -2;
                    $user->updateData($shopInfo['user_id'], $data);
                }
            }
        }
        $shopIdStr = trim($shopIdStr, ",");
        if(!empty($shopIdStr)){
            $this->error('店铺'.$shopIdStr.'切换状态失败');
        }
        alog("taokeshop.shop.edit", '编辑小店 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function payLog()
    {
        $this->checkAuth('taokeshop:shop:pay_log');

        $get = input();
        $log = new DredgeLog();
        $total = $log->getTotal($get);
        $page = $this->pageshow($total);
        $list = $log->getLogList($get, $page->firstRow, $page->listRows);
        $pay_methods = enum_array("pay_methods");
        if($list) {
            foreach ($list as $key => $value) {
                if($value['pay_method']) {
                    foreach ($pay_methods as $val){
                        if($val['value'] == $value['pay_method']){
                            $list[$key]['method'] = $val['platform'];
                        }
                    }
                }
            }
        }
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function deleteLog()
    {
        $this->checkAuth('taokeshop:shop:deleteLog');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('dredge_log')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("taokeshop.shop.del_log", '删除小店开通支付记录 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}