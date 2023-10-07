<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/24
 * Time: 16:23
 */
namespace app\taokegoods\service;

use app\taokeshop\service\AnchorGoods;
use bxkj_module\service\Service;
use think\Db;

class Goods extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('goods');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $this->db = Db::name('goods');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        if ($get['cate_id'] != 0) {
            $where['cate_id'] = $get['cate_id'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
//        if (isset($get['is_recommand']) && $get['is_recommand'] != "") {
//            $where['is_recommand'] = $get['is_recommand'];
//        }
        if (isset($get['type']) && $get['type'] != "") {
            if($get['type'] == "B"){
                $type = ["B", "C"];
            }else{
                $type = $get['type'];
            }
            $where['shop_type'] = $type;
        }

        if ($get['keyword'] != '') {
            $where1[] = ['id|goods_id|title','like','%'.$get['keyword'].'%'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['id'] = 'DESC';
        }else{
            $order = $get['sort'];
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $id = Db::name('goods')->insertGetId($data);
        return $id;
    }

    public function update($where, $data)
    {
        $status = Db::name('goods')->where($where)->update($data);
        return $status;
    }

    public function getGoodsInfo($where)
    {
        $info = Db::name('goods')->where($where)->find();
        return $info;
    }

    /**
     * 删除商品库商品，同时删除商品橱窗已被添加的商品
     * @param $where
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del($where)
    {
        $status = Db::name('goods')->where($where)->delete();
        if($status !== false){
            $anchorGoods = new AnchorGoods();
            foreach ($where['id'] as $value){
                $anchorGoods->delGoods(["goods_id" => $value]);
            }
        }
        return $status;
    }

    /**
     * 格式化淘宝联盟搜索商品
     * @param $data
     * @return array
     */
    public function formatTbGoods($data)
    {
        $goods = [];
        foreach ($data as $value){
            if(isset($value['num_iid'])){
                $info['goods_id'] = $value['num_iid'];
            }
            if(isset($value['item_id'])){
                $info['goods_id'] = $value['item_id'];
            }
            $info['title'] = $value['title'];
            if(isset($value['sub_title']) && empty($value['short_title'])){
                $info['short_title'] = $value['sub_title'];
            }else {
                $info['short_title'] = $value['short_title'];
            }
            $info['shop_type'] = $value['user_type'] == 1 ? "B" : "C";
            $info['img'] = strpos($value['pict_url'], "http") === false ? "http:".$value['pict_url'] : $value['pict_url'];
            if(isset($value['item_description'])) {
                $info['desc'] = $value['item_description'];
            }
            if(isset($value['small_images'])) {
                $galleryImgs = $value['small_images']['string'];
                $imgs = [];
                foreach ($galleryImgs as $k => $img){
                    $imgs[$k] = strpos($img, "http") === false ? "http:".$img : $img;
                }
                $info['gallery_imgs'] = implode(",", $imgs);
            }
            $info['price'] = $value['zk_final_price'];
            $has_coupon = 0;
            $coupon_price = 0;
            if(isset($value['coupon_amount']) && $value['coupon_amount'] > 0){
                $coupon_price = $value['coupon_amount'];
                if(!empty($value['seller_id']) && !empty($value['coupon_id'])){
                    $couponUrl = "https://uland.taobao.com/quan/detail?sellerId=".$value['seller_id']."&activityId=".$value['coupon_id'];
                }else{
                    $couponUrl = "";
                }
                $info['coupon_url'] = $couponUrl;
                $info['coupon_total'] = $value['coupon_total_count'];
                $info['coupon_surplus'] = isset($value['coupon_remain_count']) ? $value['coupon_remain_count'] : 0;
                $info['coupon_condition'] = floatval($value['coupon_start_fee']);
                $info['coupon_start_time'] = is_numeric($value['coupon_start_time']) ? $value['coupon_start_time'] / 1000 : strtotime($value['coupon_start_time']);
                $info['coupon_end_time'] = is_numeric($value['coupon_end_time']) ? $value['coupon_end_time'] / 1000 : strtotime($value['coupon_end_time']);
                $has_coupon = 1;
            }
            $info['has_coupon'] = $has_coupon;
            $info['coupon_price'] = $coupon_price;
            $info['discount_price'] = ($info['price'] - $coupon_price);
            $info['commission_rate'] = $value['commission_rate'];
            $info['commission'] = substr(sprintf("%.3f", $info['discount_price'] * $info['commission_rate'] / 100), 0, -1);
            $info['volume'] = $value['volume'];
            $info['item_url'] = "https://detail.tmall.com/item.htm?id=".$info['goods_id'];
            $info['seller_id'] = empty($value['seller_id']) ? "" : $value['seller_id'];
            $info['shop_name'] = empty($value['shop_title']) ? "" : $value['shop_title'];
            $goods[] = $info;
        }
        return $goods;
    }

    /**
     * 格式化多多进宝搜索商品
     * @param $data
     * @return array
     */
    public function formatPddGoods($data)
    {
        $goods = [];
        foreach ($data as $value){
            $info['goods_id'] = $value['goods_id'];
            $info['pdd_goods_sign'] = $value['goods_sign'];
            $info['title'] = $value['goods_name'];
            $info['desc'] = $value['goods_desc'];
            $info['img'] = $value['goods_thumbnail_url'];
            $info['gallery_imgs'] = json_encode($value['goods_gallery_urls'], true);
            $info['price'] = floatval($value['min_group_price'] / 100);
            $info['coupon_price'] = floatval($value['coupon_discount'] / 100);
            $info['discount_price'] = floatval($info['price'] - $info['coupon_price']);
            $info['coupon_start_time'] = $value['coupon_start_time'];
            $info['coupon_end_time'] = $value['coupon_end_time'];
            $info['volume'] = $value['sales_tip'];
            $info['commission_rate'] = $value['promotion_rate'] / 10;
            $info['commission'] = substr(sprintf("%.3f", $info['discount_price'] * $info['commission_rate'] / 100), 0, -1);
            $info['shop_type'] = "P";
            $info['item_url'] = "https://mobile.yangkeduo.com/goods2.html?goods_id=".$value['goods_id'];
            $info['has_coupon'] = ($info['coupon_price'] == 0) ? 0 : 1;
            if($info['has_coupon'] == 1){
                $info['coupon_condition'] = floatval($value['coupon_min_order_amount'] / 100);
                $info['coupon_total'] = $value['coupon_total_quantity'];
                $info['coupon_surplus'] = $value['coupon_remain_quantity'];
            }
            $info['shop_name'] = $value['mall_name'];
            $info['shop_id'] = $value['mall_id'];
            $goods[] = $info;
        }
        return $goods;
    }

    /**
     * 格式化京东联盟搜索商品
     * @param $data
     * @return array
     */
    public function formatJdGoods($data)
    {
        $goods = [];
        foreach ($data as $value){
            $info['goods_id'] = $value['skuId'];
            $info['title'] = $value['skuName'];
            $info['spuid'] = $value['spuid'];
            $info['shop_type'] = "J";
            if(!empty($value['imageInfo'])){
                $info['img'] = $value['imageInfo']['imageList'][0]['url'];
                $imgArr = $value['imageInfo']['imageList'];
                $galleryImgs = [];
                foreach($imgArr as $val){
                    $galleryImgs[] = $val['url'];
                }
                $info['gallery_imgs'] = json_encode($galleryImgs, true);
            }
            $info['price'] = $value['priceInfo']['price'];
            $couponPrice = 0;
            $has_coupon = 0;
            if(!empty($value['couponInfo'])){
                $couponInfo = [];
                foreach ($value['couponInfo']['couponList'] as $v) {
                    if ($v['isBest'] == 1) {
                        $couponInfo = $v;
                    }
                }
                if(empty($couponInfo)){
                    $couponInfo = $value['couponInfo']['couponList'][0];
                }
                $couponPrice = $couponInfo['discount'];
                $couponCondition = $couponInfo['quota'];
                $info['coupon_start_time'] = intval($couponInfo['useStartTime'] / 1000);
                $info['coupon_end_time'] = intval($couponInfo['useEndTime'] / 1000);
                $info['coupon_url'] = $couponInfo['link'];
                $info['coupon_condition'] = $couponCondition;
                $has_coupon = 1;
            }
            $info['has_coupon'] = $has_coupon;
            $info['coupon_price'] = $couponPrice;
            $info['item_url'] = "https://item.m.jd.com/product/".$value['skuId'].".html";
            $info['discount_price'] = floatval($info['price'] - $couponPrice);
            $info['volume'] = $value['inOrderCount30Days'];
            $info['commission_rate'] = $value['commissionInfo']['commissionShare'];
            $info['commission'] = $value['commissionInfo']['commission'];
            $info['shop_name'] = $value['shopInfo']['shopName'];
            $info['shop_id'] = $value['shopInfo']['shopId'];
            $goods[] = $info;
        }
        return $goods;
    }

    public function addAsycGoods($data)
    {
        $list = [];
        foreach($data as $value) {
            if($this->getGoodsInfo(["goods_id" => $value['goods_id']])) {
                continue;
            }
            $good['goods_id'] = $value['goods_id'];
            $good['cate_id'] = $value['cate_id'];
            $good['title'] = $value['title'];
            $good['short_title'] = $value['short_title'];
            $good['img'] = $value['img'];
            $good['desc'] = $value['goods_desc'];
            $good['price'] = $value['price'];
            $good['discount_price'] = $value['discount_price'];
            $good['coupon_price'] = $value['coupon_price'];
            $good['commission_rate'] = $value['commission_rate'];
            $good['commission'] = $value['commission'];
            $good['volume'] = $value['volume'];
            $good['shop_type'] = $value['shop_type'];
            $good['coupon_url'] = isset($value['coupon_url']) ? $value['coupon_url'] : "";
            $good['has_coupon'] = empty($value['has_coupon']) ? 0 : $value['has_coupon'];
            $good['coupon_condition'] = $value['coupon_condition'];
            $good['coupon_start_time'] = $value['coupon_start_time'];
            $good['coupon_end_time'] = $value['coupon_end_time'];
            $good['coupon_total'] = $value['coupon_total'];
            $good['coupon_surplus'] = $value['coupon_surplus'];
            $good['item_url'] = $value['item_url'];
            $good['shop_name'] = $value['shop_name'];
            $good['seller_id'] = isset($value['seller_id']) ? $value['seller_id'] : 0;
            $good['shop_id'] = empty($value['shop_id']) ? 0 : $value['shop_id'];
            $good['is_jhs'] = $value['is_jhs'];
            $good['is_tqg'] = $value['is_tqg'];
            $good['is_chaoshi'] = $value['is_chaoshi'];
            $good['is_overseas'] = $value['is_overseas'];
            $good['is_brand'] = $value['is_brand'];
            $good['brand_id'] = $value['brand_id'];
            $good['brand_name'] = $value['brand_name'];
            $good['desc_score'] = $value['desc_score'];
            $good['desc_percent'] = $value['desc_percent'];
            $good['ship_score'] = $value['ship_score'];
            $good['ship_percent'] = $value['ship_percent'];
            $good['serv_score'] = $value['serv_score'];
            $good['serv_percent'] = $value['serv_percent'];
            $good['create_time'] = time();
            $list[] = $good;
        }
        $result = 0;
        if($list){
            $result = Db::name('goods')->insertAll($list);
        }
        return $result;
    }
}