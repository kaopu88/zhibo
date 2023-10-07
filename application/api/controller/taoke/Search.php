<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/9
 * Time: 11:52
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\taokegoods\service\Goods;
use app\common\controller\Controller;
use app\taokeshop\service\AnchorGoods;
use bxkj_common\HttpClient;
use think\Db;

class Search extends Controller
{
    /**
     * 超级搜索
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function superSearch()
    {
        $goodsList = [];
        $params = request()->param();
        $keyword = $params['keyword'];
        if(empty($keyword)){
            return $this->jsonError("搜索关键词不能为空");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $isCoupon = empty($params['has_coupon']) ? 0 : 1;
        $sort = isset($params['sort']) ? $params['sort'] : "";
        $type = !empty($params['type']) ? $params['type'] : "B";

        $goods = new Goods();
        if($type == "B"){
            $result = $this->taobao($keyword, $isCoupon, $sort, $page, $pageSize);
            if(!empty($result)) {
                $list = $goods->formatTbGoods($result);
            }

        }elseif ($type == "P"){
            $result = $this->pinduoduo($keyword, $isCoupon, $sort, $page, $pageSize);
            if(!empty($result)) {
                $list = $goods->formatPddGoods($result['goods_list']);
            }

        }elseif ($type == "J"){
            $result = $this->jingdong($keyword, $isCoupon, $sort, $page, $pageSize);
            if(!empty($result)) {
                $list = $goods->formatJdGoods($result);
            }

        }
        if($list){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $anchorGoods = new AnchorGoods();
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            foreach ($list as $key => $value){
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
                $good['shop_name'] = $value['shop_name'];
                $good['video_url'] = isset($value['video_url']) ? $value['video_url'] : "";
                $common = new Common();
                $commission = sprintf("%.2f", $good['discount_price'] * $good['commission_rate'] / 100);
                $good['commission'] = $common->getPurchaseCommission($commission, $value['shop_type'], $userId);
                $good['commission_sub'] = $common->getUpCommission($commission, $value['shop_type'], $userId);
                $good['commission_high'] = $common->getHighCommission($commission, $value['shop_type'], $userId);
                $good['add_window'] = 0;
                $good['add_bag'] = 0;
                $isAdd = 0;
                $isCollect = 0;
                if($userId){
                    $userInfo = $this->user;
                    if($userInfo['is_anchor'] == 1 && $userInfo['taoke_shop'] == 1 && $appConfig['add_goods'] == 1){
                        $data = $live->checkAddGoodBag($userId, $value['goods_id']);
                        $good['add_window'] = $data['add_window'];
                        $good['add_bag'] = $data['add_bag'];
                    }
                    $goodsInfo = Db::name("goods")->where(["goods_id" => $value["goods_id"], "shop_type" => $value['shop_type']])->find();
                    if ($goodsInfo) {
                        $isAdd = $anchorGoods->checkAnchorGood(["user_id" => $userId, "goods_id" => $goodsInfo['id']]);
                        $isCollect = Db::name("good_bag")->where(["goods_db_id" => $goodsInfo['id'], "user_id" => USERID, "shop_type" => $value['shop_type']])->count();
                    }
                }
                $good['is_add'] = $isAdd;
                $good['is_collect'] = $isCollect;
                $goodsList[] = $good;
            }
        }else {
            $goodsList = new \ArrayObject();
        }

        return $this->jsonSuccess($goodsList, "查询成功");
    }

    /**
     * 淘宝
     * @param $keyword
     * @param $isCoupon
     * @param string $order
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    protected function taobao($keyword, $isCoupon, $order="", $page=1, $pageSize=10)
    {
        $para['page_no'] = $page;
        $para['page_size'] = $pageSize;
        $ser = new \app\admin\service\SysConfig();
        $searchConfig = $ser->getConfig("search");
        $searchConfig = json_decode($searchConfig['value'], true);
        if($searchConfig['is_tmall'] == 1){
            $para['is_tmall'] = "true";
        }
        if($searchConfig['is_overseas'] == 1){
            $para['is_overseas'] = "true";
        }
        if($searchConfig['has_coupon'] == 1 || $isCoupon == 1){
            $para['has_coupon'] = "true";
        }
        if(!empty($searchConfig['tk_rate_start'])){
            $para['start_tk_rate'] = $searchConfig['tk_rate_start'] * 100;
        }
        if(!empty($searchConfig['tk_rate_end'])){
            $para['end_tk_rate'] = $searchConfig['tk_rate_end'] * 100;
        }
        if(!empty($searchConfig['price_start'])){
            $para['start_price'] = $searchConfig['price_start'];
        }
        if(!empty($searchConfig['price_end'])){
            $para['end_price'] = $searchConfig['price_end'];
        }
        switch ($order){
            case 1:
                $sort = "price_des";
                break;
            case 2:
                $sort = "price_asc";
                break;
            case 3:
                $sort = "total_sales_des";
                break;
            case 4:
                $sort = "total_sales_asc";
                break;
            case 5:
                $sort = "tk_rate_des";
                break;
            case 6:
                $sort = "tk_rate_asc";
                break;
            default:
                $sort = "";
                break;
        }
        $para['sort'] = $sort;
        $taokeConfig = $ser->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);

        $para['keyword'] = $keyword;
        $para['appkey'] = $taokeConfig['taobao_appkey'];
        $para['secretKey'] = $taokeConfig['taobao_appsecret'];
        $pid = $taokeConfig['taobao_pid'];
        $pidArr = explode("_", $pid);
        $para['adzone_id'] = $pidArr[3];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Search/taobaoSearch", $para)->getData('json');
        header("content-type: application/json");
        return $result['result'];
    }

    /**
     * 多多进宝
     * @param $keyword
     * @param $isCoupon
     * @param string $order
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    protected function pinduoduo($keyword, $isCoupon, $order="", $page=1, $pageSize=10)
    {
        $ser = new \app\admin\service\SysConfig();
        $config = $ser->getConfig("taoke");
        $config = json_decode($config['value'], true);
        $para['client_id'] = $config['pinduoduo_client'];
        $para['client_secret'] = $config['pinduoduo_secret'];
        $para['pid'] = $config['pinduoduo_pid'];
        $para['custom_parameters'] = $config['pinduoduo_pid'];
        $para['keyword'] = $keyword;
        $para['page'] = $page;
        $para['page_size'] = $pageSize;
        if($isCoupon == 1) {
            $para['with_coupon'] = "true";
        }else{
            $para['with_coupon'] = "false";
        }
        if(!empty($order)){
            switch ($order){
                case 1:
                    $sort = 4;
                    break;
                case 2:
                    $sort = 3;
                    break;
                case 3:
                    $sort = 6;
                    break;
                case 4:
                    $sort = 5;
                    break;
                default:
                    $sort = "";
                    break;
            }
            $para['sort_type'] = $sort;
        }
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Search/pddSuperSearch", $para)->getData('json');
        header("content-type: application/json");
        return $result['result'];
    }

    /**
     * 京东联盟
     * @param $keyword
     * @param $isCoupon
     * @param string $order
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    protected function jingdong($keyword, $isCoupon, $order="", $page=1, $pageSize=10)
    {
        $ser = new \app\admin\service\SysConfig();
        $config = $ser->getConfig("taoke");
        $config = json_decode($config['value'], true);
//        $para['appkey'] = $config['jingdong_appkey'];
//        $para['appSecret'] = $config['jingdong_appsecret'];
//        $para['keyword'] = $keyword;
//        $para['page'] = $page;
//        $para['page_size'] = $pageSize;
        $para['is_coupon'] = $isCoupon;
        if(!empty($order)){
            switch ($order){
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
            }
//            $para['sort_name'] = $sortName;
//            $para['sort'] = $sort;
        }
        $httpClient = new HttpClient();
//        $para['api'] = 1;
//        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
//        $result = $httpClient->post(TK_URL."Search/jdSuperSearch", $para)->getData('json');
//        header("content-type: application/json");
        $para['apikey'] = $config['haojingke_apikey'];
        $para['pageindex'] = $page;
        $para['pagesize'] = $pageSize;
        $para['keyword'] = $keyword;
        $para['sortname'] = $order;
        $para['sort'] = $sort;
        $para['iscoupon'] = $isCoupon;
        $para['isunion'] = 1;
        $url = "http://api-gw.haojingke.com/index.php/v1/api/jd/goodslist";
        $result = $httpClient->post($url, $para)->getData('json');
        header("content-type: application/json");
        return $result['data']['data'];
    }

    /**
     * 猜你喜欢
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function guessLike()
    {
        $goodsList = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $deviceValue = isset($params['device_value']) ? $params['device_value'] : "";
        $deviceType = isset($params['device_type']) ? $params['device_type'] : "";
        $ser = new \app\admin\service\SysConfig();
        $taokeConfig = $ser->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        $para['app_key'] = $taokeConfig['taobao_appkey'];
        $para['app_secret'] = $taokeConfig['taobao_appsecret'];
        $pid = $taokeConfig['taobao_pid'];
        $pidArr = explode("_", $pid);
        $para['adzone_id'] = $pidArr[3];

        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['device_value'] = $deviceValue;
        $para['device_type'] = $deviceType;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getUserGuessLike", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']) {
                $live = new \app\taoke\service\Live();
                $userId = empty(USERID) ? 0 : USERID;
                $config = new SysConfig();
                $appConfig = $config->getConfig("app_config");
                $appConfig = json_decode($appConfig['value'], true);

                $goods = new Goods();
                $list = $goods->formatTbGoods($result['result']);
                foreach ($list as $value){
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
                    $good['shop_name'] = $value['shop_name'];
                    $good['video_url'] = empty($value['video_url']) ? "" : $value['video_url'];
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
            return $this->jsonSuccess($goodsList, "获取成功");
        }else{
            return $this->jsonError("获取失败");
        }
    }

    /**
     * 淘宝商品相似推荐
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTbSimilarGoods()
    {
        $goodsList = [];
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError("goods_id不能为空");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;

        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['goods_id'] = $goodsId;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getTbSimilarGoodsList", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']) {
                $goods = new Goods();
                if($result['result']) {
                    $live = new \app\taoke\service\Live();
                    $userId = empty(USERID) ? 0 : USERID;
                    $config = new SysConfig();
                    $appConfig = $config->getConfig("app_config");
                    $appConfig = json_decode($appConfig['value'], true);

                    $list = $goods->formatTbGoods($result['result']);
                    foreach ($list as $value){
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
                        $good['shop_name'] = $value['shop_name'];
                        $good['video_url'] = empty($value['video_url']) ? "" : $value['video_url'];
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
            return $this->jsonSuccess($goodsList, "获取成功");
        }else{
            return $this->jsonError("获取失败");
        }
    }

    /**
     * 拼多多商品相似推荐
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPddSimilarGoods()
    {
        $goodsList = [];
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError("goods_id不能为空");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;

        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['goods_ids'] = $goodsId;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getPddSimilarGoodsList", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']) {
                $goods = new Goods();
                if($result['result']) {
                    $live = new \app\taoke\service\Live();
                    $userId = empty(USERID) ? 0 : USERID;
                    $config = new SysConfig();
                    $appConfig = $config->getConfig("app_config");
                    $appConfig = json_decode($appConfig['value'], true);

                    $list = $goods->formatPddGoods($result['result']['goods_list']);
                    foreach ($list as $value){
                        $good['good_id'] = $value['goods_id'];
                        $good['goods_id'] = $value['pdd_goods_sign'];
                        $good['title'] = $value['title'];
                        $good['short_title'] = $value['title'];
                        $good['img'] = $value['img'];
                        $good['desc'] = $value['desc'];
                        $good['price'] = $value['price'];
                        $good['discount_price'] = $value['discount_price'];
                        $good['commission_rate'] = $value['commission_rate'];
                        $good['coupon_price'] = $value['coupon_price'];
                        $good['volume'] = $value['volume'];
                        $good['shop_type'] = $value['shop_type'];
                        $good['shop_name'] = $value['shop_name'];
                        $good['video_url'] = "";
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
            return $this->jsonSuccess($goodsList, "获取成功");
        }else{
            return $this->jsonError("获取失败");
        }
    }
}