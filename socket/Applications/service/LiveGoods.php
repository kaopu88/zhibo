<?php

namespace app\service;

use app\Common;
use \GatewayWorker\Lib\Gateway;

class LiveGoods extends Common
{
    /**
     * 用户进入直播间发送直播商品消息
     * @param array $params
     */
    public static function getLiveGoods(array $params)
    {
        global $redis;
        if (!isset($params['room_id']) || $params['user_id'] != $_SESSION['user_id']) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '数据错误~5', [], 1));
            return false;
        }
        $key = "livegoods:{$params['room_id']}";
        $json = $redis->get($key);
        $data = $json ? json_decode($json, true) : [];
        Gateway::sendToCurrentClient(self::genMsg('switchListGoods', '', $data));

        return true;
    }

    /**
     * 用于主播更新redis中的数据
     * @param array $params
     */
    public static function addLivegoods(array $params)
    {
        global $db, $redis;
        if (!isset($params['room_id']) || $params['user_id'] != $_SESSION['user_id']) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '数据错误~', [], 1));
            return false;
        }
        //$goods_sql = "select lg.anchor_id as id,g.title,g.shop_type,g.img,g.short_title,g.price,g.discount_price,g.has_coupon,g.coupon_price,g.shop_name,lg.goods_type, lg.goods_id, lg.content, lg.top_time, lg.add_time, lg.live_status from " . TABLE_PREFIX . "live_goods as lg LEFT JOIN " . TABLE_PREFIX ."goods as g ON lg.goods_id=g.id WHERE lg.user_id={$params['user_id']} and lg.room_id={$params['room_id']} and lg.status=1 and lg.live_status>-1  order by lg.live_status desc,lg.top_time desc,lg.add_time desc";
        //$say_goods_result = $db->query($goods_sql);
        $goods_sql = "select anchor_id as id,goods_type, goods_id, content, top_time, add_time, live_status from " . TABLE_PREFIX . "live_goods WHERE user_id={$params['user_id']} and room_id={$params['room_id']} and status=1 and live_status>-1  order by live_status desc,top_time desc,add_time desc";
        $say_goods_result = $db->query($goods_sql);

        $key = "livegoods:{$params['room_id']}";
        if (empty($say_goods_result)) {
            $redis->del($key);
            return;
        }
        $good_detail = [];
        $now = time();
        foreach ($say_goods_result as $k => $value) {
            if ($value['goods_type'] == 0) {
                //第三方商品
                $goodsdetail = self::getTaokeGoods($value['goods_id']);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => $goodsdetail['coupon_price'], 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => $goodsdetail['shop_type'], 'title' => $goodsdetail['title']
                ];
            } elseif ($value['goods_type'] == 1) {
                $goodsdetail = self::getShopGoods($value['goods_id']);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => isset($goodsdetail['coupon_price']) ? $goodsdetail['coupon_price'] : 0, 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => 'Z', 'title' => $goodsdetail['title']
                ];
            } else {
                continue;
            }

            $activty = [
                'promotion_type' => isset($goodsdetail['promotion_type']) ? $goodsdetail['promotion_type'] : 0,
                'start_time' => isset($goodsdetail['start_time']) ? ($goodsdetail['start_time'] - $now > 0 ? $goodsdetail['start_time'] - $now : 0) : 0,
                'end_time' => isset($goodsdetail['end_time']) ? ($goodsdetail['end_time'] - $now > 0 ? $goodsdetail['end_time'] - $now : 0) : 0
            ];

            $good_detail[] = ['detail' => $detail, 'goods_type' => $value['goods_type'], 'live_status' => $value['live_status'], 'goods_id' => $value['goods_id'], 'id' => $value['id'], 'top_time' => $value['top_time'],
                'add_time' => $value['add_time'], 'content' => ($value['content'] ? $value['content'] : ''), 'goods_activty' => $activty];
        }
        $data = ['room_id' => $params['room_id'], 'goods' => $good_detail];
        $setRes = $redis->set($key, json_encode($data));
        return true;
    }

    protected static function getTaokeGoods($goods_id)
    {
        global $db, $redis;
        if (empty($goods_id)) return false;
        $key = 'goods_detail:taoke:' . $goods_id;
        if (!$redis->exists($key)) {
            $good_sql = "select id as goods_id, title, shop_type, img, short_title, price, discount_price, has_coupon, coupon_price, shop_name, status from " . TABLE_PREFIX . "goods where id =" . $goods_id;
            $goods_result = $db->query($good_sql);
            if (empty($goods_result)) return false;
            $goods = $goods_result[0];
            $redis->set($key, json_encode($goods), 3600);
        } else {
            $goods = json_decode($redis->get($key), true);
        }
        return $goods;
    }

    protected static function getShopGoods($goods_id)
    {
        global $db, $redis;
        if (empty($goods_id)) return false;
        $key = 'goods_detail:shop:' . $goods_id;
        if (!$redis->exists($key)) {
            $good_sql = "select sku_id as goods_id, goods_name as title,sku_image as img, discount_price as discount_price,goods_state as status,promotion_type,start_time,end_time   from " . TABLE_PREFIX . "shop_goods_sku WHERE sku_id={$goods_id}  LIMIT 1";
            $goods_result = $db->query($good_sql);
            if (empty($goods_result)) return false;
            $goods = $goods_result[0];
            $redis->set($key, json_encode($goods), 3600);
        } else {
            $goods = json_decode($redis->get($key), true);
        }
        return $goods;
    }

    /**
     * 添加直播商品
     * @param array $params
     */
    public static function addGoods(array $params)
    {
        global $db, $redis;
        $room_id = $params['room_id'];
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $live_status = $params['live_status'] ? $params['live_status'] : 0;
        $goods_type = isset($params['goods_type']) ? $params['goods_type'] : 0;

        $say_goods_sql = "select * from " . TABLE_PREFIX . "live_goods WHERE goods_id={$goods_id} and user_id={$user_id} and goods_type={$goods_type} and room_id={$room_id} LIMIT 1";
        $say_goods_result = $db->query($say_goods_sql);
        if (!empty($say_goods_result)) {
            if ($say_goods_result[0]['live_status'] != -1) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该商品已经在直播啦~~~', [], 1));
                return;
            }

            //表示属于哪个商品库中的
            if (empty($goods_type)) {
                $goods = $db->query("select id as goods_id,title,shop_type,img,short_title,price,discount_price,has_coupon,coupon_price,shop_name,status from " . TABLE_PREFIX . "goods WHERE id={$goods_id}  LIMIT 1");
            }

            if ($goods_type == 1) {
                $cacheFriend = $redis->exists('cache:livegoods_config');
                if (empty($cacheFriend)) {
                    $info = $db->query("select *  from " . TABLE_PREFIX . "shop_config WHERE site_id=0 and app_module='admin' and config_key='LIVE_GOODS_CONFIG' LIMIT 1");
                    $info = $info[0];
                    if (!empty($info)) {
                        $info['value'] = json_decode($info['value'], true);
                    } else {
                        $info = ['site_id' => 0, 'app_module' => 'admin', 'config_key' => 'LIVE_GOODS_CONFIG', 'value' => [], 'config_desc' => '', 'is_use' => 0, 'create_time' => 0, 'modify_time' => 0];
                        $info['value'] = ["withdraw_status" => 1, "withdraw" => 0, "withdraw_rate" => 0, "is_use" => 0, "is_live" => 0,];
                    }
                    $redis->setex('cache:livegoods_config', 3600, json_encode($info['value']));
                }
                $livegoods_config = json_decode($redis->get('cache:livegoods_config'), true);
                if ($livegoods_config['is_live'] == 0) {
                    Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商城商品已被禁止直播带货', [], 1));
                    return;
                }
                //查自营
                $goods = $db->query("select goods_id, goods_name as title,sku_image as img, discount_price as discount_price,goods_state as status,promotion_type,start_time,end_time,can_living  from " . TABLE_PREFIX . "shop_goods_sku WHERE sku_id={$goods_id}  LIMIT 1");
                $goodsId = $goods[0]['goods_id'];
                $goodsMain = $db->query("select can_living  from " . TABLE_PREFIX . "shop_goods WHERE goods_id={$goodsId}  LIMIT 1");
                if ($goodsMain[0]['can_living'] == 0) {
                    Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商品已被禁止直播', [], 1));
                    return;
                }
            }

            if (empty($goods) || $goods[0]['status'] == 0) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商品不存在或已下架~', [], 1));
                return;
            }
            $promotion_type = isset($goods[0]['promotion_type']) ? $goods[0]['promotion_type'] : 0;
            $addtime = time();
            $res = $db->update(TABLE_PREFIX . 'live_goods')->cols(['live_status' => $live_status, 'add_time' => $addtime, 'promotion_type' => $promotion_type])->where("id=" . $say_goods_result[0]['id'])->query();
            if (!$res) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '添加失败,请重试~', [], 1));
                return;
            }
            $good_detail = [];
            $detail = [
                'discount_price' => $goods[0]['discount_price'], 'coupon_price' => isset($goods[0]['coupon_price']) ? $goods[0]['coupon_price'] : 0, 'img' => ($goods[0]['img'] ? $goods[0]['img'] : ''),
                'shop_type' => isset($goods[0]['shop_type']) ? $goods[0]['shop_type'] : 'Z', 'title' => $goods[0]['title']
            ];
            $now = time();
            $activty = [
                'promotion_type' => isset($goods[0]['promotion_type']) ? $goods[0]['promotion_type'] : 0,
                'start_time' => isset($goods[0]['start_time']) ? ($goods[0]['start_time'] - $now > 0 ? $goods[0]['start_time'] - $now : 0) : 0,
                'end_time' => isset($goods[0]['end_time']) ? ($goods[0]['end_time'] - $now > 0 ? $goods[0]['end_time'] - $now : 0) : 0
            ];
            $good_detail[] = ['detail' => $detail, 'goods_type' => $goods_type, 'goods_id' => $goods_id, 'top_time' => $say_goods_result[0]['top_time'],
                'id' => $say_goods_result[0]['anchor_id'], 'add_time' => $addtime, 'content' => ($say_goods_result[0]['content'] ? $say_goods_result[0]['content'] : ''), 'goods_activty' => $activty];
            $data = ['live_status' => $live_status, 'room_id' => $params['room_id'], 'goods' => $good_detail];
            Gateway::sendToGroup($room_id, self::genMsg('switchAddGoods', '加入成功', $data));
        } else {
            if (empty($goods_type)) {
                $goods = $db->query("select id as goods_id,title,shop_type,img,short_title,price,discount_price,has_coupon,coupon_price,shop_name,status from " . TABLE_PREFIX . "goods WHERE id={$goods_id}  LIMIT 1");
            }
            if ($goods_type == 1) {
                $cacheFriend = $redis->exists('cache:livegoods_config');
                if (empty($cacheFriend)) {
                    $info = $db->query("select *  from " . TABLE_PREFIX . "shop_config WHERE site_id=0 and app_module='admin' and config_key='LIVE_GOODS_CONFIG' LIMIT 1");
                    $info = $info[0];
                    if (!empty($info)) {
                        $info['value'] = json_decode($info['value'], true);
                    } else {
                        $info = ['site_id' => 0, 'app_module' => 'admin', 'config_key' => 'LIVE_GOODS_CONFIG', 'value' => [], 'config_desc' => '', 'is_use' => 0, 'create_time' => 0, 'modify_time' => 0];
                        $info['value'] = ["withdraw_status" => 1, "withdraw" => 0, "withdraw_rate" => 0, "is_use" => 0, "is_live" => 0,];
                    }
                    $redis->setex('cache:livegoods_config', 3600, json_encode($info['value']));
                }
                $livegoods_config = json_decode($redis->get('cache:livegoods_config'), true);
                if ($livegoods_config['is_live'] == 0) {
                    Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商城商品已被禁止直播带货', [], 1));
                    return;
                }

                //查自营
                $goods = $db->query("select goods_id, goods_name as title,sku_image as img, discount_price as discount_price,goods_state as status,promotion_type,start_time,end_time  from " . TABLE_PREFIX . "shop_goods_sku WHERE sku_id={$goods_id}  LIMIT 1");
                $goodsId = $goods[0]['goods_id'];
                $goodsMain = $db->query("select can_living  from " . TABLE_PREFIX . "shop_goods WHERE goods_id={$goodsId}  LIMIT 1");
                if ($goodsMain[0]['can_living'] == 0) {
                    Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商品已被禁止直播', [], 1));
                    return;
                }
            }

            if (empty($goods) || $goods[0]['status'] == 0) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商品不存在或已下架~', [], 1));
                return;
            }
            $addtime = time();
            $insert_id = $db->insert(TABLE_PREFIX . "live_goods")->cols(array(
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'anchor_id' => $params['anchor_id'] ?: 0,
                'goods_type' => $goods_type,
                'live_status' => $live_status,
                'add_time' => $addtime,
                'status' => 1,
                'site_id' => isset($params['site_id']) ? $params['site_id'] : 0,
                'promotion_type' => isset($goods[0]['promotion_type']) ? $goods[0]['promotion_type'] : 0,
                'room_id' => $room_id))->query();
            if (!$insert_id) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '添加失败~', [], 1));
                return;
            }
            $good_detail = [];
            $detail = [
                'discount_price' => $goods[0]['discount_price'], 'coupon_price' => isset($goods[0]['coupon_price']) ? $goods[0]['coupon_price'] : 0, 'img' => ($goods[0]['img'] ? $goods[0]['img'] : ''),
                'shop_type' => isset($goods[0]['shop_type']) ? $goods[0]['shop_type'] : 'Z', 'title' => $goods[0]['title']
            ];
            $now = time();
            $activty = [
                'promotion_type' => isset($goods[0]['promotion_type']) ? $goods[0]['promotion_type'] : 0,
                'start_time' => isset($goods[0]['start_time']) ? ($goods[0]['start_time'] - $now > 0 ? $goods[0]['start_time'] - $now : 0) : 0,
                'end_time' => isset($goods[0]['end_time']) ? ($goods[0]['end_time'] - $now > 0 ? $goods[0]['end_time'] - $now : 0) : 0
            ];
            $good_detail[] = ['detail' => $detail, 'goods_type' => $goods_type, 'goods_id' => $goods_id, 'top_time' => 0, 'id' => $params['anchor_id'],
                'add_time' => $addtime, 'content' => '', 'goods_activty' => $activty];
            $data = ['live_status' => $live_status, 'room_id' => $params['room_id'], 'goods' => $good_detail];
            Gateway::sendToGroup($room_id, self::genMsg('switchAddGoods', '加入成功', $data));
            return;
        }
    }

    /**
     * 讲解商品
     * @param array $params
     */
    public static function sayGoods(array $params)
    {
        global $db, $redis;
        $room_id = $params['room_id'];
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $live_status = $params['live_status'] ? $params['live_status'] : 0;
        $goods_type = isset($params['goods_type']) ? $params['goods_type'] : 0;

        $say_goods_sql = "select * from " . TABLE_PREFIX . "live_goods WHERE goods_id={$goods_id} and user_id={$user_id} and room_id={$room_id} and goods_type={$goods_type} and live_status>-1 LIMIT 1";
        $say_goods_result = $db->query($say_goods_sql);
        if (empty($say_goods_result)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该商品还未直播~', [], 1));
            return;
        }
        if ($say_goods_result[0]['live_status'] == $live_status) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该商品正在讲解中~', [], 1));
            return;
        }

        $has_say_goods_sql = "select * from " . TABLE_PREFIX . "live_goods WHERE  user_id={$user_id} and room_id={$room_id} and live_status={$live_status} LIMIT 1";
        $has_say_goods_result = $db->query($has_say_goods_sql);
        if (!empty($has_say_goods_result)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '你有其他商品正在讲解中~', [], 1));
            return;
        }

        $res = $db->update(TABLE_PREFIX . 'live_goods')->cols(['live_status' => $live_status])->where("id=" . $say_goods_result[0]['id'])->query();
        if (!$res) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '设置错误,请重试~', [], 1));
            return;
        }

        //表示属于哪个商品库中的
        if (empty($goods_type)) {
            $goods = $db->query("select id as goods_id,title,shop_type,img,short_title,price,discount_price,has_coupon,coupon_price,shop_name,status from " . TABLE_PREFIX . "goods WHERE id={$goods_id}  LIMIT 1");
        }

        if ($goods_type == 1) {
            //查自营
            $goods = $db->query("select goods_id, goods_name as title,sku_image as img, discount_price as discount_price,goods_state as status,promotion_type,start_time,end_time  from " . TABLE_PREFIX . "shop_goods_sku WHERE sku_id={$goods_id}  LIMIT 1");
        }
        $promotion_type = isset($goods[0]['promotion_type']) ? $goods[0]['promotion_type'] : 0;
        $addtime = time();
        $detail = [
            'discount_price' => $goods[0]['discount_price'], 'coupon_price' => isset($goods[0]['coupon_price']) ? $goods[0]['coupon_price'] : 0, 'img' => ($goods[0]['img'] ? $goods[0]['img'] : ''),
            'shop_type' => isset($goods[0]['shop_type']) ? $goods[0]['shop_type'] : 'Z', 'title' => $goods[0]['title']
        ];
        $now = time();
        $activty = [
            'promotion_type' => isset($goods[0]['promotion_type']) ? $goods[0]['promotion_type'] : 0,
            'start_time' => isset($goods[0]['start_time']) ? ($goods[0]['start_time'] - $now > 0 ? $goods[0]['start_time'] - $now : 0) : 0,
            'end_time' => isset($goods[0]['end_time']) ? ($goods[0]['end_time'] - $now > 0 ? $goods[0]['end_time'] - $now : 0) : 0
        ];
        $good_detail = ['detail' => $detail, 'goods_type' => $goods_type, 'goods_id' => $goods_id, 'top_time' => $say_goods_result[0]['top_time'],
            'id' => $say_goods_result[0]['anchor_id'], 'add_time' => $addtime, 'content' => ($say_goods_result[0]['content'] ? $say_goods_result[0]['content'] : ''), 'goods_activty' => $activty];

        $data = ['live_status' => $live_status, 'room_id' => $params['room_id'], 'goods_id' => $goods_id, 'id' => $say_goods_result[0]['id'], 'goods' => $good_detail];
        Gateway::sendToGroup($room_id, self::genMsg('switchSayGoods', '讲解设置成功', $data));
    }

    /**
     * 取消讲解
     */
    public static function cancelSayGoods(array $params)
    {
        global $db;
        $room_id = $params['room_id'];
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $goods_type = isset($params['goods_type']) ? $params['goods_type'] : 0;

        $say_goods_sql = "select * from " . TABLE_PREFIX . "live_goods WHERE goods_id={$goods_id} and user_id={$user_id} and room_id={$room_id} and goods_type={$goods_type} and live_status>-1 LIMIT 1";
        $say_goods_result = $db->query($say_goods_sql);
        if (empty($say_goods_result)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该商品还未直播~', [], 1));
            return;
        }
        if ($say_goods_result[0]['live_status'] != 1) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该商品不在讲解中~', [], 1));
            return;
        }

        $res = $db->update(TABLE_PREFIX . 'live_goods')->cols(['live_status' => 0])->where("id=" . $say_goods_result[0]['id'])->query();
        if (!$res) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '设置错误,请重试~', [], 1));
            return;
        }
        $data = ['live_status' => 2, 'room_id' => $params['room_id'], 'goods_id' => $goods_id, 'id' => $say_goods_result[0]['id']];
        Gateway::sendToGroup($room_id, self::genMsg('switchCancelSayGoods', '讲解成功取消', $data));
    }

    /**
     * 设置卖点
     * @param array $params
     */
    public static function sellGoods(array $params)
    {
        global $db, $redis;
        $room_id = $params['room_id'];
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $goods_type = isset($params['goods_type']) ? $params['goods_type'] : 0;
        $content = isset($params['content']) ? $params['content'] : '';
        $say_goods_sql = "select * from " . TABLE_PREFIX . "live_goods WHERE goods_id={$goods_id} and user_id={$user_id} and room_id={$room_id} and goods_type={$goods_type} and live_status>-1 LIMIT 1";
        $say_goods_result = $db->query($say_goods_sql);
        if (empty($say_goods_result)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该商品还未直播~', [], 1));
            return;
        }

        if (!empty($content) && mb_strlen($content, 'UTF8') > 50) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '卖点字数过多', [], 1));
            return;
        }

        $res = $db->update(TABLE_PREFIX . 'live_goods')->cols(['content' => $content])->where("id=" . $say_goods_result[0]['id'])->query();
        if (!$res) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '设置错误,请重试~', [], 1));
            return;
        }
        $data = ['live_status' => 3, 'room_id' => $params['room_id'], 'goods_id' => $goods_id, 'goods_content' => $content, 'id' => $say_goods_result[0]['id']];
        Gateway::sendToGroup($room_id, self::genMsg('switchSellGoods', '卖点设置成功', $data));
    }

    /**
     * 移除直播商品
     * @param array $params
     */
    public static function delGoods(array $params)
    {
        global $db, $redis;
        $room_id = $params['room_id'];
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $live_status = $params['live_status'] ? $params['live_status'] : 0;

        if (empty($goods_id) || empty($goods_id) || empty($user_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '非法操作', [], 1));
        }

        $res = $db->update(TABLE_PREFIX . 'live_goods')->cols(['live_status' => $live_status])->where("goods_id IN(" . $goods_id . ")")->query();
        if (!$res) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '删除失败,请重试~', [], 1));
            return;
        }

        $goods = [];
        $goods_array_id = explode(',', $goods_id);
        foreach ($goods_array_id as $key => $value) {
            $goods[] = ['goods_id' => $value];
        }
        $data = ['live_status' => $live_status, 'room_id' => $params['room_id'], 'goods' => $goods];
        Gateway::sendToGroup($room_id, self::genMsg('switchDelGoods', '移除成功', $data));
    }

    /**
     * 置顶商品
     */
    public static function topGoods(array $params)
    {
        global $db, $redis;
        $room_id = $params['room_id'];
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $live_status = $params['live_status'] ? $params['live_status'] : 0;
        if (empty($goods_id) || empty($goods_id) || empty($user_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '非法操作', [], 1));
        }

        $res = $db->update(TABLE_PREFIX . 'live_goods')->cols(['top_time' => time()])->where("goods_id IN(" . $goods_id . ")")->query();
        if (!$res) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '置顶失败,请重试~', [], 1));
            return;
        }

        $goods = [];
        $goods_array_id = explode(',', $goods_id);
        foreach ($goods_array_id as $key => $value) {
            $goods[] = ['goods_id' => $value];
        }
        $data = ['live_status' => $live_status, 'room_id' => $params['room_id'], 'goods' => $goods];
        Gateway::sendToGroup($room_id, self::genMsg('switchTopGoods', '置顶成功', $data));
    }
}