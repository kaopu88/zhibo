<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/14
 * Time: 16:15
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\taoke\service\Estimate;
use app\taoke\service\JdOrder;
use app\taoke\service\Order as taokeOrder;
use app\taoke\service\PddOrder;
use app\taoke\service\Rebate;
use app\taoke\service\TaobaoOrder;
use bxkj_common\HttpClient;

class Order extends UserController
{
    /**
     * 获取订单列表
     * @return \think\response\Json
     */
    public function getOrderList()
    {
        $orderList = [];
        $userId = USERID;
        $params = request()->param();
        $page = !empty($params['page']) ? $params['page'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 10;
        $orderStatus = !empty($params['order_status']) ? $params['order_status'] : "";//订单状态
        $rebate = !empty($params['rebate']) ? $params['rebate'] : "";//返利状态
        $type = !empty($params['shop_type']) ? $params['shop_type'] : "";//平台类型
        $sort = !empty($params['sort']) ? $params['sort'] : "";//排序类型
        $timeType = !empty($params['time_type']) ? $params['time_type'] : "";//时间类型
        $startTime = !empty($params['start_time']) ? $params['start_time'] : "";//开始时间
        $endTime = !empty($params['end_time']) ? $params['end_time'] : "";//结束时间

        $where['user_id'] = $userId;
        if(!empty($rebate)){
            $where['rebate'] = $rebate;
        }
        if(!empty($orderStatus)){
            $where['order_status'] = $orderStatus;
        }
        if(!empty($type)){
            $where['type'] = $type;
        }
        if(!empty($timeType)){
            $where['time_type'] = $timeType;
            $where['start_time'] = $startTime;
            $where['end_time'] = $endTime;
        }
        if(!empty($sort)){
            switch ($sort){
                case "time_desc":
                    $orderBy = 'o.add_time desc';
                    break;
                case "time_asc":
                    $orderBy = 'o.add_time asc';
                    break;
                case "price_desc":
                    $orderBy = 'o.pay_price desc';
                    break;
                case "price_asc":
                    $orderBy = 'o.pay_price asc';
                    break;
                default:
                    $orderBy = 'o.id desc';
                    break;
            }
        }
        $offset = ($page-1)*$pageSize;

        $order = new taokeOrder();
        $orderList = $order->getOrderList($where, $offset, $pageSize, $orderBy);
        if($orderList){
            $estimate = new Estimate();//未结算--预估
            $rebate = new Rebate();//已结算
            foreach ($orderList as $key => $value){
                $where['goods_order'] = $value['goods_order'];
                $where['goods_sonorder'] = $value['goods_sonorder'];
                if($value['rebate'] == 1){
                    $log = $rebate->getLog($where);
                }else{
                    $log = $estimate->getLog($where);
                }
                if($log) {
                    $orderList[$key]['sub_commission'] = json_decode($log['value'], true);
                }
                $orderList[$key]['order_time'] = date("Y-m-d H:i:s", $value['create_time']);
                $orderList[$key]['end_time'] = date("Y-m-d H:i:s", $value['earning_time']);
            }
        }
        return $this->jsonSuccess($orderList, "查询成功");
    }

    /**
     * 订单找回
     * @return \think\response\Json
     */
    public function findOrder()
    {
        $orderInfo = [];
        $userId = USERID;
        $userInfo = $this->user;
        $params = request()->param();
        $orderNo = $params['order_no'];
        if($orderNo){
            return $this->jsonError("订单号不能为空");
        }
        $order = new taokeOrder();
        $orderInfo = $order->getOrderInfo(["goods_order" => $orderNo]);
        if(empty($orderInfo)){
            $orderInfo = $order->getOrderInfo(["goods_sonorder" => $orderNo]);
            if(empty($orderInfo)) {
                return $this->jsonError("查无此单");
            }
        }
        if($orderInfo['user_id'] > 0){
            return $this->jsonError("此单已归属其他用户");
        }
        if($orderInfo['type'] == 0){
            $upData['relation_id'] = $userInfo['relation_id'];
            $upData['special_id'] = $userInfo['special_id'];
        }elseif ($orderInfo['type'] == 1){
            $upData['position_id'] = $userInfo['pdd_pid'];
            $upData['position_name'] = $userInfo['nickname'];
        }elseif ($orderInfo['type'] == 2){
            $upData['position_id'] = $userInfo['jd_pid'];
            $upData['position_name'] = $userInfo['nickname'];
        }
        $upData['user_id'] = $userId;
        $status = $order->updateOrderInfo(["id" => $orderInfo['id']], $upData);
        if($status){
            return $this->jsonSuccess("", "找回成功");
        }else{
            return $this->jsonError("找回失败");
        }
    }

    /**
     * 请求淘宝订单
     * @param array $params
     */
    public function getTbOrder($params=[])
    {
        if(empty($params)) {
            $params = request()->param();
        }
        $para['start_time'] = $params['start_time'];
        $para['end_time'] = $params['end_time'];
        if(!empty($params['tk_status'])){
            $para['tk_status'] = $params['tk_status'];
        }
        if(!empty($params['query_type'])){
            $para['query_type'] = $params['query_type'];
        }
        if(!empty($params['member_type'])){
            $para['member_type'] = $params['member_type'];
        }
        if(!empty($params['order_scene'])){
            $para['order_scene'] = $params['order_scene'];
        }
        if(!empty($params['jump_type'])){
            $para['jump_type'] = $params['jump_type'];
        }
        if(!empty($params['position_index'])){
            $para['position_index'] = $params['position_index'];
        }
        $config = new SysConfig();
        $taobaoAuth = $config->getConfig("taoke_auth");
        $taobaoAuth = json_decode($taobaoAuth['value'], true);
        $para['session'] = $taobaoAuth['access_token'];
        $http = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $para['page'] = 1;
        $para['pageSize'] = 100;
        $result = $http->post(TK_URL."Order/getTbOrder", $para)->getData("json");
        if($result['code'] == 200){
            $orderList = $result['result']['list'];
            if($orderList){
                $tbOrder = new TaobaoOrder();
                $tbOrder->addOrder($orderList);
                if($result['result']['has_next']){//如果有翻页
                    $param = $params;
                    $param['page'] = $params['page'] + 1;
                    $param['pageSize'] = 100;
                    $param['jump_type'] = 1;
                    $param['position_index'] = $result['result']['position_index'];
                    $this->getTbOrder($param);
                }
            }

        }
    }

    /**
     * 请求拼多多订单
     */
    public function getPddOrder()
    {
        $params = request()->param();
        $para['start_time'] = $params['start_time'];
        $para['end_time'] = $params['end_time'];
        $config = new SysConfig();
        $taokeConfig = $config->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        if(!empty($taokeConfig['pinduoduo_client']) && !empty($taokeConfig['pinduoduo_secret'])) {
            $para['client_id'] = $taokeConfig['pinduoduo_client'];
            $para['client_secret'] = $taokeConfig['pinduoduo_secret'];

            $http = new HttpClient();
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $para['pageSize'] = 100;
            $result = $http->post(TK_URL . "Order/getPddOrder", $para)->getData("json");
            if ($result['code'] == 200) {
                $orderList = $result['result']['list'];
                if ($orderList) {
                    $pddOrder = new PddOrder();
                    $pddOrder->addOrder($orderList);
                }
            }

        }
    }

    /**
     * 获取京东订单
     */
    public function getJdOrder($params=[])
    {
        if(empty($params)) {
            $params = request()->param();
        }
        $para['time'] = $params['time'];
        $para['type'] = $params['type'];
        $config = new SysConfig();
        $taokeConfig = $config->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        if(!empty($taokeConfig['jingdong_appkey']) && !empty($taokeConfig['jingdong_appsecret'])) {
            $para['appkey'] = $taokeConfig['jingdong_appkey'];
            $para['appSecret'] = $taokeConfig['jingdong_appsecret'];

            $http = new HttpClient();
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $para['page'] = 1;
            $para['pageSize'] = 100;
            $result = $http->post(TK_URL . "Order/getJdOrder", $para)->getData("json");
            if ($result['code'] == 200) {
                $orderList = $result['result']['list'];
                if ($orderList) {
                    $jdOrder = new JdOrder();
                    $jdOrder->addOrder($orderList);
                    if($result['result']['hasMore']){//如果有翻页
                        $param = $params;
                        $param['page'] = $params['page'] + 1;
                        $param['pageSize'] = 100;
                        $this->getJdOrder($param);
                    }
                }
            }

        }
    }
}