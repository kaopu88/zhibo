<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/10
 * Time: 10:42
 */
namespace app\api\controller\taoke;

use app\common\controller\Controller;
use app\taokeshop\service\AnchorGoods;
use think\Db;

class Collect extends Controller
{
    /**
     * 同步商品添加
     */
    public function syncGoodsAdd()
    {
        $data = request()->param();
        if($data) {
            $goods = new \app\taokegoods\service\Goods();
            $goods->addAsycGoods($data);
        }else{
            echo 1;
        }
        $this->delOuttimeGoods();
    }

    /**
     * 同步商品更新
     */
    public function syncGoodsUpdate()
    {
        $data = request()->param();
        if($data){
            $good = new \app\taokegoods\service\Goods();
            foreach($data as $value) {
                $goodsInfo = $good->getGoodsInfo(["goods_id" => $value['goodsId']]);
                if($goodsInfo) {
                    $where['goods_id'] = $value['goodsId'];
                    $upData['price'] = $value['originalPrice'];
                    $upData['discount_price'] = $value['actualPrice'];
                    $upData['volume'] = $value['monthSales'];
                    $upData['coupon_price'] = $value['couponPrice'];
                    $upData['commission_rate'] = $value['commissionRate'];
                    $upData['update_time'] = time();
                    $good->update($where, $upData);
                }else{
                    continue;
                }
            }
        }else{
            echo 1;
        }
    }

    /**
     * 商品删除
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function syncGoodsValid()
    {
        $data = request()->param();
        if ($data) {
            $good = new \app\taokegoods\service\Goods();
            $anchorGoods = new AnchorGoods();
            foreach ($data as $value) {
                $goodsInfo = $good->getGoodsInfo(["goods_id" => $value]);
                if ($goodsInfo) {
                    $shopGoodsNum = $anchorGoods->getTotal(["goods_id" => $goodsInfo['id']]);
                    if ($shopGoodsNum > 0) {
                        $good->update(["goods_id" => $value], ["status" => 0]);
                    } else {
                        $good->del(["goods_id" => $value]);
                    }
                }
            }
            $this->delOuttimeGoods();
        } else {
            echo 1;
        }
    }

    /**
     * 删除
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delOuttimeGoods()
    {
        $goodsList = [];
        $where['status'] = 1;
        $where['add_user_id'] = 0;
        $where['add_type'] = ["0,1"];
        $goodsList = Db::name("goods")->field("id,goods_id")->where($where)->where("coupon_end_time < " . time())->limit(0, 100)->select();
        if ($goodsList) {
            $good = new \app\taokegoods\service\Goods();
            $anchorGoods = new AnchorGoods();
            foreach ($goodsList as $value) {
                $shopGoodsNum = $anchorGoods->getTotal(["goods_id" => $value['id']]);
                if ($shopGoodsNum > 0) {
                    $good->update(["goods_id" => $value['goods_id']], ["status" => 0]);
                } else {
                    Db::name("goods")->where(["id" => $value['id']])->delete();
                }
            }
        }

    }

    /**
     * 发圈添加
     */
    public function circleAdd()
    {
        $params = request()->param();
        if($params) {
            if($params['type'] == 0){
                $data['cid'] = 7;
            }elseif ($params['type'] == 1){
                $data['cid'] = 5;
            }elseif ($params['type'] == 2){
                $data['cid'] = 6;
            }
            $data['type'] = 1;
            $data['add_id'] = $params['hdk_id'];
            $data['title'] = $params['title'];
            $data['images'] = $params['image'];
            $data['goods_info'] = $params['goods_info'];
            $data['showwriting'] = $params['show_text'];
            $data['comment'] = empty($params['comment']) ? "" : $params['comment'];
            $circle = new \app\taoke\service\Circle();
            $info = $circle->getInfo(["add_id"=>$params['hdk_id']]);
            if(empty($info)) {
                $circle->add($data);
            }
        }
    }

    /**
     * 删除发圈
     */
    public function circleDel()
    {
        $params = request()->param();
        if($params) {
            $ids = $params['ids'];
            $circle = new \app\taoke\service\Circle();
            foreach ($ids as $id){
                $circle->delete(["add_id" => $id]);
            }
        }
    }
}