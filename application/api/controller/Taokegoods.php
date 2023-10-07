<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/29
 * Time: 16:08
 */
namespace app\api\controller;

use app\taokeshop\service\AnchorGoods;
use app\taokeshop\service\AnchorShop;
use app\taokeshop\service\LiveGoods;
use app\common\controller\UserController;
use app\api\service\Video as VideoService;
use bxkj_common\HttpClient;
use bxkj_common\Prophet;
use think\Db;

class Taokegoods extends UserController
{
    /**
     * 获取商品分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCateList()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $anchorGoods = new AnchorGoods();
        $params = request()->param();
        $where['user_id'] = USERID;
        $page = empty($params['page']) ? 1 : $params['page'];
        $pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
        $where['status'] = 1;
        $cateList = $anchorGoods->getCateList($where, $page, $pageSize, "sort desc");
        return $this->success($cateList, '获取成功');
    }

    /**
     * 获取商品分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopCateList()
    {
        $anchorGoods = new AnchorGoods();
        $params = request()->param();
        if (empty($params['shop_id'])) {
            return $this->jsonError('店铺id不能为空');
        }
        $page = empty($params['page']) ? 1 : $params['page'];
        $pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
        $where['status'] = 1;
        $where['shop_id'] = $params['shop_id'];
        $cateList = $anchorGoods->getCateList($where, $page, $pageSize, "sort desc");
        return $this->jsonSuccess($cateList, '获取成功');
    }

    /**
     * 获取商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsList()
    {
        $data = [];
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $page = empty($params['page']) ? 1 : $params['page'];
        $pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
        $anchorGoods = new AnchorGoods();
        $where = [];
        $where['user_id'] = USERID;
        if(isset($params['cate_id']) && $params['cate_id'] != ""){
            $where['ag.cate_id'] =  $params['cate_id'];
        }
        if (isset($params['keyword']) && $params['keyword'] != "") {
            $where['keyword'] = $params['keyword'];
        }
        switch ($params['sort']) {
            case "new":
                $order = 'ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
                break;
            case "hot":
                $order = 'ag.is_top DESC,ag.sort DESC,ag.create_time DESC';
                break;
            case "price_asc":
                $order = 'ag.price ASC,ag.sort DESC,ag.create_time DESC';
                break;
            case "price_desc":
                $order = 'ag.price DESC,ag.sort DESC,ag.create_time DESC';
                break;
            default:
                $order = 'ag.is_top DESC,ag.top_time DESC,ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
                break;
        }
        $data = $anchorGoods->getGoodsLists($page, $pageSize, $where, $order, USERID, 0);
        return $this->jsonSuccess($data, '获取成功');
    }

    /**
     * 获取商店列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopGoodsList()
    {
        $data = [];
        $params = request()->param();
        if (empty($params['shop_id'])) {
            return $this->jsonError('店铺id不能为空');
        }

        $anchorShop = new AnchorShop();
        $shopInfo = $anchorShop->getShopInfo(["id" => $params['shop_id']]);
        if (empty($shopInfo)) {
            return $this->jsonError('此店铺不存在');
        }
        $page = empty($params['page']) ? 1 : $params['page'];
        $pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
        $anchorGoods = new AnchorGoods();

        $where['ag.user_id'] = $shopInfo['user_id'];
        if(isset($params['cate_id']) && $params['cate_id'] != ""){
            $where['ag.cate_id'] =  $params['cate_id'];
        }
        if (isset($params['keyword']) && $params['keyword'] != "") {
            $where['keyword'] = $params['keyword'];
        }
        switch ($params['sort']) {
            case "new":
                $order = 'ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
                break;
            case "hot":
                $order = 'ag.is_top DESC,ag.sort DESC,ag.create_time DESC';
                break;
            case "price_asc":
                $order = 'ag.price ASC,ag.sort DESC,ag.create_time DESC';
                break;
            case "price_desc":
                $order = 'ag.price DESC,ag.sort DESC,ag.create_time DESC';
                break;
            default:
                $order = 'ag.is_top DESC,ag.top_time DESC,ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
                break;
        }
        $data = $anchorGoods->getGoodsLists($page, $pageSize, $where, $order, USERID, 0);
        return $this->jsonSuccess($data, '获取成功');
    }

    /**
     * 获取商品详情信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsDetail()
    {
        $params = request()->param();
        $goodsId = $params['id'];
        if (empty($goodsId)) {
            return $this->jsonError('商品id不能为空');
        }
        $type = $params['type'];
        if (isset($params['anchor_goods']) && $params['anchor_goods'] == 0) {
            $isAnchor = 0;
        } else {
            $isAnchor = 1;
        }
        if ($type == 0) {//第三方商品
            $anchorGoods = new AnchorGoods();
            if ($isAnchor == 0) {//未添加到商品橱窗
                $detail = $anchorGoods->getGoodsInfo(["id" => $goodsId]);
            } else {//已添加到商品橱窗
                $detail = $anchorGoods->getAnchorGoods(["id" => $goodsId]);
            }
            if (mb_strlen($detail['short_title']) > 10) {
                $detail['short_title'] = "";
            }
        } elseif ($type == 1) {//自营商品

        }
        if ($this->user && $detail) {
            $data['type'] = 1;
            $data['user_id'] = USERID;
            $data['goods_id'] = $goodsId;
            $data['img'] = $detail['img'];
            $data['title'] = $detail['title'];
            $data['shop_type'] = $detail['shop_type'];
            $data['shop_name'] = $detail['shop_name'];
            $data['price'] = $detail['price'];
            $data['discount_price'] = $detail['discount_price'];
            $data['coupon_price'] = $detail['coupon_price'];
            $data['volume'] = $detail['volume'];
            $data['commission_rate'] = $detail['commission_rate'];
            $viewLog = new \app\taoke\service\ViewLog();
            $viewLog->addViewLog($data);
        }
        if ($detail) {
            return $this->jsonSuccess($detail, '获取成功');
        } else {
            return $this->jsonError('获取失败');
        }
    }


    /**
     * 解析商品链接获取商品详细信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function analysisContent()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] == 0) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $ser = new \app\admin\service\SysConfig();
        $config = $ser->getConfig("taoke");
        $config = json_decode($config['value'], true);

        $imgsArr = [];
        $para['content'] = $params['content'];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL . "Tool/goods/analysisUrl", $para)->getData('json');
        if ($result['code'] == 200) {
            if (empty($result['result'])) {
                return $this->jsonError("未识别到商品，请手动添加");
            }
            $info = $result['result'];
            $anchorGoods = new AnchorGoods();
            $goodsInfo = $anchorGoods->getGoodsInfo(["goods_id" => $info['goods_id']]);
            if (empty($goodsInfo)) {
                if (!empty($info['gallery_imgs'])) {
                    $imgsArr = explode(",", $info['gallery_imgs']);
                    $imgsArr = array_slice($imgsArr, 0, 5);
                    $info['gallery_imgs'] = implode(",", $imgsArr);
                }
                $info['add_type'] = 2;
                $info['add_user_id'] = USERID;
                $info['goods_type'] = 0;
                $goodsId = $anchorGoods->addGoods($info);
                if ($goodsId === false) {
                    return $this->jsonError('商品入库失败');
                }
            } else {
                $goodsId = $goodsInfo['id'];
                if (empty($goodsInfo['gallery_imgs'])) {
                    $galleryImgs = $info['gallery_imgs'];
                } else {
                    $galleryImgs = $goodsInfo['gallery_imgs'];
                }
                $imgsArr = explode(",", $galleryImgs);
                $imgsArr = array_slice($imgsArr, 0, 5);
                $info['gallery_imgs'] = implode(",", $imgsArr);

                if ($goodsInfo['price'] != $info['price']) {
                    $upData['price'] = $info['price'];
                }
                if ($goodsInfo['discount_price'] != $info['discount_price']) {
                    $upData['discount_price'] = $info['discount_price'];
                }
                if ($goodsInfo['coupon_price'] != $info['coupon_price']) {
                    $upData['coupon_price'] = $info['coupon_price'];
                }
                if (count($goodsInfo['gallery_imgs']) < count($info['gallery_imgs'])) {
                    $upData['gallery_imgs'] = $info['gallery_imgs'];
                }
                $upData['title'] = $info['title'];
                $upData['volume'] = $info['volume'];
                $anchorGoods->updateGoodsInfo(["goods_id" => $goodsId], $upData);//商品信息有变化则更新
            }
            if (mb_strlen($info['short_title']) > 10) {
                $info['short_title'] = "";
            }
            $info['id'] = $goodsId;
            $info['is_add'] = 0;
            $info['volume'] = volume_format($info['volume']);
            $anchorGoods = new AnchorGoods();
            $goodsInfo = $anchorGoods->getGoodsInfo(["goods_id" => $info['goods_id']]);
            if ($goodsInfo) {
                $exiset = $anchorGoods->getAnchorGoodsInfo(["goods_id" => $goodsInfo['id'], "user_id" => USERID]);
                if ($exiset) {
                    $info['is_add'] = 1;
                }
            }
            $info['taoke_swicth'] = isset($config['taoke_swicth']) ? $config['taoke_swicth'] : 0;
            if ($info['taoke_swicth'] == 0) {
                $info['commission'] = 0;
            }
            return $this->jsonSuccess($info, '获取成功');
        } else {
            return $this->jsonError("未识别到商品，请手动添加");
        }
    }

    /**
     * 一键解析商品添加到直播间
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function analysisGoodsToLive()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] == 0) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $para['content'] = $params['content'];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL . "Tool/goods/analysisUrl", $para)->getData('json');
        if ($result['code'] == 200) {
            $info = $result['result'];
            if (empty($info)) {
                return $this->jsonError("未识别到商品，请手动添加");
            }
            $anchorGoods = new AnchorGoods();
            $goodsInfo = $anchorGoods->getGoodsInfo(["goods_id" => $info['goods_id']]);
            if ($goodsInfo) {
                $goodsId = $goodsInfo['id'];

                if ($goodsInfo['price'] != $info['price']) {
                    $upData['price'] = $info['price'];
                }
                if ($goodsInfo['discount_price'] != $info['discount_price']) {
                    $upData['discount_price'] = $info['discount_price'];
                }
                if ($goodsInfo['coupon_price'] != $info['coupon_price']) {
                    $upData['coupon_price'] = $info['coupon_price'];
                }
                if (count($goodsInfo['gallery_imgs']) < count($info['gallery_imgs'])) {
                    $upData['gallery_imgs'] = $info['gallery_imgs'];
                }
                $upData['title'] = $info['title'];
                $upData['volume'] = $info['volume'];
                $anchorGoods->updateGoodsInfo(["goods_id" => $goodsId], $upData);//商品信息有变化则更新

                $anchorGoodsInfo = $anchorGoods->getAnchorGoodsInfo(["goods_id" => $goodsId, "user_id" => USERID]);
                if (empty($anchorGoodsInfo)) {
                    $anData['user_id'] = USERID;
                    $anData['goods_id'] = $goodsId;
                    $anData['shop_id'] = $userInfo["shop_id"];
                    $anData['shop_type'] = $goodsInfo['shop_type'];
                    $anData['cate_id'] = "";
                    $anData['create_time'] = time();
                    $anData['price'] = $info['discount_price'];
                    $anchorGoodId = $anchorGoods->addAnchorGoods($anData);
                    if ($anchorGoodId === false) {
                        return $this->jsonError('添加商品到橱窗失败');
                    }
                }else{
                    return $this->jsonError('该商品已经添加过橱窗');
                }
            } else {
                $info['add_type'] = 2;
                $info['add_user_id'] = USERID;
                $info['goods_type'] = 0;
                $goodsId = $anchorGoods->addGoods($info);
                if ($goodsId === false) {
                    return $this->jsonError('添加失败');
                }
                $anData['user_id'] = USERID;
                $anData['goods_id'] = $goodsId;
                $anData['shop_id'] = $userInfo["shop_id"];
                $anData['shop_type'] = $info['shop_type'];
                $anData['cate_id'] = "";
                $anData['create_time'] = time();
                $anData['price'] = $info['discount_price'];
                $anchorGoodId = $anchorGoods->addAnchorGoods($anData);
                if ($anchorGoodId === false) {
                    return $this->jsonError('添加商品到橱窗失败');
                }
            }
            $liveInfo = Db::name("live_goods")->where(["goods_id" => $goodsId, "user_id" => USERID])->where("live_status != -1")->find();
            if ($liveInfo) {
                return $this->jsonError("此商品已添加为直播商品");
            }
            return $this->jsonSuccess(["id" => $anchorGoodId, "goods_id" => $goodsId], '添加成功');
        } else {
            return $this->jsonError("未识别到商品，请手动添加");
        }
    }

    /**
     * 一键添加商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addGoods()
    {
        $userId = USERID;
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if (empty($goodsId)) {
            return $this->jsonError('商品id不能为空');
        }
        $anchorGoods = new AnchorGoods();
        $goodsInfo = $anchorGoods->getGoodsInfo(["goods_id" => $goodsId]);
        if (!$goodsInfo) {
            return $this->jsonError("此商品不存在");
        }
        $count = $anchorGoods->getTotal(["goods_id" => $goodsInfo["id"], "user_id" => $userId]);
        if ($count > 0) {
            return $this->jsonError("此商品已添加");
        }
        $anData['user_id'] = $userId;
        $anData['goods_id'] = $goodsInfo["id"];
        $title = mb_substr($params['short_title'], 0, 10);
        $anData['goods_title'] = $title;
        $anData['shop_id'] = $userInfo["shop_id"];
        $anData['shop_type'] = $params['shop_type'];
        $anData['cate_id'] = isset($params['cate_id']) ? $params['cate_id'] : "";
        $anData['price'] = isset($goodsInfo['discount_price']) ? $goodsInfo['discount_price'] : "";
        $anData['create_time'] = time();
        $status = $anchorGoods->addAnchorGoods($anData);
        if ($status !== false) {
            return $this->jsonSuccess("", '添加成功');
        } else {
            return $this->jsonError("添加失败，请稍后再试");
        }
    }

    /**+
     * 手动添加商品
     * @return \think\response\Json
     */
    public function addSelfGoods()
    {
        $userId = USERID;
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $itemUrl = $params['item_url'];
        if (empty($itemUrl)) {
            return $this->jsonError('商品链接不能为空');
        }
        $data['title'] = $params['title'];
        if (empty($params['title'])) {
            return $this->jsonError('商品标题不能为空');
        }
        $data['short_title'] = $params['short_title'];
        if (empty($params['short_title'])) {
            return $this->jsonError('商品短标题不能为空');
        }
        $data['img'] = $params['img'];
        if (empty($params['img'])) {
            return $this->jsonError('商品主图不能为空');
        }
        $data['price'] = $params['price'];
        if (empty($params['price'])) {
            return $this->jsonError('商品原价不能为空');
        }
        $data['discount_price'] = $params['discount_price'];
        if (empty($params['discount_price'])) {
            return $this->jsonError('商品现价不能为空');
        }
        $data['volume'] = $params['volume'];
        if (empty($params['volume'])) {
            return $this->jsonError('销量不能为空');
        }
        $data['shop_type'] = $params['shop_type'];
        if (empty($params['shop_type'])) {
            return $this->jsonError('商品平台类型不能为空');
        }
        $data['gallery_imgs'] = isset($params['gallery_imgs']) ? $params['gallery_imgs'] : "";
        if (!empty($info['gallery_imgs'])) {
            $imgsArr = explode(",", $info['gallery_imgs']);
            $imgsArr = array_slice($imgsArr, 0, 5);
            $data['gallery_imgs'] = implode(",", $imgsArr);
        }
        $data['item_url'] = $params['item_url'];
        $data['add_type'] = 2;
        $data['add_user_id'] = $userId;
        $data['goods_type'] = 1;//(0：一键添加；1：手动添加)
        $anchorGoods = new AnchorGoods();
        $goodsId = $anchorGoods->addGoods($data);
        if ($goodsId !== false) {
            $anData['user_id'] = $userId;
            $anData['goods_id'] = $goodsId;
            $anData['goods_title'] = $params['short_title'];
            $anData['shop_type'] = $params['shop_type'];
            $anData['shop_id'] = $userInfo["shop_id"];
            $anData['cate_id'] = isset($params['cate_id']) ? $params['cate_id'] : "";
            $anData['price'] = isset($params['price']) ? $params['price'] : 0;
            $anData['create_time'] = time();
        } else {
            return $this->jsonError("添加失败，请稍后再试");
        }
        $status = $anchorGoods->addAnchorGoods($anData);
        if ($status !== false) {
            return $this->jsonSuccess($goodsId, '添加成功');
        } else {
            return $this->jsonError("添加失败，请稍后再试");
        }
    }

    /**
     * 主播编辑商品
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function editGoods()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $userId = USERID;
        $params = request()->param();
        $id = $params['id'];
        if (empty($id)) {
            return $this->jsonError('id不能为空');
        }
        $anchorGoods = new AnchorGoods();

        $goodsType = $params['goods_type'];
        if ($goodsType == 0) {
            $data['title'] = $params['title'];
            if (empty($params['title'])) {
                return $this->jsonError('商品标题不能为空');
            }
            $data['short_title'] = isset($params['short_title']) ? $params['short_title'] : "";
            $data['img'] = $params['img'];
            if (empty($params['img'])) {
                return $this->jsonError('商品主图不能为空');
            }
            $data['gallery_imgs'] = isset($params['gallery_imgs']) ? $params['gallery_imgs'] : "";
            if (empty($params['price'])) {
                return $this->jsonError('商品原价不能为空');
            }
            $data['price'] = $params['price'];
            $data['shop_type'] = $params['shop_type'];
            if (empty($params['shop_type'])) {
                return $this->jsonError('商品平台类型不能为空');
            }
            $data['item_url'] = $params['item_url'];
            $data['add_user_id'] = $userId;

            $status = $anchorGoods->updateGoodsInfo(["id" => $id], $data);
        }
        if (!empty($params['short_title'])) {
            $title = mb_substr($params['short_title'], 0, 10);
            $updata['goods_title'] = $title;
        }
        if (!empty($params['cate_id'])) {
            $updata["cate_id"] = $params['cate_id'];
        }

        $detail = $anchorGoods->getAnchorGoods(["goods_id" => $id, "user_id" => $userId]);
        if (empty($detail)) {
            $updata['user_id'] = $userId;
            $updata['goods_id'] = $id;
            $updata['shop_id'] = $userInfo["shop_id"];
            $updata['shop_type'] = $params['shop_type'];
            $updata['price'] = $params['price'];
            $updata['create_time'] = time();
            $status = $anchorGoods->addAnchorGoods($updata);
        } else {
            $updata['goods_title'] = isset($params['short_title']) ? $params['short_title'] : "";
            if (!empty($updata)) {
                $status = $anchorGoods->updateGoods(["user_id" => $userId, "goods_id" => $id], $updata);
            }
        }
        if ($status !== false) {
            return $this->jsonSuccess("", '已加入橱窗，您可在发布视频时加橱窗商品进行推广');
        } else {
            return $this->jsonError("操作失败");
        }
    }

    /**
     * 实时查询商品信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLatestGoodsInfo()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if (empty($goodsId)) {
            return $this->jsonError('商品id不能为空');
        }
        $anchorGoods = new AnchorGoods();
        $find = $anchorGoods->getAnchorGoodsInfo(['goods_id'=>$goodsId,'user_id'=>$userInfo['user_id']]);

        if($find){
         $shot_title = $find['goods_title'];
        }else{
            $shot_title = '';
        }
        $shop_type = $params['shop_type'];
       if($shop_type=='Z'){
           $detail = $anchorGoods->getMallSkuGoodsInfo(["sku_id" => $goodsId]);
           $mallGoodsInfo = [
               'goods_id'=> $detail['sku_id'],
               'title'=> $detail['sku_name'],
               'short_title'=> $shot_title,
               'shop_type'=> "Z",
               'img'=> $detail['sku_image'],
               'gallery_imgs'=> $detail['sku_images'],
               'price'=> $detail['price'],
               'discount_price'=> $detail['discount_price'],
               'volume'=> $detail['sku_id'], //销量
               'item_url'=> '',
               'seller_id'=> '',
               'shop_name'=> $detail['site_name'],

           ];
           if($mallGoodsInfo){
               return $this->jsonSuccess($mallGoodsInfo, '获取成功');
           }else{
               return $this->jsonError("商品不存在");
           }
       }else{
           $goodInfo = $anchorGoods->getGoodsInfo(["id" => $goodsId]);
           if ($goodInfo) {
               $info = "";
               if ($goodInfo['goods_type'] == 0) {
                   $shopType = empty($params['shop_type']) ? "B" : $params['shop_type'];
                   $httpClient = new HttpClient();
                   $para['goods_id'] = $goodsId;
                   $para['shop_type'] = $goodInfo['shop_type'];
                   $result = $httpClient->post(config('app.taoke_api_url') . "Tool/goods/getGoodsDetail", $para)->getData('json');
                   if ($result['code'] == 200) {
                       $info = $result['result'];
                       if (empty($info)) {
                           return $this->jsonError("查询失败");
                       }
                       $info['id'] = $goodInfo['id'];
                   } else {
                       $info = $goodInfo;
                   }
               } else {
                   $info = $goodInfo;
               }

               return $this->jsonSuccess($info, '获取成功');
           } else {
               return $this->jsonError("商品不存在");
           }


       }

    }

    /**
     * 置顶商品
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setTop()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if (empty($goodsId)) {
            return $this->jsonError('商品id不能为空');
        }
        $where['user_id'] = USERID;
        $isTop = $params['is_top'] == 1 ? 1 : 0;
        $where['goods_id'] = $goodsId;
        $anchorGoods = new AnchorGoods();
        $status = $anchorGoods->updateGoods($where, ["is_top" => $isTop, "top_time" => time()]);
        if ($status) {
            return $this->jsonSuccess("", '操作成功');
        } else {
            return $this->jsonError("操作失败");
        }
    }

    /**
     * 移除商品的分类
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeGoodCate()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $userId = USERID;
        if (empty($params['goods_id'])) {
            return $this->jsonError('请选择需要移除分类的商品');
        }
        $anchorGoods = new AnchorGoods();
        $status = $anchorGoods->updateGoods(["goods_id" => $params['goods_id'], "user_id" => $userId], ["cate_id" => 0]);
        if ($status) {
            return $this->jsonSuccess("", '移除成功');
        } else {
            return $this->jsonError("移除失败");
        }
    }

    /**
     * 移除橱窗商品
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeGoods()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $where['user_id'] = USERID;
        $goodsId = trim($params['goods_id'], ",");
        $anchorGoods = new AnchorGoods();

        $data["goods_id"] = 0;
        $data["short_title"] = "";
        $data["goods_type"] = 0;
        $data["cate_id"] = 0;
        if (strpos($goodsId, ",") === false) {
            $goodsIdArr = $goodsId;
            $anchorGoodsInfo = $anchorGoods->getAnchorGoodsInfo(["user_id" => USERID, "goods_id" => $goodsId]);
            if($anchorGoodsInfo){
                Db::name("video")->where(["goods_id" => $anchorGoodsInfo['id'], "user_id" => USERID])->update($data);
            }
        } else {
            $goodsIdArr = explode(",", $goodsId);
            foreach ($goodsIdArr as $goodsId){
                $anchorGoodsInfo = $anchorGoods->getAnchorGoodsInfo(["user_id" => USERID, "goods_id" => $goodsId]);
                if($anchorGoodsInfo){
                    Db::name("video")->where(["goods_id" => $anchorGoodsInfo['id'], "user_id" => USERID])->update($data);
                }
            }
        }
        $where["goods_id"] = $goodsIdArr;
        $status = $anchorGoods->delGoods($where);
        if ($status) {
            return $this->jsonSuccess("", '移除成功');
        } else {
            return $this->jsonError("移除失败");
        }
    }

    /**
     * 移动商品分类
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function moveToCate()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $goodsId = trim($params['goods_id'], ",");
        if (strpos($goodsId, ",") === false) {
            $where['goods_id'] = $goodsId;
        } else {
            $where["goods_id"] = explode(",", $goodsId);
        }
        $where['user_id'] = USERID;
        $upData['cate_id'] = $params['cate_id'];
        $anchorGoods = new AnchorGoods();
        $status = $anchorGoods->updateGoods($where, $upData);
        if ($status !== false) {
            return $this->jsonSuccess("", '移动成功');
        } else {
            return $this->jsonError("移动失败");
        }
    }

    /**
     * 添加商品分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCate()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $data['cate_name'] = $params['cate_name'];
        if (empty($params['cate_name'])) {
            return $this->jsonError('分类名不能为空');
        }
        $userInfo = $this->user;
        $data['shop_id'] = $userInfo['shop_id'];
        $data['user_id'] = USERID;

        $shop = new AnchorShop();
        $shopInfo = $shop->getShopInfo(["user_id" => USERID, "id" => $userInfo['shop_id']]);
        if (empty($shopInfo)) {
            return $this->jsonError('参数错误，此店铺不存在');
        }
        $anchorGoods = new AnchorGoods();
        $info = $anchorGoods->getCateInfo(["cate_name" => $data['cate_name'], "user_id" => $data['user_id']]);
        if ($info) {
            return $this->jsonError("此分类名已添加，请更换分类名称");
        } else {
            $status = $anchorGoods->addGoodsCate($data);
            if ($status) {
                return $this->jsonSuccess("", '添加成功');
            } else {
                return $this->jsonError("添加失败");
            }
        }
    }

    /**
     * 编辑分类信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updateCate()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $data['cate_name'] = $params['cate_name'];
        if (empty($params['cate_name'])) {
            return $this->jsonError('分类名不能为空');
        }
        if (empty($params['cate_id'])) {
            return $this->jsonError('分类id不能为空');
        }
        $userId = USERID;
        if (isset($params['status'])) {
            if (in_array($params['status'], ['0', '1'])) {
                $data['status'] = $params['status'];
            }
        }
        if (!empty($params['sort'])) {
            $data['sort'] = $params['sort'];
        }

        $anchorGoods = new AnchorGoods();
        $info = $anchorGoods->getCateInfo(["cate_id" => $params['cate_id'], "user_id" => $userId]);
        if ($info) {
            $status = $anchorGoods->updateCateInfo(["cate_id" => $params['cate_id'], "user_id" => $userId], $data);
            if ($status) {
                return $this->jsonSuccess("", '编辑成功');
            } else {
                return $this->jsonError("编辑失败");
            }
        } else {
            return $this->jsonError("参数错误，此分类不存在");
        }
    }

    /**
     * 移除分类
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeCate()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $userId = USERID;
        if (empty($params['cate_id'])) {
            return $this->jsonError('请选择需要移除的分类');
        }
        $anchorGoods = new AnchorGoods();
        $status = $anchorGoods->delCate(["cate_id" => $params['cate_id'], "user_id" => $userId]);
        if ($status) {
            $anchorGoods->updateGoods(["cate_id" => $params['cate_id'], "user_id" => $userId], ["cate_id" => 0]);
            return $this->jsonSuccess("", '移除成功');
        } else {
            return $this->jsonError("移除失败");
        }
    }

    /**
     * 添加商品到直播
     * @return \think\response\Json
     */
    public function addToLive()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $goodsList = [];
        $params = request()->param();
        $type = $params['type'];
        $goodsId = trim($params['goods_id'], ",");
        if (strpos($goodsId, ",") === false) {
            $goodsList[] = $goodsId;
        } else {
            $goodsList = explode(",", $goodsId);
        }
        $liveGoods = new LiveGoods();
        foreach ($goodsList as $key => $good) {
            $data[$key]['goods_id'] = $good;
            $data[$key]['goods_type'] = $type;
            $data[$key]['user_id'] = USERID;
            $data[$key]['add_time'] = time();
        }
        $status = $liveGoods->addToLive($data);
        if ($status) {
            return $this->jsonSuccess("", '添加成功');
        } else {
            return $this->jsonError("添加失败");
        }
    }

    /**
     * 获取橱窗商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllAnchorGoods()
    {
        $data = [];
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $params['user_id'] = USERID;
        $page = empty($params['page']) ? 1 : $params['page'];
        $pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
        $anchorGoods = new AnchorGoods();
        $where[] = ['user_id', '=', USERID];
        $roomId = $params['room_id'];
        if (isset($params['keyword']) && $params['keyword'] != "") {
            $where['keyword'] = $params['keyword'];
        }
        $order = 'ag.is_top DESC,ag.top_time DESC,ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
        $data = $anchorGoods->getGoodsLists($page, $pageSize, $where, $order, USERID, $roomId);
        return $this->jsonSuccess($data, '获取成功');
    }

    /**
     * 获取直播商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLiveGoodsList()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $roomId = $params['room_id'];
        $userId = USERID;
        $liveGoods = new LiveGoods();
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $where['user_id'] = $userId;
        $where['room_id'] = $roomId;

        $goodsList = $liveGoods->getLiveList($where, $page, $pageSize, "live_status DESC,top_time DESC,add_time desc,sort DESC");
        return $this->success($goodsList, '获取成功');
    }

    /**
     * 讲解商品置顶
     * @return \think\response\Json
     */
    public function setLiveGoodsTop()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $goodsId = $params['goods_id'];
        $liveGoods = new LiveGoods();
        $userId = USERID;
        $where['user_id'] = $userId;
        $where['goods_id'] = $goodsId;
        $data['top_time'] = time();
        $status = $liveGoods->updateLiveInfo($where, $data);
        if ($status) {
            return $this->jsonSuccess("", '置顶成功');
        } else {
            return $this->jsonError("置顶失败");
        }
    }

    /**
     * 获取短视频商品列表
     * 初步用分类id进行关联
     * @param $goodsType 0表示第三方 1表示自营
     * @param $cateId 分类id
     */
    public function getVideoGoods()
    {
        $params = request()->param();
        $page = !empty($params['page']) ? $params['page'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 10;
        $cateId = !empty($params['cateId']) ? $params['cateId'] : 0;
        $goodsType = !empty($params['goodsType']) ? $params['goodsType'] : 0;
        $prophet = new Prophet();
        $videoGoodsList = $prophet->getGoodsList($page, $pageSize, $goodsType, $cateId);
        $VideoService = new VideoService();
        $videoGoodsList = $VideoService->initializeFilm($videoGoodsList, VideoService::$allow_fields['common']);
        return $this->success($videoGoodsList, '获取成功');
    }

    /**
     * 获取浏览记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getViewList()
    {
        $data = [];
        $params = request()->param();
        $page = !empty($params['page']) ? $params['page'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 10;
        $userId = USERID;
        $where['user_id'] = $userId;
        $where['type'] = 1;
        $offset = ($page - 1) * $pageSize;
        $viewLog = new \app\taoke\service\ViewLog();
        $data = $viewLog->getList($where, $offset, $pageSize);
        return $this->success($data, '获取成功');
    }

    /**
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delViewLog()
    {
        $params = request()->param();
        $type = empty($params['type']) ? 0 : 1;
        if ($type == 0) {
            $ids = trim($params['ids'], ",");
            if (empty($ids)) {
                return $this->jsonError('请选择需要清除的记录');
            }
            if (strpos($ids, ",") === false) {
                $where['id'] = $ids;
            } else {
                $where["id"] = explode(",", $ids);
            }
        }
        $where['user_id'] = USERID;
        $where['type'] = 1;
        $viewLog = new \app\taoke\service\ViewLog();
        $status = $viewLog->deleteLog($where);
        if ($status) {
            return $this->jsonSuccess("", '清除成功');
        } else {
            return $this->jsonError("清除失败");
        }
    }

    /**
     * 获取商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRecommandGoodsList()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $data = [];
        $params = request()->param();
        $params['status'] = 1;
        $params['goods_type'] = 0;
        $page = !empty($params['page']) ? $params['page'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 10;
        $goods = new \app\taokegoods\service\Goods();
        $data['total'] = $goods->getTotal($params);

        $ser = new \app\admin\service\SysConfig();
        $config = $ser->getConfig("taoke");
        $config = json_decode($config['value'], true);
        $httpClient = new HttpClient();

        if ($params['type'] == "B") {
            $offset = ($page - 1) * $pageSize;
            $goodsList = $goods->getList($params, $offset, $pageSize);
        } elseif ($params['type'] == "P") {
            $para = [];
            $para['client_id'] = $config['pinduoduo_client'];
            $para['client_secret'] = $config['pinduoduo_secret'];
            $para['pid'] = $config['pinduoduo_pid'];
            $para['custom_parameters'] = $config['pinduoduo_pid'];
            $para['cat_id'] = $params['cate_id'];
            $para['page'] = $page;
            $para['page_size'] = $pageSize;
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL . "Search/pddSuperSearch", $para)->getData('json');
            header("content-type: application/json");
            if (!empty($result['result'])) {
                $goodsList = $goods->formatPddGoods($result['result']['goods_list']);
                $data['total'] = $result['result']['total_count'];
            }
        } elseif ($params['type'] == "J") {
            $para = [];
            $para['apikey'] = $config['haojingke_apikey'];
            $para['pageindex'] = $page;
            $para['pagesize'] = $pageSize;
            $para['cid1'] = $params['cate_id'];
            $para['isunion'] = 1;
            $url = "http://api-gw.haojingke.com/index.php/v1/api/jd/goodslist";
            $result = $httpClient->post($url, $para)->getData('json');
            header("content-type: application/json");
            if (!empty($result['data'])) {
                $goodsList = $goods->formatJdGoods($result['data']['data']);
                $data['total'] = $result['data']['totalCount'];
            }
        }
        if ($goodsList) {
            $anchorGoods = new AnchorGoods();
            foreach ($goodsList as $value) {
                $good['cate_id'] = isset($params['cate_id']) ? $params['cate_id'] : 0;
                if($params['type'] == "P"){
                    $good['goods_id'] = $value['pdd_goods_sign'];
                }else {
                    $good['goods_id'] = $value['goods_id'];
                }
                $good['title'] = $value['title'];
                $good['short_title'] = isset($value['short_title']) ? $value['short_title'] : "";
                $good['desc'] = isset($value['desc']) ? $value['desc'] : "";
                $good['img'] = $value['img'];
                $good['price'] = $value['price'];
                $good['discount_price'] = $value['discount_price'];
//                $good['commission'] = $value['commission'];//先隐藏
                $good['commission'] = 0;
                $good['volume'] = $value['volume'];
                $good['shop_type'] = $value['shop_type'];
                $good['shop_name'] = $value['shop_name'];
                $goodsInfo = Db::name("goods")->where(["goods_id" => $value["goods_id"], "shop_type" => $value['shop_type']])->find();
                $isAdd = 0;
                $isCollect = 0;
                if ($goodsInfo) {
                    $isAdd = $anchorGoods->checkAnchorGood(["user_id" => USERID, "goods_id" => $goodsInfo['id']]);
                    $isCollect = Db::name("good_bag")->where(["goods_db_id" => $goodsInfo['id'], "user_id" => USERID, "shop_type" => $value['shop_type']])->count();
                }
                $good['is_add'] = $isAdd;
                $good['is_collect'] = $isCollect;
                $data['list'][] = $good;
            }
        }else{
            $data['list'] = new \ArrayObject();
        }
        return $this->jsonSuccess($data, '获取成功');
    }

    /**
     * 未入库的商品一键添加到橱窗
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addToWindow()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] == 0) {
            return $this->jsonError('未开通小店权限');
        }

        $params = request()->param();
        $para['goods_id'] = $params['goods_id'];
        if($params['shop_type'] == "P"){
            $para['pdd_goods_sign'] = $params['goods_id'];
        }
        $para['shop_type'] = $params['shop_type'];
        $where['shop_type'] = $params['shop_type'];
        $where['goods_id'] = $params['goods_id'];

        $goodsInfo = Db::name("goods")->where($para)->find();

        $anchorGoods = new AnchorGoods();
        if (empty($goodsInfo)) {
            $httpClient = new HttpClient();
            $where['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL . "Tool/goods/getGoodsDetail", $where)->getData('json');
            if ($result['code'] == 200) {
                $info = $result['result'];
                if (empty($info)) {
                    return $this->jsonError("商品信息查询失败");
                }
                $info['add_type'] = 2;
                $info['add_user_id'] = USERID;
                $info['goods_type'] = 0;
                $goodsId = $anchorGoods->addGoods($info);
                if ($goodsId === false) {
                    return $this->jsonError('添加失败');
                }
                $discountPrice = $info['discount_price'];
            } else {
                return $this->jsonError("商品信息查询失败");
            }
        } else {
            $goodsId = $goodsInfo['id'];
            $discountPrice = $goodsInfo['discount_price'];
        }
        $isAdd = $anchorGoods->checkAnchorGood(["user_id" => USERID, "goods_id" => $goodsId]);
        if($isAdd > 0){
            return $this->jsonError('商品已在橱窗中');
        }
        $anData['user_id'] = USERID;
        $anData['goods_id'] = $goodsId;
        $anData['shop_id'] = $userInfo["shop_id"];
        $anData['shop_type'] = $params['shop_type'];
        $anData['price'] = $discountPrice;
        $anData['cate_id'] = "";
        $anData['create_time'] = time();

        $anchorGoodId = $anchorGoods->addAnchorGoods($anData);
        if ($anchorGoodId === false) {
            return $this->jsonError('添加商品到橱窗失败');
        }
        return $this->jsonSuccess(["id" => $anchorGoodId, "goods_id" => $goodsId], '添加成功');
    }

}