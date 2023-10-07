<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/5
 * Time: 22:46
 */
namespace app\api\controller;

use app\taokeshop\service\AnchorGoods;
use app\common\controller\UserController;
use app\taoke\service\Collect as co;

class Collect extends UserController
{
    /**
     * 获取收藏记录列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCollectList()
    {
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $collect = new co();
        $offset = ($page-1)*$pageSize;
        $cateList = $collect->getList($params, $offset, $pageSize);
        return $this->success($cateList, '获取成功');
    }

    /**
     * 添加收藏
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCollect()
    {
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError('商品id不能为空');
        }
        $anchorGoods = new AnchorGoods();
        $detail = $anchorGoods->getGoodsInfo(["id" => $goodsId]);
        if($detail) {
            $collect = new co();
            $info = $collect->getInfo(["user_id" => USERID, "goods_id" => $goodsId]);
            if(empty($info)) {
                $data['user_id'] = USERID;
                $data['goods_id'] = $goodsId;
                $data['img'] = $detail['img'];
                $data['title'] = $detail['title'];
                $data['shop_type'] = $detail['shop_type'];
                $data['price'] = $detail['price'];
                $data['discount_price'] = $detail['discount_price'];
                $data['coupon_price'] = $detail['coupon_price'];
                $data['volume'] = $detail['volume'];
                $data['commission_rate'] = $detail['commission_rate'];
                $status = $collect->addCollect($data);

                if ($status) {
                    return $this->jsonSuccess("", '收藏成功');
                } else {
                    return $this->jsonError("收藏失败");
                }
            }else{
                return $this->jsonError("商品已收藏");
            }
        }else{
            return $this->jsonError("商品不存在，请刷新后再试");
        }
    }

    /**
     * 取消收藏
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function cancleCollect()
    {
        $params = request()->param();
        $ids = $params['ids'];
        $ids = trim($ids, ",");
        if(strpos($ids, ",") === false){
            $where['goods_id'] = $ids;
        }else{
            $where["goods_id"] = explode(",", $ids);
        }
        $collect = new co();
        $status = $collect->cancleCollect($where);
        if($status){
            return $this->jsonSuccess("", '删除成功');
        }else{
            return $this->jsonError("删除失败");
        }
    }
}