<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/3
 * Time: 17:39
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\taoke\service\DuomaiOrder;
use bxkj_common\HttpClient;
use function GuzzleHttp\Psr7\str;

class Duomai extends UserController
{
    public function getAds()
    {
        $sysConfig = new SysConfig();
        $adConfig = $sysConfig->getConfig("duomaiAds");
        $adConfig = json_decode($adConfig['value'], true);
        $hash = $adConfig['hash'];
        $para['hash'] = $hash;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Ads/queryOrder", $para)->getData('json');
        return $this->jsonSuccess($result['result'], "获取成功");
    }

    /**
     * 获取多麦广告列表
     * @return \think\response\Json
     */
    public function getList()
    {
        $data = [];
        $topList = [];
        $list = [];
        $sysConfig = new SysConfig();
        $adConfig = $sysConfig->getConfig("duomaiAds");
        $adConfig = json_decode($adConfig['value'], true);
        $siteid = $adConfig['media_id'];
        $userId = empty(USERID) ? 0 : USERID;

        $duomaiAds = new \app\taoke\service\Duomai();
        $map["is_top"] = 1;
        $map["status"] = 1;
        $topTotal = $duomaiAds->getTotal($map);
        $topList = $duomaiAds->getList($map, 0, $topTotal);
        if($topList){
            foreach ($topList as $topKey => $topValue){
                $topList[$topKey]['promotion_url'] = "http://c.duomai.com/track.php?site_id=".$siteid."&aid=".$topValue['ads_id']."&euid=".$userId."&t=".urlencode($topValue['site_url']);
            }
        }

        $where['status'] = 1;
        $listTotal = $duomaiAds->getTotal($where);
        $list = $duomaiAds->getList($where, 0, $listTotal);
        if($list){
            foreach ($list as $key => $value){
                $list[$key]['promotion_url'] = "http://c.duomai.com/track.php?site_id=".$siteid."&aid=".$value['ads_id']."&euid=".$userId."&t=".urlencode($value['site_url']);
            }
        }
        $data['top'] = $topList;
        $data['list'] = $list;
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 获取可追踪的推广链接
     * @return \think\response\Json
     */
    public function getUserLink()
    {
        $params = request()->param();
        $adsId = $params['ads_id'];
        if(empty($adsId)){
            return $this->jsonError("广告id不能为空");
        }
        $siteUrl = $params['site_url'];
        if(empty($siteUrl)){
            return $this->jsonError("目标url不能为空");
        }
        $sysConfig = new SysConfig();
        $adConfig = $sysConfig->getConfig("duomaiAds");
        $adConfig = json_decode($adConfig['value'], true);
        $userId = USERID;
        $siteid = $adConfig['media_id'];
        $link = "http://c.duomai.com/track.php?site_id=".$siteid."&aid=".$adsId."&euid=".$userId."&t=".urlencode($siteUrl);
        return $this->jsonSuccess($link, "获取成功");
    }

    /**
     * 多麦订单接收
     * @return int
     */
    public function receiveOrder()
    {
        $data = [];
        $query = request()->param();
        $sysConfig = new SysConfig();
        $adConfig = $sysConfig->getConfig("duomaiAds");
        $adConfig = json_decode($adConfig['value'], true);
        $hash = $adConfig['hash'];
        $checksum = $query['checksum'];
        unset($query['checksum'], $query['id']);
        ksort($query);
        $localsum = md5(join('',  array_values($query)).$hash);
        if($localsum == $checksum){
            $orderSn = $query['order_sn'];
            $data['ads_id'] = $query['ads_id'];
            $data['ads_name'] = $query['ads_name'];
            $data['site_id'] = $query['site_id'];
            $data['link_id'] = $query['link_id'];
            $data['euid'] = $query['euid'];
            $data['user_id'] = empty($query['euid']) ? 0 : $query['euid'];
            $data['order_sn'] = $orderSn;
            $orderTime = strtotime($query['order_time']);
            $data['order_time'] = $orderTime;
            $data['orders_price'] = $query['orders_price'];
            $data['siter_commission'] = $query['siter_commission'];
            $data['status'] = $query['status'];
            if($query['status'] == 2){
                $orderList = $this->queryOrderDetail($hash, $query['site_id'], $query['ads_id'], $query['status'], $orderTime - 600, $orderTime + 600);
                if($orderList){
                    foreach ($orderList as $item){
                        if($item['order_sn'] == $orderSn){
                            $data['confirm_price'] = $item['confirm_price'];
                            $data['confirm_siter_commission'] = $item['confirm_siter_commission'];
                            $data['charge_time'] = strtotime($item['charge_time']);
                        }
                    }
                }
            }
            $order = new DuomaiOrder();
            $orderInfo = $order->getOrdeInfo(["order_sn" => $orderSn]);
            //TODO 计算分佣
            if($orderInfo){
                $order->updateOrderInfo(["id"=>$orderInfo['id']], $data);
                return 0;
            }else{
                $order->addOrder($data);
                return 1;
            }
        }else{
            return -1;
        }
    }

    /**
     * 查询订单
     * @param $hash
     * @param string $siteId
     * @param string $adsId
     * @param int $status
     * @param string $timeStart
     * @param string $timeEnd
     * @return array
     */
    protected function queryOrderDetail($hash, $siteId="", $adsId="", $status=0, $timeStart="", $timeEnd="")
    {
        $data = [];
        $para['hash'] = $hash;
        $para['site_id'] = $siteId;
        $para['ads_id'] = $adsId;
        $para['status'] = $status;
        if(!empty($timeStart)) {
            $para['time_from'] = date("Y-m-d H:i:s", $timeStart);
        }
        if(!empty($timeEnd)) {
            $para['time_to'] = date("Y-m-d H:i:s", $timeEnd);
        }
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Ads/queryOrder", $para)->getData('json');
        if($result['code'] == 200){
            $data = $result['result'];
        }
        return $data;
    }

}