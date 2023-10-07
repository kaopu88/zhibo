<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/10
 * Time: 11:54
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\taokeshop\service\AnchorGoods;
use bxkj_common\CoreSdk;
use bxkj_common\HttpClient;
use app\taokegoods\service\Goods;
use app\taoke\service\Common;
use think\Db;

class Live extends UserController
{
    protected $appConfig;

    public function __construct()
    {
        parent::__construct();
        $sysConfig = new SysConfig();
        $config = $sysConfig->getConfig("app_config");
        $this->appConfig = json_decode($config['value'], true);
        if ($this->appConfig['add_goods'] == 0) {
            return $this->jsonError("一键添加商品功能未开启");
        }
        $userInfo = $this->user;
        if ($userInfo['is_anchor'] == 0 || $userInfo['taoke_shop'] == 0) {
            return $this->jsonError("用户无此权限");
        }
    }

    /**
     * 选择商品添加到商品袋
     * @return \think\response\Json
     */
    public function addToBag()
    {
        $params = request()->param();
        $userId = USERID;
        $goodsId = $params['goods_id'];
        $shopType = $params['shop_type'];
        if (empty($goodsId)) {
            return $this->jsonError("商品id不能为空");
        }
        if (empty($shopType)) {
            return $this->jsonError("平台类型不能为空");
        }

        $goodNumLimit = $this->appConfig['goods_num'];//商品袋限制商品数量
        $where["user_id"] = $userId;
        $bagGoodNum = Db::name("good_bag")->where($where)->count();//商品袋个数
        $total = $bagGoodNum + 1;
        if ($total > $goodNumLimit && $goodNumLimit > 0) {//超出商品袋个数限制
            return $this->jsonError("商品袋最多只能添加" . $goodNumLimit . "个商品");
        }

        $goods = new Goods();
        $anchorGoods = new AnchorGoods();
        $data = [];
        $goodInfo = $goods->getGoodsInfo(["goods_id" => $goodsId, "shop_type" => $shopType]);
        $goodsDbId = $goodInfo['id'];
        if (empty($goodInfo)) {//非商品库商品 访问第三方获取详情
            $para['goods_id'] = $goodsId;
            $para['type'] = $shopType;
            $httpClient = new HttpClient();
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL . "Goods/getDetail", $para)->getData('json');
            if ($result['code'] != 200) {
                return $this->jsonError("商品信息获取失败");
            }
            $goodInfo = $result['result'];
            $goodInfo['add_type'] = 2;
            $goodInfo['add_user_id'] = $userId;
            $goodInfo['price'] = $goodInfo['discount_price'];
            $goodInfo['goods_type'] = 0;
            $goodsDbId = $anchorGoods->addGoods($goodInfo);
            if (!$goodsDbId) {
                return $this->jsonError("添加失败");
            }
        }
        $exist = Db::name("good_bag")->where(["goods_id" => $goodsId, "user_id" => $userId, "shop_type" => $shopType])->count();
        if ($exist) {
            return $this->jsonError("此商品已添加");
        }
        Db::startTrans();
        try {
            $data['goods_db_id'] = $goodsDbId;
            $data['goods_id'] = $goodsId;
            $data['title'] = $goodInfo['title'];
            $data['img'] = $goodInfo['img'];
            $data['shop_type'] = $goodInfo['shop_type'];
            $data['price'] = $goodInfo['price'];
            $data['discount_price'] = $goodInfo['discount_price'];
            $data['coupon_price'] = $goodInfo['coupon_price'];
            $data['commission_rate'] = $goodInfo['commission_rate'];
            $common = new Common();
            $commission = sprintf("%.2f", $goodInfo['discount_price'] * $goodInfo['commission_rate'] / 100);
            $good['commission'] = $common->getPurchaseCommission($commission, $shopType, $userId);
            $data['shop_name'] = isset($goodInfo['shop_name']) ? $goodInfo['shop_name'] : "";
            $data['user_id'] = $userId;
            $data['add_time'] = time();
            $id = Db::name("good_bag")->insert($data);
            if (empty($id)) {
                throw new \Exception('添加失败');
            }
            $collectData['member_id'] = $userId;
            $collectData['goods_id'] = $goodsDbId;
            $collectData['shop_type'] = $goodInfo['shop_type'];
            $collectData['create_time'] = time();
            $res = Db::name("shop_live_goods_collect")->insertGetId($collectData);
            if (empty($res)) {
                throw new \Exception('添加失败');
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->jsonError($e->getMessage());
        }

        $live = new \app\taoke\service\Live();
        $live->updateBagRedis($userId, $goodsId);
        return $this->jsonSuccess("", "添加成功");
    }

    /**
     * 获取商品袋商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBagGoodList()
    {
        $data = [];
        $params = request()->param();
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $field = "id,goods_db_id,goods_id,title,img,shop_type,price,discount_price,coupon_price,commission_rate,commission,shop_name";
        $data['total'] = Db::name("good_bag")->where(["user_id" => USERID])->count();
        $data['goods_list'] = Db::name("good_bag")->field($field)->where(["user_id" => USERID])->limit(($page - 1) * $pageSize, $pageSize)->select();
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 移除商品袋商品
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeBagGood()
    {
        $params = request()->param();
        $ids = $params['ids'];
        if (empty($ids)) {
            return $this->jsonError("请选择操作数据");
        }
        $idArray = [];
        if (strpos($ids, ",") === false) {
            $idArray[] = $ids;
        } else {
            $idArray = explode(",", $ids);
        }
        $shopType = $params['shop_type'];
        if (empty($shopType)) {
            return $this->jsonError("平台类型不能为空");
        }
        Db::startTrans();
        try {
            $live = new \app\taoke\service\Live();
            foreach ($idArray as $value) {
                $where['goods_id'] = $value;
                $where['shop_type'] = $shopType;
                $where['user_id'] = USERID;
                $goodInfo = Db::name("good_bag")->where($where)->find();
                $status = Db::name("good_bag")->where($where)->delete();
                if (empty($status)) {
                    throw new \Exception('移除失败');
                }
                $map['goods_id'] = $goodInfo['goods_db_id'];
                $map['shop_type'] = $shopType;
                $map['member_id'] = USERID;
                $status2 = Db::name("shop_live_goods_collect")->where($map)->delete();
                if (empty($status2)) {
                    throw new \Exception('移除失败');
                }
                $live->delBagRedis(USERID, $goodInfo['goods_db_id']);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->jsonError($e->getMessage());
        }
        return $this->jsonSuccess("", "移除成功");
    }

    /**
     * 商品袋商品添加到橱窗 并移除商品袋记录
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function addBagToWindow()
    {
        $params = request()->param();
        $infoList = $params['info_list'];
        $infoList = json_decode($infoList, true);
        if (empty($infoList)) {
            return $this->jsonError("请选择商品");
        }
        $userId = USERID;
        $userInfo = $this->user;

        $num = 0;
        $idArray = [];
        $anchorGood = new AnchorGoods();
        $live = new \app\taoke\service\Live();
        foreach ($infoList as $value) {
            $data['user_id'] = $userId;
            $data['shop_id'] = $userInfo['shop_id'];
            $data['goods_id'] = $value['good_db_id'];
            $data['shop_type'] = $value['shop_type'];
            $data['price'] = $value['price'];//后续补充的价格  用作排序使用
            $data['create_time'] = time();
            $count = $anchorGood->getTotal(["user_id" => $userId, "goods_id" => $value['good_db_id']]);
            if ($count > 0) {
                continue;
            }
            $status = $anchorGood->addAnchorGoods($data);
            if ($status === false) {
                continue;
            }
            $num++;
            $idArray[] = $value['id'];
            $live->delBagRedis($userId, $value['good_db_id']);
        }
        $where['user_id'] = $userId;
        $where['id'] = $idArray;
        Db::name("good_bag")->where($where)->delete();
        return $this->jsonSuccess("", "成功添加" . $num . "个商品");
    }

    /**
     * @return \think\response\Json
     * @throws \bxkj_module\exception\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLiveList()
    {
        $liveingList = [];
        $params = request()->param();
        $shopType = $params['shop_type'];
        if (empty($shopType) || $shopType == "T") {
            $where['goods.shop_type'] = ["B", "C"];
        } else {
            $where['goods.shop_type'] = $shopType;
        }
        $page = !empty($params["page"]) ? $params["page"] : 1;
        $pageSize = !empty($params["pageSize"]) ? $params["pageSize"] : 10;
        $liveingList = Db::name("live_goods")->alias("lg")->leftJoin("goods", "lg.goods_id = goods.id")
            ->field("lg.user_id,lg.room_id")
            ->where("lg.live_status > -1 AND lg.goods_type = 0")->where($where)
            ->group("lg.user_id")->order("lg.room_id desc")
            ->limit(($page - 1) * $pageSize, $pageSize)->select();
        if ($liveingList) {
            $coreSdk = new CoreSdk();
            foreach ($liveingList as $key => $value) {
                $goodsList = [];
                $liveInfo = Db::name('live')->where(['user_id' => $value['user_id']])->find();
                $liveingList[$key]['avatar'] = $liveInfo['avatar'];
                $anchorInfo = Db::name('anchor_shop')->where(['user_id' => $value['user_id']])->find();
                $liveingList[$key]['shop_name'] = empty($anchorInfo['title']) ? $liveInfo['nickname'] : $anchorInfo['title'];
                $albumCount = Db::name('user_album')->where(['user_id' => $value['user_id']])->count();
                if ($albumCount > 1) {
                    $album = Db::name('user_album')->where(['user_id' => $value['user_id']])->orderRand()->value('image');
                    $liveingList[$key]['bg_url'] = img_url($album, 'live');
                } else {
                    $coverUrl = $liveInfo['cover_url'];
                    $liveingList[$key]['bg_url'] = img_url($coverUrl, 'live');
                }
                $liveingList[$key]['audience_num'] = $coreSdk->post('zombie/getRoomAudience', ['room_id' => $value['room_id']])[$value['room_id']];//获取直播间观众人数

                $goodsList = Db::name("live_goods")->alias("lg")
                    ->leftJoin("anchor_goods", "anchor_goods.id = lg.anchor_id")
                    ->leftJoin("goods", "lg.goods_id = goods.id")
                    ->field("lg.live_status,anchor_goods.goods_title as short_title,goods.title,goods.img,goods.shop_type,goods.coupon_price")
                    ->where("lg.live_status > -1 AND lg.goods_type = 0")->where($where)->where(["lg.user_id" => $value['user_id']])
                    ->order("lg.top_time desc,lg.add_time desc")
                    ->limit(($page - 1) * $pageSize, $pageSize)->select();

                $liveingList[$key]['goods_list'] = $goodsList;
            }
        }
        return $this->jsonSuccess($liveingList, "获取成功");
    }
}