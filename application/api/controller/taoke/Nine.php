<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/26
 * Time: 17:40
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Nine extends Controller
{
    /**
     * 获取九块九包邮
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $type = 1;
        if($type == 1){
            $sort = isset($params['sort']) ? $params['sort'] : 0;
            $cid = isset($params['cid']) ? $params['cid'] : "";
            $para['cid'] = $cid;
            $para['sort'] = $sort;
        }elseif ($type == 2){
            $cid = isset($params['cid']) ? $params['cid'] : -1;
            $para['cids'] = $cid;
        }elseif ($type == 3){
            $sort = isset($params['sort']) ? $params['sort'] : "";
            $para['sort'] = $sort;
        }
        $para['type'] = $type;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Special/getNineGoods", $para)->getData('json');
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
            return $this->jsonSuccess($goodsList, "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 获取拼多多9.9商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPddNineGoodsList()
    {
        $goodsList = [];
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $para['theme_id'] = 7602;
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
                    $good['shop_type'] = "P";
                    $good['video_url'] = "";
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
            $goodsList = array_slice($goodsList, ($page-1)*$pageSize, $pageSize);
            return $this->jsonSuccess($goodsList, "查询成功");
        }else{
            return $this->jsonError("查询失败");
        }
    }
}