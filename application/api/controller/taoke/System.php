<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/25
 * Time: 11:51
 */
namespace app\api\controller\taoke;

use app\admin\service\AdminNotice;
use app\admin\service\Robot;
use app\admin\service\SysConfig;
use app\common\controller\Controller;
use app\taoke\service\Module;
use app\taoke\service\ModulePosition;
use app\taokeshop\service\LiveGoods;
use bxkj_common\HttpClient;

class System extends Controller
{
    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = [];
        $where['status'] = 1;
        $where['application_index'] = 0;
        $where['type'] = "app";
        $where['order'] = "sort asc";
        $modulePosition = new ModulePosition();
        $positionTotal = $modulePosition->getTotal($where);
        $positionList = $modulePosition->getList($where, 0, $positionTotal);
        if($positionList){
            $sysConfig = new SysConfig();
            $appConfig = $sysConfig->getConfig("app_config");
            $appConfig = json_decode($appConfig['value'], true);

            $module = new Module();
            $map['status'] = 1;
            $map['order'] = "sort asc";
            foreach($positionList as $key => $value) {
                $moduleName = $value['pinyin'];
                $data[$moduleName]['name'] = $value['name'];
                $data[$moduleName]['img'] = $value['img'];
                $data[$moduleName]['desc'] = $value['desc'];
                $mData = [];
                if ($moduleName == "preferential_headlines") {//优惠头条
                    $data[$moduleName]['headlines_type'] = $appConfig['headlines']['type'];// 1：佣金记录；2：公告；3：关联商品
                    $mData = $this->getHeadlinesData($appConfig);
                    $data[$moduleName]['data'] = $mData;

                } else {
                    $map['position_id'] = $value['id'];
                    $moduleTotal = $module->getTotal($map);
                    $moduleList = $module->getList($map, 0, $moduleTotal);
                    if ($moduleList) {
                        foreach ($moduleList as $k => $v) {
                            $mData[$k]['title'] = $v['title'];
                            $mData[$k]['desc'] = $v['desc'];
                            $mData[$k]['image'] = $v['image'];
                            $mData[$k]['selected_image'] = $v['selected_image'];
                            $mData[$k]['bg_color'] = empty($v['bg_color']) ? [] : json_decode($v['bg_color']);
                            $mData[$k]['page_id'] = $v['page_id'];
                            $mData[$k]['open_type'] = $v['open_type'];
                            $mData[$k]['open_url'] = $v['open_url'];
                            $mData[$k]['params'] = $v['params'];
                            if($v['page_id']){
                                $info = $module->getPageInfo($v['page_id']);
                                $mData[$k]['open_url'] = $info['open_url'];
                            }
                        }
                    }
                    $data[$moduleName]['modules'] = $mData;
                }
            }

            if($appConfig['seckill'] == 1){
                $goodsList = [];
                $goodsList = $this->getSeckillData();
                $data['sec_kill']['data'] = $goodsList;
            }

            if($appConfig['today_buy'] == 1){
                $goodsList = [];
                $goodsList = $this->getTodaybuyData();
                $data['today_buy']['data'] = $goodsList;
            }

            if($appConfig['taoke_live'] == 1){
                $goodsList = [];
                $goodsList = $this->getLiveData();
                $data['live_goods']['data'] = $goodsList;
            }
        }
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 获取拼多多京东模块首页布局
     * @return \think\response\Json
     */
    public function getPddJdIndex()
    {
        $params = request()->param();
        $type = $params['type'];
        if(empty($type)){
            return $this->jsonError("type不能为空");
        }
        if($type == "pdd"){
            $index = 1;
        }else{
            $index = 2;
        }
        $data = [];
        $where['status'] = 1;
        $where['application_index'] = $index;
        $where['type'] = "app";
        $where['order'] = "sort asc";
        $modulePosition = new ModulePosition();
        $positionTotal = $modulePosition->getTotal($where);
        $positionList = $modulePosition->getList($where, 0, $positionTotal);
        if($positionList){
            $module = new Module();
            $map['status'] = 1;
            $map['order'] = "sort asc";
            foreach($positionList as $key => $value) {
                $moduleName = $value['pinyin'];
                $data[$moduleName]['name'] = $value['name'];
                $data[$moduleName]['img'] = $value['img'];
                $data[$moduleName]['desc'] = $value['desc'];
                $mData = [];
                $map['position_id'] = $value['id'];
                $moduleTotal = $module->getTotal($map);
                $moduleList = $module->getList($map, 0, $moduleTotal);
                if ($moduleList) {
                    foreach ($moduleList as $k => $v) {
                        $mData[$k]['title'] = $v['title'];
                        $mData[$k]['desc'] = $v['desc'];
                        $mData[$k]['image'] = $v['image'];
                        $mData[$k]['selected_image'] = $v['selected_image'];
                        $mData[$k]['bg_color'] = empty($v['bg_color']) ? [] : json_decode($v['bg_color']);
                        $mData[$k]['page_id'] = $v['page_id'];
                        $mData[$k]['open_type'] = $v['open_type'];
                        $mData[$k]['open_url'] = $v['open_url'];
                        $mData[$k]['params'] = $v['params'];
                        if($v['page_id']){
                            $info = $module->getPageInfo($v['page_id']);
                            $mData[$k]['open_url'] = $info['open_url'];
                        }
                    }
                }
                $data[$moduleName]['modules'] = $mData;
            }
        }
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 获取优惠头条数据
     * @param $appConfig
     * @return array
     */
    protected function getHeadlinesData($appConfig)
    {
        $data = [];
        $headeLineType = $appConfig['headlines']['type'];
        if($headeLineType == 1){
            $param['page'] = 1;
            $param['pageSize'] = 100;
            $data = $this->getRobotCommissionList($param);

        }elseif ($headeLineType == 2){
            $notice = new AdminNotice();
            $noticeList = $notice->getlist(["status"=>1,"visible"=>0]);
            if($noticeList){
                foreach ($noticeList as $content){
                    $inform['id'] = $content['id'];
                    $inform['title'] = $content['title'];
                    $data[] = $inform;
                }
            }

        }elseif ($headeLineType == 3){
            $shopType = $appConfig['headlines']['goods_type'];
            $goodsIds = $appConfig['headlines']['goods_ids'];
            if($goodsIds) {
                $goodsIdArr = explode(",", $goodsIds);
                $httpClient = new HttpClient();
                $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
                $para['type'] = $shopType;
                foreach($goodsIdArr as $goodsId) {
                    $para['goods_id'] = $goodsId;
                    $result = $httpClient->post(TK_URL . "Goods/getDetail", $para)->getData('json');
                    if ($result['code'] != 200) {
                        continue;
                    }
                    $data[] = $result['result'];
                }
            }

        }
        return $data;
    }

    /**
     * 获取机器人佣金记录展示轮播列表
     * @param array $params
     * @return array
     */
    public function getRobotCommissionList($params=[])
    {
        $data = [];
        $page = empty($params['page']) ? 1 : $params['page'];
        $pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
        $robot = new Robot();
        $robotList = $robot->getList([], $page, $pageSize);
        foreach($robotList as $value){
            $username = mb_substr($value['nickname'], 0, 1).'***';
            $randNum = rand(10, 60);
            $timeArr = ["秒", "分钟"];
            $timeKey = array_rand($timeArr, 1);
            $randCommission = rand(10, 10000);
            $topLine['username'] = $username;
            $topLine['time'] = $randNum.$timeArr[$timeKey];
            $topLine['commission'] = $randCommission / 100;
            $data[] = $topLine;
        }
        return $data;
    }

    /**
     * 获取限时抢数据（好单库）
     * @return array
     */
    protected function getSeckillData()
    {
        $data = [];
        $timeArr = $this->handleHdkTime();
        $httpClient = new HttpClient();
        $para['page'] = 1;
        $para['type'] = 1;
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        foreach ($timeArr as $key => $time){
            $para['time_type'] = $key;
            $result = $httpClient->post(TK_URL."Tqg/getTqgGoods", $para)->getData('json');
            if($result['code'] != 200){
                continue;
            }
            $timeStr = date("Y-m-d", time());
            $res['time'] = strtotime($timeStr." ".$time.":00");
            $res['goodsList'] = $result['result'];
            $data[] = $res;
        }
        return $data;
    }

    /**
     * 获取好单库对应时间数组
     * @return array
     */
    protected function handleHdkTime()
    {
        $now = date("G");
        if($now < 10) {
            $timeArr = array(6=>0,7=>10,8=>12);
        }elseif ($now >= 10 && $now < 12){
            $timeArr = array(7=>10,8=>12,9=>15);
        }elseif ($now >= 12 && $now < 15){
            $timeArr = array(8=>12,9=>15,10=>20);
        }elseif ($now >= 15){
            $timeArr = array(9=>15,10=>20,11=>24);
        }
        return $timeArr;
    }

    /**
     * 获取今日值得买商品
     * @return array
     */
    protected function getTodaybuyData()
    {
        $data = [];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Special/getToadyBuy", $para)->getData('json');
        if($result['code'] == 200){
            $data = $result['result'];
        }
        return $data;
    }

    /**
     * 获取直播中的商品
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getLiveData()
    {
        $data = [];
        $liveGoods = new LiveGoods();
        $liveGoodsList = $liveGoods->getLiveList(["live_status"=>1], 1, 10);
        if($liveGoodsList){
            foreach($liveGoodsList as $value){
                $item['room_id'] = $value['room_id'];
                $item['goods_id'] = $value['goods_id'];
                $item['img'] = isset($value['detail']['img']) ? $value['detail']['img'] : "";
                $item['title'] = isset($value['detail']['title']) ? $value['detail']['title'] : "";
                $item['short_title'] = isset($value['detail']['short_title']) ? $value['detail']['short_title'] : "";
                $item['price'] = isset($value['detail']['price']) ? $value['detail']['price'] : "";
                $item['discount_price'] = isset($value['detail']['discount_price']) ? $value['detail']['discount_price'] : "";
                $item['commission_rate'] = isset($value['detail']['commission_rate']) ? $value['detail']['commission_rate'] : "";
                $item['shop_type'] = isset($value['detail']['shop_type']) ? $value['detail']['shop_type'] : "";
            }
            $data = $liveGoodsList;
        }
        return $data;
    }
}