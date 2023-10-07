<?php
/**
 * Created by PhpStorm.
 * User: 崔鹏
 * Date: 2020/05/18
 * Time  17:00
 */
namespace app\shop\controller;

use app\shop\service\DredgeLog;
use think\Db;

class Shop extends Controller
{
    public function index()
    {
        $this->checkAuth('shop:shop:index');

        $get = input();
        $shopService = new \app\taokeshop\service\Shop();
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
        $this->checkAuth('shop:shop:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('dredge_log')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("shop.shop.del_log", '删除商城店铺开通支付记录 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function payLog()
    {
        $this->checkAuth('shop:shop:pay_log');

        $get = input();
        $get['type'] = 'mall';
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
        $this->checkAuth('shop:shop:deleteLog');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('dredge_log')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("shop.shop.del_log", '删除商城店铺开通支付记录 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function gomaill(){
        $this->checkAuth('shop:shop:goMaill');
        $params['uid'] = 1;
        $mall_admin_message =  Db::name('shop_user')->where(["uid"=>1])->find();
        $params['password'] = $mall_admin_message['password'];
        $url = MALL_URL.'/seller/login/transfer?uid=1&password='.$mall_admin_message['password'];
        return redirect($url);
    }
}