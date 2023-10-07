<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/5
 * Time: 10:21
 */
namespace app\api\controller\taoke;

use app\taoke\service\Common;
use app\admin\service\SysConfig;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Theme extends Controller
{
    /**
     * 获取拼多多专题列表
     * @return \think\response\Json
     */
    public function getPddThemeList()
    {
        $themeList = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Theme/getPddThemeList", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']) {
                $themeList = $result['result'];
            }
            return $this->jsonSuccess($themeList, "查询成功");
        }else{
            return $this->jsonError("查询失败");
        }
    }

    /**
     * 获取拼多多专题商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPddThemeGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $themeId = $params["theme_id"];
        if(empty($themeId)){
            return $this->jsonError("theme_id不能为空");
        }
        $para['theme_id'] = $themeId;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Theme/getPddThemeGoodsList", $para)->getData('json');
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
                    $good['desc'] = $value['desc'];
                    $good['price'] = $value['price'];
                    $good['discount_price'] = $value['discount_price'];
                    $good['commission_rate'] = $value['commission_rate'];
                    $good['coupon_price'] = $value['coupon_price'];
                    $good['volume'] = $value['volume'];
                    $good['shop_type'] = $value['shop_type'];
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
            return $this->jsonSuccess($goodsList, "查询成功");
        }else{
            return $this->jsonError("查询失败");
        }
    }

    /**
     * 获取专题频道的推广链接
     * @return \think\response\Json
     */
    public function getPddThemePromoUrl()
    {
        $url = "";
        $params = request()->param();
        $themeIdList = $params["theme_id_list"];
        if(empty($themeIdList)){
            return $this->jsonError("theme_id_list不能为空");
        }
        $para['theme_id_list'] = $themeIdList;
        if(isset($params["generate_we_app"])){
            $para['generate_we_app'] = $params["generate_we_app"];
        }
        if(isset($params["generate_weapp_webview"])){
            $para['generate_weapp_webview'] = $params["generate_weapp_webview"];
        }
        if(isset($params["generate_short_url"])){
            $para['generate_short_url'] = $params["generate_short_url"];
        }
        if(isset($params["generate_schema_url"])){
            $para['generate_schema_url'] = $params["generate_schema_url"];
        }
        if(isset($params["generate_qq_app"])){
            $para['generate_qq_app'] = $params["generate_qq_app"];
        }
        if(isset($params["generate_mobile"])){
            $para['generate_mobile'] = $params["generate_mobile"];
        }
        if(isset($params["custom_parameters"])){
            $para['custom_parameters'] = $params["custom_parameters"];
        }
        $userInfo = $this->user;
        $para['pid'] = $userInfo['pdd_pid'];
        $config = new SysConfig();
        $pddAuth = $config->getConfig("pdd_auth");
        $pddAuth = json_decode($pddAuth['value'], true);
        $para['token'] = $pddAuth['access_token'];

        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Theme/getPddThemePromUrl", $para)->getData('json');
        if($result['code'] == 200){
            $url = $result['result'][0]['url'];
            return $this->jsonSuccess($url, "获取成功");
        }else{
            return $this->jsonError("获取失败");
        }
    }

    /**
     * 物料分类
     * @return \think\response\Json
     */
    public function getMatrialCateList()
    {
        $cateList = [];
        $params = request()->param();
        $type = $params["type"];
        if(empty($type)){
            return $this->jsonError("type不能为空");
        }
        $hotCate = [
            ["name"=>"综合","material_id"=>28026],
            ["name"=>"大服饰","material_id"=>28029],
            ["name"=>"大快消","material_id"=>28027],
            ["name"=>"电器美家","material_id"=>28028]
        ];
        $goodCate = [
            ["name"=>"综合","material_id"=>3756],
            ["name"=>"女装","material_id"=>3767],
            ["name"=>"母婴","material_id"=>3760],
            ["name"=>"食品","material_id"=>3761],
            ["name"=>"鞋包配饰","material_id"=>3762],
            ["name"=>"美妆个护","material_id"=>3763],
            ["name"=>"男装","material_id"=>3764],
            ["name"=>"内衣","material_id"=>3765],
            ["name"=>"家居家装","material_id"=>3758],
            ["name"=>"数码家电","material_id"=>3759],
            ["name"=>"运动户外","material_id"=>3766]
        ];
        $bigCate =[
           ["name"=>"综合","material_id"=>9660],
           ["name"=>"女装","material_id"=>9658	],
           ["name"=>"母婴","material_id"=>9650],
           ["name"=>"食品","material_id"=>9649],
           ["name"=>"鞋包配饰","material_id"=>9648],
           ["name"=>"美妆个护","material_id"=>9653],
           ["name"=>"男装","material_id"=>9654],
           ["name"=>"内衣","material_id"=>9652],
           ["name"=>"家居家装","material_id"=>9655],
           ["name"=>"数码家电","material_id"=>9656],
           ["name"=>"运动户外","material_id"=>9651]
        ];
        $highCate = [
            ["name"=>"综合","material_id"=>13366],
            ["name"=>"女装","material_id"=>13367	],
            ["name"=>"母婴","material_id"=>13374],
            ["name"=>"食品","material_id"=>13375],
            ["name"=>"鞋包配饰","material_id"=>13370],
            ["name"=>"美妆个护","material_id"=>13371],
            ["name"=>"男装","material_id"=>13372],
            ["name"=>"内衣","material_id"=>13373],
            ["name"=>"家居家装","material_id"=>13368],
            ["name"=>"数码家电","material_id"=>13369],
            ["name"=>"运动户外","material_id"=>13376]
        ];
        $pinpaiCate = [
            ["name"=>"综合","material_id"=>3786],
            ["name"=>"女装","material_id"=>3788],
            ["name"=>"母婴","material_id"=>3789],
            ["name"=>"食品","material_id"=>3791],
            ["name"=>"鞋包配饰","material_id"=>3796],
            ["name"=>"美妆个护","material_id"=>3794],
            ["name"=>"男装","material_id"=>3790],
            ["name"=>"内衣","material_id"=>3787],
            ["name"=>"家居家装","material_id"=>3792],
            ["name"=>"数码家电","material_id"=>3793],
            ["name"=>"运动户外","material_id"=>3795]
        ];
        $muyingCate = [
            ["name"=>"备孕","material_id"=>4040],
            ["name"=>"0至6个月","material_id"=>4041],
            ["name"=>"4至6岁","material_id"=>4044],
            ["name"=>"7至12个月","material_id"=>4042],
            ["name"=>"1至3岁","material_id"=>4043],
            ["name"=>"7至12岁","material_id"=>4045]
        ];
        switch ($type){
            case "time_hot":
                $cateList = $hotCate;
                break;
            case "good_quan":
                $cateList = $goodCate;
                break;
            case "big_quan":
                $cateList = $bigCate;
                break;
            case "high_commission":
                $cateList = $highCate;
                break;
            case "pinpai":
                $cateList = $pinpaiCate;
                break;
            case "muying":
                $cateList = $muyingCate;
                break;
        }
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 淘联盟物料专题活动商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMaterialThemeGoods()
    {
        $goodsList = [];
        $params = request()->param();
        $materialId = $params["material_id"];
        if(empty($materialId)){
            return $this->jsonError("material_id不能为空");
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['material_id'] = $materialId;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Theme/getMaterialTheme", $para)->getData('json');
        if($result['code'] == 200){
            $goods = new \app\taokegoods\service\Goods();
            if($result['result']) {
                $list = $goods->formatTbGoods($result['result']);
                $live = new \app\taoke\service\Live();
                $userId = empty(USERID) ? 0 : USERID;
                $config = new SysConfig();
                $appConfig = $config->getConfig("app_config");
                $appConfig = json_decode($appConfig['value'], true);

                foreach ($list as $key => $value) {
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
            return $this->jsonSuccess($goodsList, "获取成功");
        }else{
            return $this->jsonError("获取失败");
        }
    }

}