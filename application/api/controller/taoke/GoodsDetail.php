<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/28
 * Time: 16:35
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\admin\service\User;
use app\common\controller\Controller;
use  app\taoke\service\Common;
use app\taoke\service\ViewLog;
use app\taokeshop\service\LiveGoods;
use bxkj_common\HttpClient;

class GoodsDetail extends Controller
{
    /**
     * 获取商品详情（先查库，再请求远程接口）
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsDetail()
    {
        $detail = [];
        $params = request()->param();
        $goodsId = $params["goods_id"];
        if(empty($goodsId)){
            return $this->jsonError("商品id不能为空");
        }
        $shopType = $params["shop_type"];
        if(empty($shopType)){
            return $this->jsonError("商品类型不能为空");
        }
        $goods = new \app\taokegoods\service\Goods();
        $detail = $goods->getGoodsInfo(["goods_id" => $goodsId, "shop_type" => $shopType]);
        $httpClient = new HttpClient();
        if(empty($detail)) {
            $para['goods_id'] = $goodsId;
            $para['type'] = $shopType;
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL."Goods/getDetail", $para)->getData('json');
            if($result['code'] != 200){
                return $this->jsonError($result['msg']);
            }
            $detail = $result['result'];
        }else{
            if(empty($detail['gallery_imgs'])){
                $params['api_key'] = config('app.system_deploy')['taoke_api_key'];
                $params['goods_id'] = $goodsId;
                $result = $httpClient->post(TK_URL."Goods/getTbBasciInfo", $params)->getData('json');
                if($result['code'] == 200){
                    if(isset($result['result']['small_images'])) {
                        $galleryImgs = $result['result']['small_images']['string'];
                        $imgs = [];
                        foreach ($galleryImgs as $k => $img){
                            $imgs[$k] = strpos($img, "http") === false ? "http:".$img : $img;
                        }
                        $detail['gallery_imgs'] = implode(",", $imgs);
                    }
                }
            }

            $goodsId = $detail['id'];
            $live = new LiveGoods();
            $liveCount = $live->getTotal(["goods_id" => $goodsId, "live_status" => 1]);//是否有主播讲解此商品
            if($liveCount > 0){
                $liveList = $live->getLiveList(["goods_id" => $goodsId, "live_status" => 1], 0, $liveCount);
                $user = new User();
                foreach ($liveList as $key => $value){
                    $liveArr[$key]['room_id'] = $value['room_id'];//直播房间号
                    $userInfo = $user->getInfo($value['user_id']);
                    $liveArr[$key]['avatar'] = $userInfo['avatar'];//直播用户的头像
                }
                $good['live_list'] = $liveArr;
            }
        }
        $userId = empty(USERID) ? 0 : USERID;
        if($userId && $detail) {
            $this->addViewLog($userId, $detail);
            $common = new Common();
            $commission = sprintf("%.2f", $detail['discount_price'] * $detail['commission_rate'] / 100);
            $detail['commission'] = $common->getPurchaseCommission($commission, $shopType, $userId);
            $detail['commission_sub'] = $common->getUpCommission($commission, $shopType, $userId);
            $detail['commission_high'] = $common->getHighCommission($commission, $shopType, $userId);
            $detail['add_window'] = 0;
            $detail['add_bag'] = 0;
            $live = new \app\taoke\service\Live();
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            $userInfo = $this->user;
            if($userInfo['is_anchor'] == 1 && $userInfo['taoke_shop'] == 1 && $appConfig['add_goods'] == 1){
                $data = $live->checkAddGoodBag($userId, $value['goods_id']);
                $detail['add_window'] = $data['add_window'];
                $detail['add_bag'] = $data['add_bag'];
            }
        }
        return $this->success($detail, "获取成功");
    }

    /**
     * 用户浏览记录
     * @param $userId
     * @param $goodInfo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function addViewLog($userId, $goodInfo)
    {
        $data['user_id'] = $userId;
        $data['goods_id'] = $goodInfo['goods_id'];
        $data['img'] = $goodInfo['img'];
        $data['title'] = $goodInfo['title'];
        $data['shop_type'] = $goodInfo['shop_type'];
        $data['price'] = $goodInfo['price'];
        $data['discount_price'] = $goodInfo['discount_price'];
        $data['coupon_price'] = $goodInfo['coupon_price'];
        $data['volume'] = $goodInfo['volume'];
        $data['commission_rate'] = $goodInfo['commission_rate'];
        $data['add_time'] = time();
        $view = new ViewLog();
        $view->addViewLog($data);
    }
}