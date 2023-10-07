<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/19
 * Time: 17:16
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Goods extends Controller
{
    /**
     * 用户未登录首页商品--大淘客-热榜
     * @return \think\response\Json
     */
    public function index()
    {
        $goodsList = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['type'] = 2;
        $para['rank_type'] = 1;
        $url = TK_URL."Hot/getHotList";
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post($url, $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']) {
                foreach ($result['result'] as $key => $value) {
                    $good['goods_id'] = $value['goods_id'];
                    $good['title'] = $value['title'];
                    $good['short_title'] = $value['short_title'];
                    $good['img'] = $value['img'];
                    $good['desc'] = $value['desc'];
                    $good['price'] = $value['price'];
                    $good['discount_price'] = $value['discount_price'];
                    $good['commission_rate'] = $value['commission_rate'];
                    $good['coupon_price'] = $value['coupon_price'];
                    $good['volume'] = $value['volume'];
                    $good['shop_type'] = $value['shop_type'];
                    $good['commission'] = 0;
                    $good['commission_sub'] = 0;
                    $good['commission_high'] = 0;
                    $goodsList[] = $good;
                }
            }
            return $this->jsonSuccess($goodsList, '获取成功');
        }else{
            return $this->jsonError("获取失败");
        }
    }

    /**
     * 获取商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $level = !empty($params["level"]) ? $params["level"] : 1;
        if(!in_array($level, ['1', '2'])){
            return $this->jsonError("层级非法");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        if(!empty($params['sort'])){
            switch ($params['sort']){
                case 1:
                    $sort = "discount_price desc";
                    $dtkSort = 5;
                    break;
                case 2:
                    $sort = "discount_price asc";
                    $dtkSort = 6;
                    break;
                case 3:
                    $sort = "volume desc";
                    $dtkSort = 2;
                    break;
                case 4:
                    $sort = "volume asc";
                    $dtkSort = 2;
                    break;
                default:
                    $sort = "update_time desc,create_time desc";
                    $dtkSort = 0;
                    break;
            }
            $data['sort'] = $sort;
        }
        $data['status'] = 1;
        $data['goods_type'] = 0;
        $cid = $params['cid'];
        if(!empty($cid)) {
            $cate = new \app\taokegoods\service\Cate();
            $cateInfo = $cate->getInfo(["cate_id" => $cid]);
            $data['cate_id'] = $cateInfo['dtk_cate_id'];
            $para['cid'] = $cid;
        }
        $list = [];
        if($level == 1) {//一级分类商品
            $offset = ($page-1)*$pageSize;
            $goods = new \app\taokegoods\service\Goods();
            $list = $goods->getList($data, $offset, $pageSize);

        }elseif ($level == 2){//二级分类商品
            $para['page'] = $page;
            $para['pageSize'] = $pageSize;
            $para['sort'] = $dtkSort;
            $httpClient = new HttpClient();
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL."Goods/getDtkSecGoodsList", $para)->getData('json');
            if($result['code'] == 200 && $result['result']){
                $list = $result['result'];
            }

        }
        if($list){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);

            foreach ($list as $key => $value){
                $good['goods_id'] = $value['goods_id'];
                $good['cate_id'] = isset($value['cate_id']) ? $value['cate_id'] : 0;
                $good['title'] = $value['title'];
                $good['short_title'] = $value['short_title'];
                $good['img'] = $value['img'];
                $good['desc'] = $value['desc'];
                $good['price'] = $value['price'];
                $good['discount_price'] = $value['discount_price'];
                $good['commission_rate'] = $value['commission_rate'];
                $good['coupon_price'] = $value['coupon_price'];
                $good['volume'] = $value['volume'];
                $good['shop_type'] = $value['shop_type'];
                $good['shop_name'] = $value['shop_name'];
                $good['video_url'] = isset($value['video_url']) ? $value['video_url'] : "";
                $good['is_new'] = isset($value['is_new']) ? $value['is_new'] : 0;
                $good['is_top'] = isset($value['is_top']) ? $value['is_top'] : 0;
                $common = new Common();
                $commission = sprintf("%.2f", $good['discount_price'] * $good['commission_rate'] / 100);
                $good['commission'] = $common->getPurchaseCommission($commission, $value['shop_type'], $userId);
                $good['commission_sub'] = $common->getUpCommission($commission, $value['shop_type'], $userId);
                $good['commission_high'] = $common->getHighCommission($commission, $value['shop_type'], $userId);
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
     * 获取二级分类商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSecGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        if(empty($params['cid'])){
            return $this->jsonError("二级分类id不能为空");
        }
        $para['cid'] = $params['cid'];
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        if(!empty($params['sort'])){
            switch ($params['sort']){
                case 1:
                    $sort = 5;
                    break;
                case 2:
                    $sort = 6;
                    break;
                case 3:
                    $sort = 2;
                    break;
                default:
                    $sort = 0;
                    break;
            }
            $para['sort'] = $sort;
        }
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getDtkSecGoodsList", $para)->getData('json');
        if($result['code'] == 200){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            if($result['result']) {
                foreach ($result['result'] as $key => $value) {
                    $good['goods_id'] = $value['goods_id'];
                    $good['title'] = $value['title'];
                    $good['short_title'] = $value['short_title'];
                    $good['img'] = $value['img'];
                    $good['price'] = $value['price'];
                    $good['desc'] = $value['desc'];
                    $good['shop_type'] = $value['shop_type'];
                    $good['shop_name'] = $value['shop_name'];
                    $good['discount_price'] = $value['discount_price'];
                    $good['commission_rate'] = $value['commission_rate'];
                    $good['coupon_price'] = $value['coupon_price'];
                    $good['volume'] = $value['volume'];
                    $common = new Common();
                    $commission = sprintf("%.2f", $good['discount_price'] * $good['commission_rate'] / 100);
                    $good['commission'] = $common->getPurchaseCommission($commission, $value['shop_type'], $userId);
                    $good['commission_sub'] = $common->getUpCommission($commission, $value['shop_type'], $userId);
                    $good['commission_high'] = $common->getHighCommission($commission, $value['shop_type'], $userId);
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
        }
        return $this->jsonSuccess($goodsList, "查询成功");
    }

    /**
     * 获取拼多多分类商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPddGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        switch ($params['sort']){
            case 1:
                $sort = 10;
                break;
            case 2:
                $sort = 9;
                break;
            case 3:
                $sort = 6;
                break;
            case 4:
                $sort = 5;
                break;
            default:
                $sort = 0;
                break;
        }
        $data['sort'] = $sort;
        if(!empty($params['cate_id'])) {
            $data['cate_id'] = $params['cate_id'];
        }
        $data['page'] = $page;
        $data['pageSize'] = $pageSize;

        $name = "pdd-goods-list"."-pageSize-".$pageSize."-sort-".$sort."-cate_id-".$params['cate_id']."-page-";
        $listId = cache(md5($name.$page));
        if(!empty($listId)){
            $data['list_id'] = $listId;
        }

        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getPddCateGoodsList", $data)->getData('json');
        if($result['code'] == 200){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            if($result['result']['goods_list']) {
                foreach ($result['result']['goods_list'] as $key => $value) {
                    $good['good_id'] = $value['goods_id'];
                    $good['goods_id'] = $value['pdd_goods_sign'];
                    $good['title'] = $value['goods_name'];
                    $good['short_title'] = $value['goods_name'];
                    $good['desc'] = $value['goods_desc'];
                    $good['img'] = $value['goods_thumbnail_url'];
                    $good['price'] = $value['min_group_price'] / 100;//原价
                    $good['shop_type'] = "P";
                    $good['shop_name'] = $value['mall_name'];
                    $good['discount_price'] = ($value['min_group_price'] - $value['coupon_discount']) / 100;//券后价
                    $good['commission_rate'] = $value['promotion_rate'] / 10;
                    $good['coupon_price'] = $value['coupon_discount'] / 100;//券面额
                    $good['volume'] = $value['sales_tip'];
                    $common = new Common();
                    $commission = sprintf("%.2f", $good['discount_price'] * $good['commission_rate'] / 100);
                    $good['commission'] = $common->getPurchaseCommission($commission, "P", $userId);
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
            cache(md5($name.($page+1)), $result['result']['list_id']);
        }
        return $this->jsonSuccess($goodsList, "查询成功");
    }

    /**
     * 获取京东分类商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getJdGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        switch ($params['sort']){
            case 1:
                $sortName = "price";
                $sort = "desc";
                break;
            case 2:
                $sortName = "price";
                $sort = "asc";
                break;
            case 3:
                $sortName = "inOrderCount30Days";
                $sort = "desc";
                break;
            case 4:
                $sortName = "inOrderCount30Days";
                $sort = "asc";
                break;
            default:
                $sortName = "";
                $sort = "";
                break;
        }
        $data['sort_name'] = $sortName;
        $data['sort'] = $sort;
        if(!empty($params['cate_id'])) {
            $data['cate_id'] = $params['cate_id'];
        }
        $data['page'] = $page;
        $data['pageSize'] = $pageSize;
        $data['is_coupon'] = 1;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getJdCateGoodsList", $data)->getData('json');
        if($result['code'] == 200){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            if($result['result']) {
                foreach ($result['result'] as $key => $value) {
                    $good['goods_id'] = $value['skuId'];
                    $good['title'] = $value['skuName'];
                    $good['short_title'] = $value['skuName'];
                    $good['desc'] = $value['skuName'];
                    $good['img'] = $value['imageInfo']['imageList'][0]['url'];
                    $good['price'] = $value['priceInfo']['price'];//原价
                    $good['shop_type'] = "J";
                    $good['shop_name'] = $value['shopInfo']['shopName'];
                    if (isset($value['couponInfo']) && !empty($value['couponInfo']['couponList'])) {
                        $coupon_price = floatval($value['couponInfo']['couponList'][0]['discount']);
                    } else {
                        $coupon_price = 0;
                    }
                    $good['coupon_price'] = $coupon_price;//券面额
                    $good['discount_price'] = $good['price'] - $coupon_price;//券后价
                    $good['commission_rate'] = $value['commissionInfo']['commissionShare'];
                    $good['volume'] = $value['inOrderCount30Days'];
                    $common = new Common();
                    $commission = sprintf("%.2f", $good['discount_price'] * $good['commission_rate'] / 100);
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
        }
        return $this->jsonSuccess($goodsList, "查询成功");
    }
}