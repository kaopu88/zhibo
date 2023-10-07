<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/9/4 0004
 * Time: 下午 4:07
 */

namespace app\api\service;

use bxkj_common\RedisClient;
use bxkj_module\service\User;
use think\Db;
use think\Model;

class Goods extends Model
{
    protected $type = ['TaokeGoods', 'ShopGoods'];

    public function getGoods($type = 0, $goods_id)
    {
        $method = $this->type[$type];

        if (empty($method)) return ['code' => 9101, 'msg' => '类型不存在'];
        if (empty($goods_id)) return ['code' => 9102, 'msg' => '商品id不能为空'];
        if (!method_exists($this, 'get' . $method)) return ['code' => 9103, 'msg' => '方法不存在'];

        $res = call_user_func_array([$this, 'get' . $method], [['goods_id' => $goods_id]]);

        if (empty($res)) return ['code' => 9104, 'msg' => '商品不存在'];
        return ['code' => 200, 'goods' => $res];
    }

    public function getTaokeGoods(array $params, $type = 0)
    {
        $redis = RedisClient::getInstance();

        if (empty($type)) {
            $anchor_goods = Db::name('anchor_goods')->where(['goods_id' => $params['goods_id'], 'user_id' => USERID])->find();
            if (empty($anchor_goods)) return false;
        }

        $key = 'goods_detail:taoke:' . $params['goods_id'];
        if (!$redis->exists($key)) {
            $goods = Db::name('goods')->field('id as goods_id,title,shop_type,img,short_title,price,discount_price,has_coupon,coupon_price,shop_name,status')->where(['id' => $params['goods_id'], 'status' => 1])->find();
            if (empty($goods) && $goods['status'] == 0) return false;
            $goods['short_title'] = $anchor_goods['goods_title'] ? $anchor_goods['goods_title'] : $goods['short_title'];
            $redis->set($key, json_encode($goods), 3600);
        } else {
            $goods = json_decode($redis->get($key), true);
        }

        $goods = array_merge(['anchor_id' => $anchor_goods['id']], $goods);
        return $goods;
    }

    public function getShopGoods(array $params, $type = 0)
    {
        $redis = RedisClient::getInstance();

        if (empty($type)) {
            $anchor_goods = Db::name('anchor_goods')->where(['goods_id' => $params['goods_id'], 'user_id' => USERID])->find();
            if (empty($anchor_goods)) return false;
        }

        $key = 'goods_detail:shop:' . $params['goods_id'];
        if (!$redis->exists($key)) {
            $goods = Db::name('shop_goods_sku')->field('goods_id as g_id,sku_id as goods_id, goods_name as title,sku_image as img, discount_price as discount_price,goods_state as status, promotion_type, start_time, end_time')->where(['sku_id' => $params['goods_id'], 'goods_state' => 1])->find();
            if (empty($goods) && $goods['status'] == 0) return false;
            $where[] = ["", 'exp', Db::raw("FIND_IN_SET({$goods['g_id']}, goods_ids)")];
            $where[] = ["status","eq",1];
            $coupopn = Db::name('shop_promotion_coupon_type')->where($where)->find();
            if(empty($coupopn)){
                $goods['coupon_price'] = 0.00;
            }else{
                $goods['coupon_price'] = $coupopn['money']>0?$coupopn['money']:$coupopn['discount'];
            }
            $goods['short_title'] = $anchor_goods['goods_title'] ? $anchor_goods['goods_title'] : '';
            $redis->set($key, json_encode($goods), 3600);
        } else {
            $goods = json_decode($redis->get($key), true);
        }
        $goods = array_merge(['anchor_id' => $anchor_goods['id']], $goods);
        return $goods;
    }

    /**
     * TODO
     * 获取添加过的商品列表
     */
    public function getLiveList(array $where = [], $page = 1, $pageSize = 10, $sort = "id desc")
    {
        $fields = "anchor_id as id,goods_id,content,goods_type,live_status,top_time,sort,add_time";
        $list = Db::name("live_pre_goods")->field($fields)->where($where)->where('live_status > -1')->order($sort)->page($page, $pageSize)->select();

        if (empty($list)) return [];
        $good_detail = [];
        foreach ($list as $key => $value) {
            if ($value['goods_type'] == 0) {
                //第三方商品
                $goodsdetail = $this->getTaokeGoods(['goods_id' => $value['goods_id']]);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => $goodsdetail['coupon_price'], 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => $goodsdetail['shop_type'], 'title' => $goodsdetail['title']
                ];
            } else {
                //自营商品
                $goodsdetail = $this->getShopGoods(['goods_id' => $value['goods_id']]);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => isset($goodsdetail['coupon_price']) ? $goodsdetail['coupon_price'] : 0, 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => 'Z', 'title' => $goodsdetail['title']
                ];
            }

            $good_detail[] = ['detail' => $detail, 'goods_type' => $value['goods_type'], 'live_status' => $value['live_status'], 'goods_id' => $value['goods_id'], 'id' => $value['id'], 'top_time' => $value['top_time'],
                'add_time' => $value['add_time'], 'content' => ($value['content'] ? $value['content'] : '')];
        }

        return $good_detail;
    }

    public function addGoods(array $params, $type = 0, $sitId = 0)
    {
        if (empty($params)) return ['code' => 9201, 'msg' => '参数错误'];
        if($type == 1){//自营商品
            $redis = RedisClient::getInstance();
            $cacheFriend = $redis->exists('cache:livegoods_config');
            if (empty($cacheFriend)) {
                $info = Db::name('shop_config')->where([['site_id', '=', 0], ['app_module', '=', 'admin'], ['config_key', '=', 'LIVE_GOODS_CONFIG']])->find();
                if(!empty($info)) {
                    $info['value'] = json_decode($info['value'], true);
                }else{
                    $info = ['site_id' => 0, 'app_module' => 'admin', 'config_key' => 'LIVE_GOODS_CONFIG', 'value' => [], 'config_desc' => '', 'is_use' => 0, 'create_time' => 0, 'modify_time' => 0];
                    $info['value'] = ["withdraw_status" => 1, "withdraw" => 0, "withdraw_rate" => 0, "is_use" => 1, "is_live" => 1];
                }
                $redis->setex('cache:livegoods_config', 3600, json_encode($info['value']));
            }
            $livegoods_config = json_decode($redis->get('cache:livegoods_config'), true);
            if($livegoods_config['is_live'] == 0 )return ['code' => 9208, 'msg' => '商城商品已被禁止直播带货'];
            $zyGoodsInfo = Db::name('shop_goods_sku')->field("goods_id")->where(['sku_id' => $params['goods_id']])->find();
            $goodsInfo = Db::name('shop_goods')->field("can_living")->where(['goods_id' => $zyGoodsInfo['goods_id']])->find();
            if ($goodsInfo['can_living']== 0) return ['code' => 9209, 'msg' => '商品已被禁止直播'];
        }
        $preHasGoods = Db::name('live_pre_goods')->where(['goods_id' => $params['goods_id'], 'goods_type' => $type, 'user_id' => USERID])->find();
        if (!empty($preHasGoods)) return ['code' => 9202, 'msg' => '该商品已经添加过啦'];
        $data['user_id'] = USERID;
        $data['goods_id'] = $params['goods_id'];
        $data['anchor_id'] = $params['anchor_id'];
        $data['promotion_type'] = isset($params['promotion_type']) ? $params['promotion_type'] : 0;
        $data['goods_type'] = $type;
        $data['site_id'] = $sitId;
        $data['live_status'] = 0;
        $data['add_time'] = time();
        $data['status'] = 1;
        $res = Db::name('live_pre_goods')->insert($data);
        if (!$res) return ['code' => 9203, 'msg' => '添加失败'];
        return ['code' => 200];
    }

    public function delGoods($goods_id, $type = 0)
    {
        if (empty($goods_id)) return false;
        $res = Db::name('live_pre_goods')->where(['user_id' => USERID, 'goods_type' => $type, 'goods_id' => $goods_id])->delete();
        if (!$res) return false;
        return true;
    }

    /**
     * 更新
     * @param $goods_id
     * @param array $field
     */
    public function updateGoods($goods_id, $type = 0, array $field)
    {
        if (empty($goods_id)) return false;
        $res = Db::name('live_pre_goods')->where(['user_id' => USERID, 'goods_type' => $type, 'goods_id' => $goods_id])->update($field);
        if (!$res) return false;
        return true;
    }

    /**
     * 获取正在直播的直播间商品
     */
    public function getRoomLiveList(array $where = [], $page = 1, $pageSize = 10, $sort = "id desc")
    {
        $fields = "anchor_id as id,goods_id,content,goods_type,live_status,top_time,sort,add_time";
        $list = Db::name("live_goods")->field($fields)->where($where)->where('live_status > -1')->order($sort)->page($page, $pageSize)->select();
        if (empty($list)) return [];
        $good_detail = [];
        $now = time();
        foreach ($list as $key => $value) {
            if ($value['goods_type'] == 0) {
                $goodsdetail = $this->getTaokeGoods(['goods_id' => $value['goods_id']], 1);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => $goodsdetail['coupon_price'], 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => $goodsdetail['shop_type'], 'title' => $goodsdetail['title']
                ];
            } else {
                $goodsdetail = $this->getShopGoods(['goods_id' => $value['goods_id']], 1);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => isset($goodsdetail['coupon_price']) ? $goodsdetail['coupon_price'] : 0, 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => 'Z', 'title' => $goodsdetail['title']
                ];
            }

            $activty = [
                'promotion_type' => isset($goodsdetail['promotion_type']) ? $goodsdetail['promotion_type'] : 0,
                'start_time' => isset($goodsdetail['start_time']) ? ($goodsdetail['start_time'] - $now > 0 ? $goodsdetail['start_time'] - $now : 0) : 0,
                'end_time' => isset($goodsdetail['end_time']) ? ($goodsdetail['end_time'] - $now > 0 ? $goodsdetail['end_time'] - $now : 0) : 0
            ];

            $good_detail[] = ['detail' => $detail, 'goods_type' => $value['goods_type'], 'live_status' => $value['live_status'], 'goods_id' => $value['goods_id'], 'id' => $value['id'], 'top_time' => $value['top_time'],
                'add_time' => $value['add_time'], 'content' => ($value['content'] ? $value['content'] : ''), 'goods_activty' => $activty];
        }

        return $good_detail;
    }

    //获取直播间是否有活动商品
    public function getShopActivtyGoods($room_id, array $where)
    {
        $fields = "anchor_id as id,goods_id,content,goods_type,live_status,top_time,sort,add_time";
        $live_good = Db::name('live_goods')->field($fields)->where($where)->where('live_status > -1')->order('live_status desc,add_time desc, id desc')->find();
        if (empty($live_good)) return false;
        if ($live_good['goods_type'] == 0) $goodsdetail = $this->getTaokeGoods(['goods_id' => $live_good['goods_id']], 1);
        if ($live_good['goods_type'] == 1) $goodsdetail = $this->getShopGoods(['goods_id' => $live_good['goods_id']], 1);

        if (empty($goodsdetail)) return false;
        return ['live_good' => $live_good, 'goodsdetail' => $goodsdetail];
    }

    /**
     * 直播间小窗优惠卷
     * @param $user_id  该直播间主播的id
     * @param $member_id 领取的用户
     */
    public function getCoupon($user_id, $member_id)
    {
        $shop_open = config('app.live_setting.is_shop_open') ? config('app.live_setting.is_shop_open') : 0;
        if (empty($shop_open)) return false;
        $userModel = new User();
        $user = $userModel->getUser($user_id);
        if (empty($user)) return false;
        if (empty($user['instance_id'])) return false;
        $coupon = Db::name('shop_promotion_coupon_type')
            ->field('coupon_type_id, type, site_id, coupon_name, money, discount, max_fetch, at_least, end_time, image, validity_type, fixed_term, status, is_show, goods_type, site_name')
            ->where(['site_id' => $user['instance_id'], 'status' => 1])
            ->order('sort des, coupon_type_id desc')
            ->find();
        if (empty($coupon)) return false;
        $coupon['fetched'] = 0;
        if ($user_id != $member_id) {
            $received_num = Db::name('shop_promotion_coupon')->where([['coupon_type_id', '=', $coupon['coupon_type_id']], ['member_id', '=', $member_id]])->count();
            $coupon['fetched'] = $received_num ? 1 : 0;
        }
        return $coupon;
    }
}