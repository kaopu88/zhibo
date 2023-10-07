<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/2
 * Time: 17:18
 */
namespace app\api\controller\taoke;

use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Circle extends Controller
{
    /**
     * 获取发圈分类
     * @return \think\response\Json
     */
    public function getCateList()
    {
        $data = [];
        $params = request()->param();
        $pid = empty($params['pid']) ? 0 : $params['pid'];
        $where['status'] = 1;
        $where['pid'] = $pid;
        $circleCate = new \app\taoke\service\CircleCate();
        $cateNum = $circleCate->getTotal($where);
        $data = $circleCate->getList($where, 0, $cateNum);
        if($data){
            foreach ($data as $key => $value){
                $map["status"] = 1;
                $map["pid"] = $value['id'];
                $secCateNum = $circleCate->getTotal($map);
                $data[$key]['sec_cate'] = $circleCate->getList($map, 0, $secCateNum);
            }
        }
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 获取发圈列表
     * @return \think\response\Json
     */
    public function getCircleList()
    {
        $list = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $cid = $params['cid'];
        if(empty($cid)){
            return $this->jsonError("分类id不能为空");
        }
        $where['status'] = 1;
        $where['cid'] = $cid;
        $circle = new \app\taoke\service\Circle();
        $offset = ($page-1)*$pageSize;
        $list = $circle->getList($where, $offset, $pageSize);
        if($list){
            foreach ($list as $key => $value){
                if($value['ctype'] == 2){
                    $para['goods_id'] = $value['goods_info'][0]['goods_id'];
                    $shopType = $value['goods_info'][0]['shop_type'];
                    $para['type'] = $shopType;
                    $httpClient = new HttpClient();
                    $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
                    $result = $httpClient->post(TK_URL."Goods/getDetail", $para)->getData('json');
                    if($result['code'] == 200 && $result['result']){
                        $detail = $result['result'];
                        $list[$key]['goods_info'][0]['price'] = $detail['price'];
                        $list[$key]['goods_info'][0]['discount_price'] = $detail['discount_price'];
                        $list[$key]['goods_info'][0]['coupon_price'] = $detail['coupon_price'];
                        $list[$key]['goods_info'][0]['commission_rate'] = $detail['commission_rate'];
                        if($detail['gallery_imgs']) {
                            $list[$key]['goods_info'][0]['gallery_imgs'] = $detail['gallery_imgs'];
                        }
                        $userId = empty(USERID) ? 0 : USERID;
                        if($userId) {
                            $common = new \app\taoke\service\Common();
                            $commission = sprintf("%.2f", $detail['discount_price'] * $detail['commission_rate'] / 100);
                            $list[$key]['goods_info'][0]['commission'] = $common->getPurchaseCommission($commission, $shopType, $userId);
                        }else{
                            $list[$key]['goods_info'][0]['commission'] = 0;
                        }
                    }
                }
//                if($value['goods_info']){
//                    foreach ($value['goods_info'] as $ke => $item){
//                        $galleryImgs = [];
//                        if($item['gallery_imgs']){
//                            $imgArr = explode(",", $item['gallery_imgs']);
//                            foreach ($imgArr as $image){
//                                $galleryImgs[] = $image."_150x150.jpg";
//                            }
//                        }
//                        $value['goods_info'][$ke]['gallery_imgs'] = implode(",", $galleryImgs);
//                    }
//                    $list[$key]['goods_info'] = $value['goods_info'];
//                }
                $list[$key]['author_avatar'] = empty($value['author_avatar']) ? config('upload.image_defaults')["avatar"] : $value['author_avatar'];
                $list[$key]['showwriting'] = htmlspecialchars_decode($value['showwriting']);
                $list[$key]['comment'] = htmlspecialchars_decode($value['comment']);
                $list[$key]['add_time'] = exchangeTime($value['add_time']);
            }
        }
        return $this->jsonSuccess($list, "获取成功");
    }
}