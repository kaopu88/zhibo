<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/26
 * Time: 11:16
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Brand extends Controller
{
    /**
     * 置顶品牌
     * @return \think\response\Json
     */
    public function getRecommandBrand()
    {
        $data = [];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Brand/getTopBrand", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']){
                $data = $result['result'];
            }
            return $this->jsonSuccess($data, "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 获取好单库大牌分类
     * @return \think\response\Json
     */
    public function getHdkBrandCate()
    {
        $cateList = [
            "0" => "全部",
            "1" => "母婴童品",
            "2" => "百变女装",
            "3" => "食品酒水",
            "4" => "居家日用",
            "5" => "美妆洗护",
            "6" => "品质男装",
            "7" => "舒适内衣",
            "8" => "箱包配饰",
            "9" => "男女鞋靴",
            "10" => "宠物用品",
            "11" => "数码家电",
            "12" => "车品文体"
        ];
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取好单库大牌列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $data = [];
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $type = 1;
        if($type == 1){
            $brandcat = isset($params['cid']) ? $params['cid'] : 0;
            $para['brandcat'] = $brandcat;
        }elseif ($type == 3){
            $materialId = isset($params['cid']) ? $params['cid'] : 0;
            $para['material_id'] = $materialId;
        }
        $para['type'] = $type;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Brand/getBrandList", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']){
                $data = $result['result'];
                foreach ($result['result'] as $key => $value){
                    $infoData['id'] = $value['brand_id'];
                    $infoData['page'] = 1;
                    $infoData['pageSize'] = 3;
                    $infoData['api_key'] = config('app.system_deploy')['taoke_api_key'];
                    $res = $httpClient->post(TK_URL."Brand/getBrandInfo", $infoData)->getData('json');
                    $list = [];
                    if($res['code'] == 200){
                        if($res['result']){
                            $live = new \app\taoke\service\Live();
                            $userId = empty(USERID) ? 0 : USERID;
                            $config = new SysConfig();
                            $appConfig = $config->getConfig("app_config");
                            $appConfig = json_decode($appConfig['value'], true);
                            $goodsList = $res['result']['goods_list'];
                            if($goodsList){
                                foreach ($goodsList as $val){
                                    $good['goods_id'] = $val['goods_id'];
                                    $good['title'] = $val['title'];
                                    $good['short_title'] = $val['short_title'];
                                    $good['img'] = $val['img'];
                                    $good['desc'] = $val['desc'];
                                    $good['price'] = $val['price'];
                                    $good['discount_price'] = $val['discount_price'];
                                    $good['commission_rate'] = $val['commission_rate'];
                                    $good['coupon_price'] = $val['coupon_price'];
                                    $good['volume'] = $val['volume'];
                                    $good['shop_type'] = $val['shop_type'];
                                    $good['video_url'] = isset($val['video_url']) ? $val['video_url'] : "";
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
                                    $list[] = $good;
                                }
                            }
                            $data[$key]['goods_list'] = $list;
                        }
                    }
                }
            }
            return $this->jsonSuccess($data, "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 获取好单库大牌商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBrandDetail()
    {
        $data = [];
        $params = request()->param();
        $id = $params['brand_id'];
        if(empty($id)){
            return $this->jsonError("品牌id不能为空");
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $order = isset($params['order']) ? $params['order'] : 1;
        $para['id'] = $id;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['order'] = $order;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Brand/getBrandInfo", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']){
                $data['brand_info'] = $result['result']['brand_info'];
                $live = new \app\taoke\service\Live();
                $userId = empty(USERID) ? 0 : USERID;
                $config = new SysConfig();
                $appConfig = $config->getConfig("app_config");
                $appConfig = json_decode($appConfig['value'], true);
                $goodsList = $result['result']['goods_list'];
                if($goodsList){
                    $list = [];
                    foreach ($goodsList as $key => $value){
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
                        $good['video_url'] = isset($value['video_url']) ? $value['video_url'] : "";
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
                        $list[] = $good;
                    }
                }
                $data['goods_list'] = $list;
            }
            return $this->jsonSuccess($data, "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 获取淘联盟大牌商品分类
     * @return \think\response\Json
     */
    public function getTaobaoBrandCate()
    {
        $cateList = [
            "3786" => "综合",
            "3788" => "女装",
            "3792" => "家居家装",
            "3793" => "数码家电",
            "3796" => "鞋包配饰",
            "3794" => "美妆个护",
            "3790" => "男装",
            "3787" => "内衣",
            "3789" => "母婴",
            "3791" => "食品",
            "3795" => "运动户外"
        ];
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取淘联盟大牌商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBrandGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $materialId = $params['material_id'];
        if(empty($materialId)){
            return $this->jsonError("物料id不能为空");
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $para['material_id'] = $materialId;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Theme/getMaterialTheme", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']){
                $live = new \app\taoke\service\Live();
                $userId = empty(USERID) ? 0 : USERID;
                $config = new SysConfig();
                $appConfig = $config->getConfig("app_config");
                $appConfig = json_decode($appConfig['value'], true);
                if($result['result']){
                    foreach ($result['result'] as $key => $value){
                        $good['goods_id'] = $value['item_id'];
                        $good['title'] = $value['title'];
                        $good['short_title'] = $value['sub_title'];
                        $good['img'] = strpos($value['pict_url'], "http") === false ? "http:".$value['pict_url'] : $value['pict_url'];
                        $good['desc'] = $value['item_description'];
                        $good['price'] = $value['zk_final_price'];
                        $good['discount_price'] = $value['zk_final_price'] - $value['coupon_amount'];
                        $good['commission_rate'] = $value['commission_rate'];
                        $good['coupon_price'] = $value['coupon_amount'];
                        $good['volume'] = $value['volume'];
                        $good['shop_type'] = ($value['user_type'] == 1) ? "B" : "C";
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
            return $this->jsonError($result['msg']);
        }
    }

}