<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/25
 * Time: 13:57
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Seckill extends Controller
{
    public function getCate()
    {
        $cateList = array(
            array("id" => 6, "time" => "00:00"),
            array("id" => 7, "time" => "10:00"),
            array("id" => 8, "time" => "12:00"),
            array("id" => 9, "time" => "15:00"),
            array("id" => 10, "time" => "20:00"),
            array("id" => 11, "time" => "24:00"),
        );
        foreach ($cateList as $key => $value){
            $now = $this->handleHdkTime();
            if($value['id'] < $now){
                $type = 0;
            }elseif ($value['id'] == $now){
                $type = 1;
            }else{
                $type = 2;
            }
            $cateList[$key]['type'] = $type;
        }
        return $this->jsonSuccess($cateList, '获取成功');
    }

    /**
     * 获取限时秒杀
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $goodsList = [];
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $timeType = isset($params['time_type']) ? $params['time_type'] : "";

        $para['page'] = $page;
        $para['pageSize'] = $pageSize;
        $para['type'] = 1;
        $para['time_type'] = (int)$timeType;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Tqg/getTqgGoods", $para)->getData('json');
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
            return $this->jsonSuccess($goodsList, '获取成功');
        }else{
            return $this->jsonError("获取失败");
        }
    }

    protected function handleHdkTime($time="")
    {
        if(empty($time) || $time > date("G")) {
            $now = date("G");
        }else{
            $now = $time;
        }
        if($now < 10) {
            $num = 6;
        }elseif ($now >= 10 && $now < 12){
            $num = 7;
        }elseif ($now >= 12 && $now < 15){
            $num = 8;
        }elseif ($now >= 15 && $now < 20){
            $num = 9;
        }elseif ($now >= 20){
            $num = 10;
        }
        return $num;
    }

    protected function handleDtkTime($time="")
    {
        if(empty($time) || $time > date("G")) {
            $now = date("G");
        }else{
            $now = $time;
        }
        if ($now < 8) {
            $num = 0;
        } elseif ($now >= 8 && $now < 10) {
            $num = 1;
        } elseif ($now >= 10 && $now < 13) {
            $num = 2;
        } elseif ($now >= 13 && $now < 15) {
            $num = 3;
        } elseif ($now >= 15 && $now < 17) {
            $num = 4;
        } elseif ($now >= 17 && $now < 19) {
            $num = 5;
        } elseif ($now >= 19 && $now < 20) {
            $num = 6;
        } elseif ($now >= 20 && $now < 21) {
            $num = 7;
        } elseif ($now >= 21) {
            $num = 8;
        }
        return $num;
    }

    protected function handleTbTime($time="")
    {
        if(empty($time) || $time > date("G")) {
            $now = date("G");
        }else{
            $now = $time;
        }
        if ($now < 10) {
            $num = 0;
        } elseif ($now >= 8 && $now < 10) {
            $num = 0;
        } elseif ($now >= 10 && $now < 11) {
            $num = 1;
        } elseif ($now >= 11 && $now < 12) {
            $num = 2;
        } elseif ($now >= 12 && $now < 13) {
            $num = 3;
        } elseif ($now >= 13 && $now < 14) {
            $num = 4;
        } elseif ($now >= 14 && $now < 15) {
            $num = 5;
        } elseif ($now >= 15 && $now < 16) {
            $num = 6;
        } elseif ($now >= 16 && $now < 17) {
            $num = 7;
        } elseif ($now >= 17 && $now < 18) {
            $num = 8;
        } elseif ($now >= 18 && $now < 19) {
            $num = 9;
        } elseif ($now >= 19 && $now < 20) {
            $num = 10;
        } elseif ($now >= 20 && $now < 21) {
            $num = 11;
        } elseif ($now >= 21 && $now < 22) {
            $num = 12;
        } elseif ($now >= 22 && $now < 23) {
            $num = 13;
        } elseif ($now >= 23) {
            $num = 14;
        }
        return $num;
    }
}