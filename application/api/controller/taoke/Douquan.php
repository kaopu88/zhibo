<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/26
 * Time: 18:42
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Douquan extends Controller
{
    /**
     * 获取抖券分类
     * @return \think\response\Json
     */
    public function getCateList()
    {
        $cateList = [
            "0" => "热门",
            "1" => "百变穿搭",
            "2" => "2时尚潮男",
            "3" => "3舒适好物",
            "4" => "4美妆达人",
            "5" => "魅力配饰",
            "6" => "步履不停",
            "7" => "包罗万象",
            "8" => "萌娃驾到",
            "9" => "宝妈神器",
            "10" => "居家好物",
            "11" => "吃货专区",
            "12" => "数码达人",
            "13" => "用电能手",
            "14" => "其他",
            "15" => "伴你前行",
            "16" => "学习娱乐",
            "17" => "萌宠世界"
        ];
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取抖券商品
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
        $cid = isset($params['cid']) ? $params['cid'] : 0;
        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['cid'] = $cid;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Douquan/geGoodsList", $para)->getData('json');
        if($result['code'] == 200){
            $live = new \app\taoke\service\Live();
            $userId = empty(USERID) ? 0 : USERID;
            $config = new SysConfig();
            $appConfig = $config->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);
            if($result['result']){
                foreach ($result['result'] as $key => $value){
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
                    $good['dy_trill_id'] = $value['dy_trill_id'];
                    $good['dy_video_url'] = $value['dy_video_url'];
                    $good['dy_video_url'] = $value['dy_video_url'];
                    $good['dy_video_like_count'] = $value['dy_video_like_count'];
                    $good['dy_video_share_count'] = $value['dy_video_share_count'];
                    $good['dy_video_title'] = $value['dy_video_title'];
                    $good['first_frame'] = $value['first_frame'];
                    $good['dynamic_image'] = $value['dynamic_image'];
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
}