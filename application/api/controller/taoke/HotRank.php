<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/25
 * Time: 20:10
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\taoke\service\Common;
use app\common\controller\Controller;
use bxkj_common\HttpClient;
use think\Db;

class HotRank extends Controller
{
    /**
     * 好单库榜单分类
     * @return \think\response\Json
     */
    public function getCate()
    {
        $cateList = [];
        $params = request()->param();
        $type = $params['type'];
        if($type == "hdk") {
            $list = ['全部', '女装', '男装', '内衣', '美妆', '配饰', '鞋品', '箱包', '儿童', '母婴', '居家', '美食', '数码', '家电', '其他', '车品', '文体', '宠物'];
            foreach ($list as $key => $value){
                $info = Db::name("hdk_cate")->where(["cid"=>$key])->find();
                $cateList[$key]['id'] = $key;
                $cateList[$key]['name'] = $value;
                $cateList[$key]['image'] = $info['img'];
            }
        }elseif($type == "dtk"){
            $cateList = ['全部', '女装', '母婴', '美妆', '居家', '鞋品', '美食', '文娱', '数码', '男装', '内衣', '箱包', '配饰', '户外', '家装'];
        }elseif($type == "taobao"){
            $cateList = ["28026" => '综合', "28027" => '大快消', "28028" => '电器美家', "28029" => '大服饰'];
        }
        return $this->jsonSuccess($cateList, '获取成功');
    }

    /**
     * 好单库榜单 1：实时销量榜（近2小时销量），2：今日爆单榜，3：昨日爆单榜，4：出单指数版
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHdkRank()
    {
        $params = request()->param();
        $para['page'] = isset($params['page']) ? $params['page'] : 1;
        $para['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $para['cid'] = isset($params['cid']) ? $params['cid'] : 0;
        $para['sale_type'] = isset($params['sale_type']) ? $params['sale_type'] : 1;
        $para['type'] = 1;
        $result = $this->getGoodsList($para);
        if(empty($result)){
            return $this->jsonError("获取失败");
        }
        return $this->jsonSuccess($result, "获取成功");
    }

    /**
     * 大淘客榜单 1.实时榜 2.全天榜 3.热推榜 4.复购榜 5.热词飙升榜 6.热词排行榜 7.综合热搜榜
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDtkRank()
    {
        $params = request()->param();
        $para['page'] = isset($params['page']) ? $params['page'] : 1;
        $para['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $para['cid'] = isset($params['cid']) ? $params['cid'] : 0;
        $para['rank_type'] = isset($params['rank_type']) ? $params['rank_type'] : 1;
        $para['type'] = 2;
        $result = $this->getGoodsList($para);
        if(empty($result)){
            return $this->jsonError("获取失败");
        }
        return $this->jsonSuccess($result, "获取成功");
    }

    /**
     * 获取淘联盟 实时热销榜 物料专题
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTaobaoRank()
    {
        $params = request()->param();
        $para['page'] = isset($params['page']) ? $params['page'] : 1;
        $para['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $materialId = $params['material_id'];
        if(empty($materialId)){
            return $this->jsonError("material_id不能为空");
        }
        $para['material_id'] = $materialId;
        $para['type'] = 3;
        $result = $this->getGoodsList($para);
        if(empty($result)){
            return $this->jsonError("获取失败");
        }
        return $this->jsonSuccess($result, "获取成功");
    }

    /**
     * 获取热榜商品列表
     * @param array $para
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getGoodsList($para=[])
    {
        $goodsList = [];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Hot/getHotList", $para)->getData('json');
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
                    $good['desc'] = isset($value['desc']) ? $value['desc'] : "";
                    $good['price'] = $value['price'];
                    $good['discount_price'] = $value['discount_price'];
                    $good['commission_rate'] = $value['commission_rate'];
                    $good['coupon_price'] = $value['coupon_price'];
                    $good['volume'] = $value['volume'];
                    $good['shop_type'] = $value['shop_type'];
                    $good['shop_name'] = empty($value['shop_name']) ? "" : $value['shop_name'];
                    $good['video_url'] = (isset($value['video_id']) && !empty($value['video_id'])) ? "http://cloud.video.taobao.com/play/u/1/p/1/e/6/t/1/".$value['video_id'].".mp4" : "";
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
        return $goodsList;
    }
}