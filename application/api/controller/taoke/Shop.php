<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/18
 * Time: 17:13
 */
namespace app\api\controller\taoke;

use app\taokegoods\service\Goods;
use app\admin\service\SysConfig;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Shop extends Controller
{
    /**
     * 获取拼多多店铺商品列表
     * @return \think\response\Json
     * @throws \bxkj_module\exception\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPddShopGoods()
    {
        $goodsList = [];
        $params = request()->param();
        $shopId = $params["shop_id"];
        if(empty($shopId)){
            return $this->jsonError("shop_id不能为空");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['shop_id'] = $shopId;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getPddShopGoodsList", $para)->getData('json');
        if($result['code'] == 200 && $result['result']){
            $list = $result['result'];
        }
        if($list){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            foreach ($list as $key => $value){
                $good = [];
                $good['goods_id'] = $value['goods_id'];
                $good['title'] = $value['goods_name'];
                $good['short_title'] = $value['goods_name'];
                $good['img'] = $value['goods_thumbnail_url'];
                $good['desc'] = $value['goods_desc'];
                $good['price'] = floatval($value['min_group_price'] / 100);
                $good['coupon_price'] = floatval($value['coupon_discount'] / 100);
                $good['commission_rate'] = floatval($value['promotion_rate'] / 10);
                $good['discount_price'] = floatval($good['price'] - $good['coupon_price']);
                $good['volume'] = $value['sales_tip'];
                $good['shop_type'] = "P";
                $good['video_url'] = "";
                $common = new \app\taoke\service\Common();
                $commission = sprintf("%.2f", $good['discount_price'] * $good['commission_rate'] / 100);
                $good['commission'] = $common->getPurchaseCommission($commission, "p", $userId);
                $good['commission_sub'] = $common->getUpCommission($commission, "P", $userId);
                $good['commission_high'] = $common->getHighCommission($commission, "P", $userId);
                $good['add_window'] = 0;
                $good['add_bag'] = 0;
                if($userId){
                    $userInfo = $this->user;
                    if($userInfo['is_anchor'] == 1 && $userInfo['taoke_shop'] == 1 && $appConfig['add_goods'] == 1){
                        $data = $live->checkAddGoodBag($userId, $value['goods_id']);
                        $good['add_window'] = $data['add_window'];
                        $good['add_bag'] = $data['add_bag'];
                    }
                }
                $goodsList[] = $good;
            }
        }
        return $this->jsonSuccess($goodsList, "查询成功");
    }

    /**
     * 获取京东店铺商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getJdShopGoods()
    {
        $goodsList = [];
        $params = request()->param();
        $shopId = $params["shop_id"];
        if(empty($shopId)){
            return $this->jsonError("shop_id不能为空");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['shop_id'] = $shopId;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getJdShopGoodsList", $para)->getData('json');
        if($result['code'] == 200 && $result['result']){
            $list = $result['result'];
        }
        if($list){
            $goods = new Goods();
            $list = $goods->formatJdGoods($list);
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            foreach ($list as $key => $value){
                $good = [];
                $good['goods_id'] = $value['goods_id'];
                $good['title'] = $value['title'];
                $good['short_title'] = $value['title'];
                $good['img'] = $value['img'];
                $good['desc'] = $value['goods_desc'];
                $good['price'] = floatval($value['price']);
                $good['coupon_price'] = floatval($value['coupon_price']);
                $good['commission_rate'] = floatval($value['commission_rate']);
                $good['discount_price'] = floatval($good['discount_price']);
                $good['volume'] = $value['volume'];
                $good['shop_type'] = "J";
                $good['video_url'] = "";
                $common = new \app\taoke\service\Common();
                $commission = sprintf("%.2f", $value['discount_price'] * $value['commission_rate'] / 100);
                $good['commission'] = $common->getPurchaseCommission($commission, "J", $userId);
                $good['commission_sub'] = $common->getUpCommission($commission, "J", $userId);
                $good['commission_high'] = $common->getHighCommission($commission, "J", $userId);
                $good['add_window'] = 0;
                $good['add_bag'] = 0;
                if($userId){
                    $userInfo = $this->user;
                    if($userInfo['is_anchor'] == 1 && $userInfo['taoke_shop'] == 1 && $appConfig['add_goods'] == 1){
                        $data = $live->checkAddGoodBag($userId, $value['goods_id']);
                        $good['add_window'] = $data['add_window'];
                        $good['add_bag'] = $data['add_bag'];
                    }
                }
                $goodsList[] = $good;
            }
        }
        return $this->jsonSuccess($goodsList, "查询成功");
    }
}