<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/21
 * Time: 14:20
 */
namespace app\api\controller\taoke;

use app\common\controller\UserController;
use app\taoke\service\ViewLog;
use bxkj_common\HttpClient;

class User extends UserController
{
    /**
     * 浏览记录列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getViewList()
    {
        $list = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $where['usre_id'] = USERID;
        $where['type'] = 0;
        $view = new ViewLog();
        $offset = ($page - 1) * $pageSize;
        $list = $view->getList($where, $offset, $pageSize);
        return $this->jsonSuccess($list, "获取成功");
    }

    /**
     * 删除浏览记录
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleteViewLog()
    {
        $userId = USERID;
        $params = request()->param();
        $type = !empty($params["type"]) ? $params["type"] : 0;//0:按id进行清除；1：全部清除
        $view = new ViewLog();
        if ($type == 1) {
            $status = $view->deleteLog(["user_id" => $userId]);
        } else {
            $ids = $params["ids"];
            if (empty($ids)) {
                return $this->jsonError("id不能为空");
            }
            $ids = trim($ids, ",");
            if (strpos($ids, ",") === false) {
                $idArr[] = $ids;
            } else {
                $idArr = explode(",", $ids);
            }
            $where["id"] = $idArr;
            $status = $view->deleteLog($where);
        }
        if ($status === false) {
            return $this->jsonError("清除失败");
        }
        return $this->jsonSuccess("", "清除成功");
    }

    /**
     * 用户添加收藏商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCollect()
    {
        $params = request()->param();
        $data['user_id'] = USERID;
        $goodsId = $params['goods_id'];
        if (empty($goodsId)) {
            return $this->jsonError("goods_id不能为空");
        }
        $shopType = $params['shop_type'];
        if (empty($shopType)) {
            return $this->jsonError("shop_type不能为空");
        }
        $goods = new \app\taokegoods\service\Goods();
        $detail = $goods->getGoodsInfo(["goods_id" => $goodsId, "shop_type" => $shopType]);
        if(empty($detail)) {
            $para['goods_id'] = $goodsId;
            $para['type'] = $shopType;
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $httpClient = new HttpClient();
            $result = $httpClient->post(TK_URL."Goods/getDetail", $para)->getData('json');
            if($result['code'] != 200){
                return $this->jsonError($result['msg']);
            }
            $detail = $result['result'];
        }
        $data['goods_id'] = $detail['goods_id'];
        $data['shop_type'] = $detail['shop_type'];
        $data['title'] = $detail['title'];
        $data['img'] = $detail['img'];
        $data['price'] = $detail['price'];
        $data['discount_price'] = $detail['discount_price'];
        $data['coupon_price'] = $detail['coupon_price'];
        $data['commission_rate'] = $detail['commission_rate'];
        $data['volume'] = $detail['volume'];
        $data['shop_name'] = $detail['shop_name'];
        $collect = new \app\taoke\service\Collect();
        $id = $collect->addCollect($data);
        if ($id === false) {
            return $this->jsonError("添加失败");
        }
        return $this->jsonSuccess("", "添加成功");
    }

    /**
     * 获取收藏列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCollectList()
    {
        $list = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $where['usre_id'] = USERID;
        $where['goods_type'] = 0;
        $collect = new \app\taoke\service\Collect();
        $offset = ($page - 1) * $pageSize;
        $list = $collect->getList($where, $offset, $pageSize);
        return $this->jsonSuccess($list, "获取成功");
    }

    /**
     * 取消收藏
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function cancleCollect()
    {
        $userId = USERID;
        $params = request()->param();
        $type = !empty($params["type"]) ? $params["type"] : 0;//0:按id进行取消；1：全部清除
        $collect = new \app\taoke\service\Collect();
        if ($type == 1) {
            $status = $collect->cancleCollect(["user_id" => $userId]);
        } else {
            $ids = $params["ids"];
            if (empty($ids)) {
                return $this->jsonError("id不能为空");
            }
            $ids = trim($ids, ",");
            if (strpos($ids, ",") === false) {
                $idArr[] = $ids;
            } else {
                $idArr = explode(",", $ids);
            }
            $where["id"] = $idArr;
            $status = $collect->cancleCollect($where);
        }
        if ($status === false) {
            return $this->jsonError("取消失败");
        }
        return $this->jsonSuccess("", "取消成功");
    }

}