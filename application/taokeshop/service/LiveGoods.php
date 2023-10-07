<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/5
 * Time: 16:18
 */

namespace app\taokeshop\service;

use app\common\service\Service;
use think\Db;

class LiveGoods extends Service
{
    /**
     * 统计数量
     * @param $where
     * @return int
     */
    public function getTotal($where)
    {
        $count = Db::name('live_goods')->where($where)->count();
        return (int)$count;
    }

    /**
     * 获取直播商品列表
     * @param array $where
     * @param int $page
     * @param int $pageSize
     * @param string $sort
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLiveList($where = [], $page = 1, $pageSize = 10, $sort)
    {
        $list   = [];
        $fields = "anchor_id as id,goods_id,room_id,content,goods_type,live_status,top_time,sort,add_time";
        $list   = Db::name("live_goods")->field($fields)->where($where)->where('live_status > -1')->order($sort)->limit(($page - 1) * $pageSize, $pageSize)->select();
        if ($list) {
            $anchorGoods = new AnchorGoods();
            foreach ($list as $key => $value) {
                if ($value['goods_type'] == 0) {//第三方商品
                    $detail = $anchorGoods->getGoodsInfo(["id" => $value['goods_id']]);
                    if ($detail) {
                        $list[$key]['detail']['img']             = $detail['img'];
                        $list[$key]['detail']['discount_price']  = $detail['discount_price'];
                        $list[$key]['detail']['shop_type']       = $detail['shop_type'];
                        $list[$key]['detail']['title']           = $detail['title'];
                        $list[$key]['detail']['short_title']     = $detail['short_title'];
                        $list[$key]['detail']['commission_rate'] = $detail['commission_rate'];
                        $list[$key]['detail']['price']           = $detail['price'];
                        $list[$key]['detail']['coupon_price']    = $detail['coupon_price'];
                        $list[$key]['detail']['volume']          = $detail['volume'];
                    } else {
                        $list[$key]['detail'] = (object)[];
                    }
                } elseif ($value['goods_type'] == 1) {//自营商品
                    $detail     = $anchorGoods->getMallSkuGoodsInfo(["sku_id" => $value['goods_id']]);
                    $shopConfig = Db::name("shop_shop")->where(['site_id' => $detail['site_id']])->find();
                    if ($shopConfig['live_commission_percent'] > 0) {
                        $percent = $shopConfig['live_commission_percent'];
                    } else {
                        $percent = 0;
                    }
                    if ($detail) {
                        $list[$key]['detail']['img']                     = $detail['sku_image'];
                        $list[$key]['detail']['discount_price']          = $detail['discount_price'];
                        $list[$key]['detail']['shop_type']               = 'Z';
                        $list[$key]['detail']['title']                   = $detail['sku_name'];
                        $list[$key]['detail']['short_title']             = $value['goods_title'];
                        $list[$key]['detail']['commission_rate']         = 0;
                        $list[$key]['detail']['price']                   = $detail['price'];
                        $list[$key]['detail']['coupon_price']            = '';
                        $list[$key]['detail']['volume']                  = $detail['sale_num'];
                        $list[$key]['detail']['live_commission_percent'] = $detail['live_commission_percent'] > 0 ? $detail['live_commission_percent'] : $percent;
                    } else {
                        $list[$key]['detail'] = (object)[];
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 批量添加商品到直播
     * @param $data
     * @return int|string
     */
    public function addToLive($data)
    {
        $res = Db::name("live_goods")->insertAll($data);
        return $res;
    }

    public function updateLiveInfo($where, $data)
    {
        $data['update_time'] = time();
        $res                 = Db::name("live_goods")->where($where)->update($data);
        return $res;
    }

    /**
     * @param $userId
     * @param $roomId
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllGoods($userId, $roomId = 0, $keyword = "")
    {
        $goodsList        = [];
        $where["user_id"] = $userId;
        $anchorGoods      = new AnchorGoods();
        if (!empty($keyword)) {
            $where['keyword'] = $keyword;
        }
        $total     = $anchorGoods->getTotal($where);
        $goodsList = $anchorGoods->getList($where, 0, $total);
        foreach ($goodsList as $key => $value) {
            $map["user_id"]    = $userId;
            $map["room_id"]    = $roomId;
            $map["goods_type"] = 0;
            $map["goods_id"]   = $value['goods_id'];
            $count             = Db::name("live_goods")->where($map)->where("live_status != -1")->count();
            $goodsKey          = 'live_goods_pre:goods:goodsid:taoke' . $userId;
            $goodsHas          = $this->redis->sIsMember($goodsKey, $value['goods_id']);
            if ($count > 0 || $goodsHas) {
                $goodsList[$key]['is_add'] = 1;
            } else {
                $goodsList[$key]['is_add'] = 0;
            }
        }
        return $goodsList;
    }

    //获取直播商品
    public function getLiveGoods($get)
    {
        $result   = [];
        $this->db = Db::name('live_goods');
        $this->setWhere($get);
        $fields = 'lg.user_id,lg.goods_id,lg.room_id,lg.goods_type,lg.live_status';
        $this->db->field('goods.*');
        $result = $this->db->field($fields)->order('lg.live_status asc')->limit(0, 3)->select();
        return $result;
    }

    //条件
    protected function setWhere($get)
    {
        $this->db->alias('lg');
        $where['lg.room_id'] = $get['room_id'];
        $this->db->where($where);
        $this->db->join('__GOODS__ goods', 'lg.goods_id=goods.id', 'LEFT');
        return $this;
    }

    //获取直播商品
    public function getLiveGoodsNew($get)
    {
        $result = [];
        $rest   = Db::name('live_goods')->where($get)->select();
        foreach ($rest as $k => $v) {
            if ($v['goods_type'] == 1) {
                $goodsMall = Db::name('shop_goods_sku')->where(['sku_id' => $v['goods_id']])->find();
                //短标签待查  店名待查
                $tempGoods = [
                    "id"                => $goodsMall['sku_id'],
                    "cate_id"           => 0,
                    "goods_id"          => $goodsMall['sku_id'],
                    "title"             => $goodsMall['sku_name'],
                    "short_title"       => '待查',
                    "img"               => $goodsMall['sku_image'],
                    "gallery_imgs"      => $goodsMall['sku_images'],
                    "desc"              => "",
                    "price"             => $goodsMall['price'],
                    "discount_price"    => $goodsMall['discount_price'],
                    "coupon_price"      => "0.00",
                    "commission_rate"   => "0",
                    "commission"        => "0",
                    "volume"            => 1,
                    "shop_type"         => "Z",
                    "coupon_url"        => "",
                    "has_coupon"        => 0,
                    "coupon_condition"  => "",
                    "coupon_start_time" => 0,
                    "coupon_end_time"   => 0,
                    "coupon_total"      => 0,
                    "coupon_surplus"    => 0,
                    "shop_name"         => $goodsMall['site_name'],
                    "seller_id"         => 0,
                    "shop_id"           => $goodsMall['site_id'],
                    "create_time"       => $goodsMall['create_time'],
                    "update_time"       => $goodsMall['modify_time'],
                    "add_type"          => 1,
                    "add_user_id"       => 0,
                    "is_top"            => 0,
                    "status"            => 1,
                    "collect_num"       => 0,
                    "sort"              => 9999,
                    "is_new"            => 0,
                    "video_url"         => "",
                    "item_url"          => "",
                    "goods_type"        => 0,
                    "is_jhs"            => 0,
                    "is_tqg"            => 0,
                    "is_chaoshi"        => 0,
                    "is_overseas"       => 0,
                    "is_brand"          => 0,
                    "brand_id"          => "0",
                    "brand_name"        => "",
                    "desc_score"        => "0",
                    "desc_percent"      => "0",
                    "ship_score"        => "0",
                    "ship_percent"      => "0",
                    "serv_score"        => "0",
                    "serv_percent"      => "0",
                    "is_recommand"      => 0,
                    "user_id"           => $v['user_id'],
                    "room_id"           => $v['room_id'],
                    "live_status"       => $v['live_status'],
                    "promotion_type"   => $goodsMall['promotion_type']
                ];
                $result[] = $tempGoods;
            } else {
                $goods    = Db::name('goods')->where(['id' => $v['goods_id']])->find();
                $goods[ "user_id" ] = $v['user_id'];
                $goods[ "room_id" ] = $v['room_id'];
                $goods[ "live_status" ] = $v['live_status'];
                $goods[ "promotion_type" ]  =  0;
                $result[] = $goods;
            }
        }
        $last_names = array_column($result,'live_status');
        array_multisort($last_names,SORT_ASC,$result);
        return $result;
    }
}