<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/26
 * Time: 9:47
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;

class TalentArticle extends Controller
{
    /**
     * 获取达人说首页数据
     * @return \think\response\Json
     */
    public function index()
    {
        $params = request()->param();
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."TalentInfo/getIndex", $para)->getData('json');
        if($result['code'] == 200){
            return $this->jsonSuccess($result['result'], "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 获取达人说分类列表
     * @return \think\response\Json
     */
    public function getList()
    {
        $params = request()->param();
        $talentcat = isset($params['cid']) ? $params['cid'] : 0;
        $para['talentcat'] = $talentcat;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."TalentInfo/getList", $para)->getData('json');
        if($result['code'] == 200){
            return $this->jsonSuccess($result['result'], "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 达人说详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetail()
    {
        $data = [];
        $goodsList = [];
        $params = request()->param();
        $id = $params['article_id'];
        if(empty($id)){
            return $this->jsonError("文章id不能为空");
        }
        $para['id'] = $id;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."TalentInfo/getDetail", $para)->getData('json');
        if($result['code'] == 200){
            if($result['result']) {
                $data = $result['result'];
                $live = new \app\taoke\service\Live();
                $userId = empty(USERID) ? 0 : USERID;
                $config = new SysConfig();
                $appConfig = $config->getConfig("app_config");
                $appConfig = json_decode($appConfig['value'], true);

                if ($data['goods_list']) {
                    foreach ($data['goods_list'] as $key => $value) {
                        $good['goods_id'] = $value['goods_id'];
                        $good['title'] = $value['title'];
                        $good['short_title'] = $value['short_title'];
                        $good['img'] = $value['img'];
                        $good['desc'] = "";
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
                    $data['goods_list'] = $goodsList;
                }
            }
            return $this->jsonSuccess($data, "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }
}