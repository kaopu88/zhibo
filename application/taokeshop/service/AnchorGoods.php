<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/29
 * Time: 15:52
 */
namespace app\taokeshop\service;

use app\common\service\Service;
use think\Db;

class AnchorGoods extends Service
{
    /**
     * 添加主播商品
     * @param $data
     * @return int|string
     */
    public function addAnchorGoods($data)
    {
        $id = Db::name("anchor_goods")->insertGetId($data);
        return $id;
    }

    /**
     * 检查主播商品
     * @param $where
     * @return float|string
     */
    public function checkAnchorGood($where)
    {
        $count = Db::name("anchor_goods")->where($where)->count();
        return $count;
    }

    /**
     * 统计数量
     * @param $where
     * @return int
     */
    public function getTotal($where)
    {
        $this->db = Db::name('anchor_goods');
        $this->setWhere($where);
        $count = $this->db->count();
        return (int)$count;
    }

    /**
     * 获取主播商品列表
     * @param $params
     * @param int $page
     * @param int $pageSize
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($params, $offset=0, $limit=15)
    {
        $result = [];
        $this->db = Db::name('anchor_goods');
        $this->setWhere($params)->setOrder($params);
        $fields = 'goods.title,goods.img,goods.price,goods.discount_price,goods.volume,goods.shop_type,goods.goods_type';
        $this->db->field('ag.id,ag.user_id,ag.shop_id,ag.cate_id,ag.goods_id,ag.goods_title as short_title,ag.shop_type,ag.create_time,ag.is_top,ag.top_time,ag.top_time,ag.sort,ag.status,ag.update_time');
        $result = $this->db->field($fields)->limit($offset, $limit)->select();
        if($result){
            $anchorCate = new AnchorGoodsCate();
            foreach ($result as $key => $value){
                if($value['cate_id'] != 0) {
                    $cateInfo = $anchorCate->getCateInfo(["user_id" => $value['user_id'], "cate_id" => $value['cate_id']]);
                    $result[$key]['cate_name'] = $cateInfo['cate_name'];
                }else{
                    $result[$key]['cate_name'] = "";
                }
            }
        }
        return $result;
    }

    /**
     * 获取主播商品列表(包括商城商品)
     * @param $params
     * @param int $page
     * @param int $pageSize
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllList($where=[], $page=1, $pageSize=10, $sort="id desc"){

        $list = Db::name("anchor_goods")->where($where)->order($sort)->limit(($page-1)*$pageSize, $pageSize)->select();
        if($list){
            $anchorGoods = new AnchorGoods();
            foreach($list as $key => $value){
                if($value['goods_type'] == 0){//第三方商品
                    $detail = $anchorGoods->getGoodsInfo(["id" => $value['goods_id']]);
                    if($detail){
                        $list[$key]['detail']['img'] = $detail['img'];
                        $list[$key]['detail']['discount_price'] = $detail['discount_price'];
                        $list[$key]['detail']['shop_type'] = $detail['shop_type'];
                        $list[$key]['detail']['title'] = $detail['title'];
                        $list[$key]['detail']['short_title'] = $detail['short_title'];
                        $list[$key]['detail']['commission_rate'] = $detail['commission_rate'];
                        $list[$key]['detail']['price'] = $detail['price'];
                        $list[$key]['detail']['coupon_price'] = $detail['coupon_price'];
                        $list[$key]['detail']['volume'] = $detail['volume'];
                    }else{
                        $list[$key]['detail'] = "";
                    }
                }elseif ($value['goods_type'] == 1){//自营商品
                    $detail = $anchorGoods->getMallSkuGoodsInfo(["id" => $value['goods_id']]);
                    if($detail){
                        $list[$key]['detail']['img'] = $detail['sku_image'];
                        $list[$key]['detail']['discount_price'] = $detail['discount_price'];
                        $list[$key]['detail']['shop_type'] = $detail['shop_type'];
                        $list[$key]['detail']['title'] = $detail['sku_name'];
                        $list[$key]['detail']['short_title'] = $detail['short_title'];
                        $list[$key]['detail']['commission_rate'] = $detail['live_commission_percent']?$detail['live_commission_percent']:0;
                        $list[$key]['detail']['price'] = $detail['price'];
                        $list[$key]['detail']['coupon_price'] = '';
                        $list[$key]['detail']['volume'] = $detail['sale_num'];
                    }else{
                        $list[$key]['detail'] = "";
                    }
                }
            }
        }
        return $list;
    }


    /**
     * 设置sql where条件
     * @param $get
     * @return $this
     */
    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        $this->db->alias('ag');
        if (isset($get['status']) && $get['status'] != "") {
            $where['ag.status'] = $get['status'];
        }
        if (isset($get['cate_id']) && $get['cate_id'] != "") {
            $where['ag.cate_id'] = $get['cate_id'];
        }
        if (isset($get['keyword']) && $get['keyword'] != "") {
            $where1[] = ['goods.title|goods.short_title|ag.goods_title','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['shop_id'])) {
            $where['ag.shop_id'] = $get['shop_id'];
        }
        if (isset($get['goods_id']) && $get['goods_id'] != "") {
            $where['ag.goods_id'] = $get['goods_id'];
        }
        if (isset($get['user_id']) && $get['user_id'] != "") {
            $where['ag.user_id'] = $get['user_id'];
        }
        $this->db->join('__GOODS__ goods', 'goods.id=ag.goods_id', 'LEFT');
        $this->db->where($where)->where($where1);
        return $this;
    }

    /**
     * 设置sort排序
     * @param $get
     * @return $this
     */
    protected function setOrder($get)
    {
        switch ($get['sort']){
            case "new":
                $order = 'ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
                break;
            case "hot":
                $order = 'ag.is_top DESC,ag.sort DESC,ag.create_time DESC';
                break;
            case "price_asc":
                $order = 'goods.price ASC,ag.sort DESC,ag.create_time DESC';
                break;
            case "price_desc":
                $order = 'goods.price DESC,ag.sort DESC,ag.create_time DESC';
                break;
            default:
                $order = 'ag.is_top DESC,ag.top_time DESC,ag.update_time DESC,ag.sort DESC,ag.create_time DESC';
                break;
        }
        $this->db->order($order);
        return $this;
    }

    /**
     * @param $where
     * @param $data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateGoods($where, $data)
    {
        $data['update_time'] = time();
        $status = Db::name('anchor_goods')->where($where)->update($data);
        return $status;
    }

    /**
     * 获取商品详情信息
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAnchorGoodsInfo($where)
    {
        $detail = Db::name('anchor_goods')->where($where)->find();
        return $detail;
    }

    /**
     * 移除主播添加到橱窗的商品（主商品库商品不改变）
     * @param $where
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delGoods($where)
    {

        $status = Db::name('anchor_goods')->where($where)->delete();
        return $status;
    }

    /**
     * 获取橱窗分类
     * @param $where
     * @param int $page
     * @param int $pageSize
     * @param $sort
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCateList($where, $page=1, $pageSize=10, $sort)
    {
        $list = [];
        if(isset($where['user_id'])){
            $map['user_id'] = $where['user_id'];
            $map2['user_id'] = $where['user_id'];
        }
        if(isset($where['shop_id'])){
            $map['shop_id'] = $where['shop_id'];
            $map2['id'] = $where['shop_id'];
        }
        $anchorShop = new AnchorShop();
        $shopInfo = $anchorShop->getShopInfo($map2);
        $userId = $shopInfo['user_id'];
        $map['status'] = 1;
        $list = Db::name('anchor_goods_cate')->where($map)->limit(($page-1)*$pageSize, $pageSize)->order($sort)->select();
        if(!empty($list)){
            foreach ($list as $key =>$value){
                $goodsNum = Db::name('anchor_goods')->where(["cate_id" =>$value['cate_id'], "user_id"=>$userId, "status" => 1])->count();
                $list[$key]['goods_num'] = $goodsNum;
            }
        }
        $num = Db::name('anchor_goods')->where(["cate_id" =>0,"user_id"=>$userId,"status" => 1])->count();
        if($num > 0 && $page == 1){
            $cate = array(
                "cate_id"=>0,
                "cate_name"=>"未分类商品",
                "shop_id"=>$where['shop_id'],
                "user_id"=>$userId,
                "sort"=>999,
                "status"=>1,
                "create_time"=>time(),
                "goods_num"=>$num,
            );
            array_unshift($list, $cate);
        }
        return $list;
    }

    /**
     * 获取橱窗分类信息
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCateInfo($where)
    {
        $cateInfo = Db::name('anchor_goods_cate')->where($where)->find();
        return $cateInfo;
    }

    /**
     * 添加橱窗分类
     * @param $data
     * @return int|string
     */
    public function addGoodsCate($data)
    {
        $data['create_time'] = time();
        $num = Db::name('anchor_goods_cate')->insertGetId($data);
        return $num;
    }

    /**
     * 编辑橱窗分类信息
     * @param $where
     * @param $data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateCateInfo($where, $data)
    {
        $status = Db::name('anchor_goods_cate')->where($where)->update($data);
        return $status;
    }

    /**
     * 删除橱窗分类
     * @param $where
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delCate($where)
    {
        $status = Db::name('anchor_goods_cate')->where($where)->delete();
        return $status;
    }

    /**
     * 主播添加商品到平台商品库
     * @param $data
     * @return int|string
     */
    public function addGoods($param)
    {
        if($param['goods_type'] == 0) {
            $info = Db::name("goods")->where(["goods_id" => $param['goods_id'], "shop_type" => $param['shop_type']])->find();
            if (!empty($info)) {
                return $info['id'];
            }
        }
        $data['goods_id'] = isset($param['goods_id']) ? $param['goods_id'] : '';
        $data['title'] = $param['title'];
        $data['short_title'] = isset($param['short_title']) ? $param['short_title'] : "";
        $data['img'] = $param['img'];
        $data['gallery_imgs'] = isset($param['gallery_imgs']) ? $param['gallery_imgs'] : "";
        $data['desc'] = isset($param['desc']) ? $param['desc'] : "";
        $data['price'] = $param['price'];
        $data['discount_price'] = isset($param['discount_price']) ? $param['discount_price'] : 0;
        $data['coupon_price'] = isset($param['coupon_price']) ? $param['coupon_price'] : 0;
        $data['commission_rate'] = isset($param['commission_rate']) ? $param['commission_rate'] : 0;
        $data['commission'] = isset($param['commission']) ? $param['commission'] : 0;
        $data['volume'] = $param['volume'];
        $data['shop_type'] = $param['shop_type'];
        $data['has_coupon'] = isset($param['has_coupon']) ? $param['has_coupon'] : 0;
        $data['coupon_start_time'] = isset($param['coupon_start_time']) ? $param['coupon_start_time'] : "";
        $data['coupon_end_time'] = isset($param['coupon_end_time']) ? $param['coupon_end_time'] : "";
        $data['coupon_total'] = isset($param['coupon_total']) ? $param['coupon_total'] : 0;
        $data['coupon_surplus'] = isset($param['coupon_surplus']) ? $param['coupon_surplus'] : 0;
        $data['shop_name'] = isset($param['shop_name']) ? $param['shop_name'] : "";
        $data['seller_id'] = isset($param['seller_id']) ? $param['seller_id'] : "";
        $data['shop_id'] = isset($param['shop_id']) ? $param['shop_id'] : "";
        $data['item_url'] = $param['item_url'];
        $data['add_type'] = $param['add_type'];
        $data['add_user_id'] = $param['add_user_id'];
        $data['goods_type'] = $param['goods_type'];//(0：一键添加；1：手动添加)
        $data['is_new'] = empty($param['is_new']) ? 0 : 1;
        $data['is_top'] = empty($param['is_top']) ? 0 : 1;
        $data['create_time'] = time();
        $id = Db::name("goods")->insertGetId($data);
        return $id;
    }

    /**
     * 获取主播添加商品的信息
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsInfo($where)
    {
        $goodsInfo = Db::name("goods")->where($where)->find();
        if($goodsInfo) {
            $anchorGoodsInfo = $this->getAnchorGoodsInfo(["goods_id" => $goodsInfo['id']]);
            if($anchorGoodsInfo) {
                if ($anchorGoodsInfo["cate_id"] != 0) {
                    $cateInfo = $this->getCateInfo(["cate_id" => $anchorGoodsInfo["cate_id"], "user_id" => $anchorGoodsInfo['user_id']]);
                    $goodsInfo['cate_name'] = $cateInfo["cate_name"];
                } else {
                    $goodsInfo['cate_name'] = "";
                }
                $goodsInfo['cate_id'] = $anchorGoodsInfo["cate_id"];
                $goodsInfo['short_title'] = $anchorGoodsInfo['goods_title'];
            }
        }
        return $goodsInfo;
    }

    public function getAnchorGoods($where)
    {
        $anchorGoodsInfo = $this->getAnchorGoodsInfo($where);
        if (empty($anchorGoodsInfo)) return false;
        if($anchorGoodsInfo['shop_type'] == "Z"){
            $goodsInfo = Db::name("shop_goods_sku")->where(['sku_id' => $anchorGoodsInfo['goods_id']])->find();
        }else{
            $goodsInfo = Db::name("goods")->where(['id' => $anchorGoodsInfo['goods_id']])->find();
        }
        if($goodsInfo) {
            $goodsInfo['short_title'] = $anchorGoodsInfo["goods_title"];
            if ($anchorGoodsInfo["cate_id"] != 0) {
                $cateInfo = $this->getCateInfo(["cate_id" => $anchorGoodsInfo["cate_id"], "user_id" => $anchorGoodsInfo['user_id']]);
                $goodsInfo['cate_name'] = $cateInfo["cate_name"];
            } else {
                $goodsInfo['cate_name'] = "";
            }
            $goodsInfo['cate_id'] = $anchorGoodsInfo["cate_id"];
            $goodsInfo['shop_id'] = $anchorGoodsInfo["shop_id"];
        }
        return $goodsInfo;
    }

    /**
     * 主播编辑自己添加的商品信息
     * @param $where
     * @param $data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateGoodsInfo($where, $data)
    {
        $data['update_time'] = time();
        $res = Db::name("goods")->where($where)->update($data);
        return $res;
    }

    /**
     * 获取主播添加商城商品的信息
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMallSkuGoodsInfo($where)
    {
         $goodsInfo = Db::name("shop_goods_sku")->where($where)->find();
        if($goodsInfo) {
            $anchorGoodsInfo = $this->getAnchorGoodsInfo(["goods_id" => $goodsInfo['id']]);
            if($anchorGoodsInfo) {
                if ($anchorGoodsInfo["cate_id"] != 0) {
                    $cateInfo = $this->getCateInfo(["cate_id" => $anchorGoodsInfo["cate_id"], "user_id" => $anchorGoodsInfo['user_id']]);
                    $goodsInfo['cate_name'] = $cateInfo["cate_name"];
                } else {
                    $goodsInfo['cate_name'] = "";
                }
                $goodsInfo['cate_id'] = $anchorGoodsInfo["cate_id"];
                $goodsInfo['short_title'] = $anchorGoodsInfo['goods_title'];
                $goodsInfo['short_type'] =  $anchorGoodsInfo['shop_type'];
            }
        }

        return $goodsInfo;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field, $roomId=0,$userId)
    {
        $where= [];
        $where = $condition;
        $this->db = Db::name('anchor_goods');
        $count    = $this->db->where($where)->count();
        if ($page_size == 0) {
            $where= [];
            $where = $condition;
            $list       = Db::name('anchor_goods')->field($field)
                ->where($where)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $where= [];
            $where = $condition;
            $start_row = $page_size * ($page_index - 1);
            $list      = Db::name('anchor_goods')->field($field)
                ->where($where)
                ->order($order)
                ->limit($start_row . "," . $page_size)
                ->select();
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int)($count / $page_size) + 1;
            }
        }
        if($list){
            $anchorGoods = new AnchorGoods();
            foreach($list as $key => $value){
                $map["user_id"] = $userId;
                $map["room_id"] = $roomId;
                $map["goods_id"] = $value['goods_id'];

                if( $value['shop_type']!= "Z"){//第三方商品
                    $detail = $anchorGoods->getGoodsInfo(["id" => $value['goods_id']]);
                    if($detail){
                        $list[$key]['img'] = $detail['img'];
                        $list[$key]['discount_price'] = $detail['discount_price'];
                        $list[$key]['shop_type'] = $detail['shop_type'];
                        $list[$key]['title'] = $detail['title'];
                        $list[$key]['short_title'] = $detail['short_title'];
                        $list[$key]['commission_rate'] = $detail['commission_rate'];
                        $list[$key]['price'] = $detail['price'];
                        $list[$key]['coupon_price'] = $detail['coupon_price'];
                        $list[$key]['volume'] = $detail['volume'];
                        $list[$key]['live_commission_percent'] = "0";
                        $list[$key]['promotion_type'] = 0;
                        $list[$key]['start_time'] = 0;
                        $list[$key]['end_time'] = 0;
                        $map["goods_type"] = 0;
                        $count1 = Db::name("live_goods")->where($map)->where("live_status != -1")->count();

                        $goodsKey = 'live_goods_pre:goods:goodsid:taoke' . $userId;
                        $goodsHas = $this->redis->sIsMember($goodsKey, $value['goods_id']);
                        if ($count1 > 0 || $goodsHas) {
                            $list[$key]['is_add'] = 1;
                        } else {
                            $list[$key]['is_add'] = 0;
                        }

                    }else{
                        $list[$key] = "";
                    }
                }else{//自营商品
                    $detail = $anchorGoods->getMallSkuGoodsInfo(["sku_id" => $value['goods_id']]);
                    //这里要判断此商户是否设置了帮助直播带货提成的比率
                   $shopConfig =  Db::name("shop_shop")->where(['site_id' => $detail['site_id']])->find();
                   if($shopConfig['live_commission_percent']>0){
                       $percent = $shopConfig['live_commission_percent'];
                   }else{
                       $percent = 0;
                   }
                    if($detail){
                        $list[$key]['img'] = $detail['sku_image'];
                        $list[$key]['discount_price'] = $detail['discount_price'];
                        $list[$key]['shop_type'] = 'Z';
                        $list[$key]['title'] = $detail['sku_name'];
                        $list[$key]['short_title'] = $value['goods_title'];
                        $list[$key]['commission_rate'] = "0";
                        $list[$key]['price'] = $detail['price'];
                        $list[$key]['coupon_price'] = '';
                        $list[$key]['volume'] = $detail['sale_num'];
                        $list[$key]['promotion_type'] = $detail['promotion_type'];
                        $list[$key]['start_time'] = $detail['start_time'];
                        $list[$key]['end_time'] = $detail['end_time'];
                        $list[$key]['live_commission_percent'] = $detail['live_commission_percent']>0?$detail['live_commission_percent']:$percent;
                        $map["goods_type"] = 1;
                        $count1 = Db::name("live_goods")->where($map)->where("live_status != -1")->count();
                        $goodsKey = 'live_goods_pre:goods:goodsid:shop' . $userId;

                        $goodsHas = $this->redis->sIsMember($goodsKey, $value['goods_id']);
                        if ($count1 > 0 || $goodsHas) {
                            $list[$key]['is_add'] = 1;
                        } else {
                            $list[$key]['is_add'] = 0;
                        }

                    }else{
                        $list[$key] = "";
                    }
                }
            }
        }


        return array(
            'total_count' => $count,
            'total' => $count,
            'page_count'  => $page_count,
            'list'        => $list,
        );
    }

    public function getGoodsLists($page, $pageSize, $condition, $order="ag.create_time desc", $userId, $roomId=0)
    {
        $where1 = [];
        if (isset($condition['keyword']) && $condition['keyword'] != "") {
            $where1[] = ['g.title|g.short_title|sgs.sku_name','like','%'.$condition['keyword'].'%'];
            unset($condition['keyword']);
        }
        $field = "ag.*,
        g.title, g.img, g.discount_price as dis_price, g.short_title, g.commission_rate, g.coupon_price, g.volume,
        sgs.sku_name, sgs.sku_image, sgs.site_id, sgs.price as s_price, sgs.sale_num, sgs.discount_price, sgs.live_commission_percent, sgs.promotion_type, sgs.start_time, sgs.end_time";
        $list = Db::name('anchor_goods')->field($field)->alias("ag")
            ->join("goods g", "ag.goods_id = g.id", "left")
            ->join("shop_goods_sku sgs", "ag.goods_id = sgs.sku_id", "left")
            ->where($condition)->where($where1)->order($order)->limit( ($page - 1) * $pageSize, $pageSize)->select();

        $count = Db::name('anchor_goods')->field($field)->alias("ag")
            ->join("goods g", "ag.goods_id = g.id", "left")
            ->join("shop_goods_sku sgs", "ag.goods_id = sgs.sku_id", "left")
            ->where($condition)->order($order)->count();

        if ($count > 0) {
            $page_count = ceil($count / $pageSize);
        } else {
            $page_count = 0;
        }

        $data = [];
        if ($list) {
            foreach ($list as $key => $value) {

                $map["user_id"] = $userId;
                $map["room_id"] = $roomId;
                $map["goods_id"] = $value['goods_id'];

                $item['id'] = $value['id'];
                $item['cate_id'] = $value['cate_id'];
                $item['create_time'] = $value['create_time'];
                $item['is_top'] = $value['is_top'];
                $item['top_time'] = $value['top_time'];
                $item['is_new'] = $value['is_new'];
                $item['sort'] = $value['sort'];
                $item['status'] = $value['status'];

                if ($value['shop_type'] != "Z") {//第三方商品
                    $item['img'] = $value['img'];
                    $item['title'] = $value['title'];
                    $item['short_title'] = $value['short_title'];
                    $item['commission_rate'] = $value['commission_rate'];
                    $item['price'] = $value['price'];
                    $item['discount_price'] = $value['dis_price'];
                    $item['coupon_price'] = $value['coupon_price'];
                    $item['volume'] = $value['volume'];
                    $item['live_commission_percent'] = "0";
                    $item['promotion_type'] = 0;
                    $item['start_time'] = 0;
                    $item['end_time'] = 0;
                    $item['shop_type'] = $value['shop_type'];
                    $item['goods_id'] = $value['goods_id'];
                    $map["goods_type"] = 0;

                    $count1 = Db::name("live_goods")->where($map)->where("live_status != -1")->count();

                    $goodsKey = 'live_goods_pre:goods:goodsid:taoke' . $userId;
                    $goodsHas = $this->redis->sIsMember($goodsKey, $value['goods_id']);
                    if ($count1 > 0 || $goodsHas) {
                        $item['is_add'] = 1;
                    } else {
                        $item['is_add'] = 0;
                    }
                } else {//自营商品
                    //这里要判断此商户是否设置了帮助直播带货提成的比率
                    $shopConfig = Db::name("shop_shop")->where(['site_id' => $value['site_id']])->find();
                    if ($shopConfig['live_commission_percent'] > 0) {
                        $percent = $shopConfig['live_commission_percent'];
                    } else {
                        $percent = 0;
                    }
                    $item['img'] = $value['sku_image'];
                    $item['discount_price'] = $value['discount_price'];
                    $item['title'] = $value['sku_name'];
                    $item['short_title'] = $value['goods_title'];
                    $item['commission_rate'] = "0";
                    $item['price'] = $value['s_price'];
                    $item['coupon_price'] = '';
                    $item['volume'] = $value['sale_num'];
                    $item['promotion_type'] = $value['promotion_type'];
                    $item['start_time'] = $value['start_time'];
                    $item['end_time'] = $value['end_time'];
                    $item['goods_id'] = $value['goods_id'];
                    $item['shop_type'] = 'Z';
                    $item['live_commission_percent'] = $value['live_commission_percent'] > 0 ? $value['live_commission_percent'] : $percent;
                    $map["goods_type"] = 1;

                    $count2 = Db::name("live_goods")->where($map)->where("live_status != -1")->count();
                    $goodsKey = 'live_goods_pre:goods:goodsid:shop' . $userId;

                    $goodsHas = $this->redis->sIsMember($goodsKey, $value['goods_id']);
                    if ($count2 > 0 || $goodsHas) {
                        $item['is_add'] = 1;
                    } else {
                        $item['is_add'] = 0;
                    }
                }
                $data[] = $item;
            }
        }

        return array(
            'total_count' => $count,
            'total' => $count,
            'page_count' => $page_count,
            'list' => $data,
        );

    }
}