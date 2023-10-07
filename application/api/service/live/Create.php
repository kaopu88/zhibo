<?php

namespace app\api\service\live;

use app\api\service\Goods;
use bxkj_module\exception\ApiException;
use app\api\service\LiveBase2;
use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use bxkj_common\RabbitMqChannel;
use bxkj_common\HttpClient;
use bxkj_module\service\Bean;
use think\Db;

class Create extends LiveBase2
{
    protected $guard = [];

    //初始化开播参数
    public function initializeService($params)
    {
        $anchorLocation = $this->redis->get(self::$livePrefix . self::$liveLocation . USERID);
        if (!empty($anchorLocation)) {
            $params['lng'] = 0;
            $params['lat'] = 0;
            $location = json_decode($anchorLocation, true);
            $params['lng'] = $location['lng'];
            $params['lat'] = $location['lat'];
        }

        $address = get_position_Lng_lat($params['lng'], $params['lat'], 'base'); //位置
        $address = empty($address)
            ? ['province' => $params['province_name'], 'city' => $params['city_name'], 'district' => $params['district_name']]
            : ['province' => $address['regeocode']['addressComponent']['province'], 'city' => $address['regeocode']['addressComponent']['city'], 'district' => $address['regeocode']['addressComponent']['district']];

        $Guard = new Guard();
        //$bean = (new CoreSdk())->post('bean/get_info', ['user_id' => USERID]); //余额
        // $weekNum = "anchor_millet:w:".DateTools::getWeekNum();
        $bean = new Bean();
        $bean = $bean->getInfo(USERID);

        $dayNum = "anchor_millet:d:" . date('Ymd');
        $displayMillet = $this->redis->zScore($dayNum, USERID);
        $live_config = get_live_config(); //直播配置
        $beginParams = array_merge($params, $live_config, $address, ['total_millet' => $displayMillet, 'act_balance' => $bean['bean']]);
        $beginParams['create_time'] = time();
        $beginParams['lng'] = $params['lng'];
        $beginParams['lat'] = $params['lat'];
        $stream_suffix = USERID . '_' . $beginParams['create_time'];

        if (empty($beginParams['cover_url']) && config('app.live_setting.avatar_set_cover')) {
            $beginParams['cover_url'] = img_url($beginParams['avatar'], 'live', 'film_cover');
        }


        $class = '\\bxkj_live\\drive\\' . ucfirst($live_config['service_platform']);
        if (!class_exists($class)) throw new ApiException('创建房间错误[2]');
        $LiveDrive = new $class($live_config);
        $beginParams['push'] = call_user_func_array([$LiveDrive, 'buildPushUrl'], [$stream_suffix]); //生成主播端推流地址
        $beginParams['pull'] = call_user_func_array([$LiveDrive, 'buildPullUrl'], [$live_config['pull_protocol'], $stream_suffix]); //生成客户端播流地址
        $beginParams['stream'] = $live_config['stream_prefix'] . '_' . $stream_suffix;
        if (empty($beginParams['cover_url']) && !config('app.live_setting.avatar_set_cover')) {
            $beginParams['cover_url'] = call_user_func_array([$LiveDrive, 'buildSnapshot'], [$stream_suffix]);
        }


        $tmpWhere = ['user_id' => USERID];
        $rel = Db::name('anchor')->where($tmpWhere)->find();
        $beginParams['agent_id'] = !empty($rel) ? $rel['agent_id'] : 0;
        $beginParams['voice_number'] = isset($params['voice_value']) ? (int)$params['voice_value'] : 1;
        $guard = $Guard->guardAvatar(USERID); //守护
        $Activity = new Activity();
        $Activity->getLiveActivity(['live_guard' => $guard], 'service');
        $this->guard = $Guard->getThreeAvatar(USERID); //主播守护
        $this->initializeRoomData($beginParams);
        $this->initializeAnchorData($beginParams);
        $this->initializeStorageData($beginParams);

        $unique = ['user_id' => USERID];
        array_shift($this->storage_data);
        $update = $this->storage_data;
        $live_info = Db::name('live')->where($unique)->find();

        if (empty($live_info)) {
            $this->storage_data['user_id'] = USERID;
            $this->room_data['room_id'] = Db::name('live')->insertGetId($this->storage_data);
        } else {
            $this->room_data['room_id'] = $live_info['id'];
            $update["create_time"] = $live_info["create_time"];
            Db::name('live')->where($unique)->update($update);
        }

        $this->redis->sadd(self::$livePrefix . 'Living', USERID);
        $this->redis->set(self::$voice_prefix . 'anchor_user:' . $this->room_data['room_id'], USERID);
        //判断是否有参与,没有则新增
        $key = "coverstar:m:" . date('Ym');
        $coverstar = $this->redis->Zscore($key, USERID);
        if ($coverstar === false) {
            $this->redis->zincrby($key, 0, USERID);
        }

        return $this;
    }

    //分配僵尸粉
    public function addServiceRobot()
    {
        $zombie = config('app.live_setting.robot');
        $sum = mt_rand($zombie['min'], $zombie['max']);
        $Http = new HttpClient();
        $Http->curl(CORE_URL . '/zombie/zombieProcess', 'post', ['room_id' => $this->room_data['room_id'], 'count' => $sum], 0.5);
        return $this;
    }


    //分配任务
    public function addServiceTask()
    {
        $Core = new CoreSdk();
        $Core->post('task/generateTaskQuota', ['user_id' => USERID]);
        return $this;
    }


    //获取开播成功后数据
    public function getServiceParams()
    {
        return [
            'room' => $this->room_data,
            'anchor' => $this->anchor_data,
            'activity' => self::$activity_data,
            'new_activity' => $this->guard,
            'pk_conf' => $this->pkConf(),
            'goods' => $this->getGoods($this->room_data['room_id']),
            'activty_goods' => $this->getActivtyGoods(['promotion_type' => 1, 'room_id' => $this->room_data['room_id']]) ?: (object)[],
        ];
    }

    protected function getActivtyGoods(array $params)
    {
        $shop_open = config('taoke.user_shop') ? config('taoke.user_shop') : 0;
        if (empty($shop_open)) return false;
        $goodsSevice = new Goods();
        $good = $goodsSevice->getShopActivtyGoods($this->room_data['room_id'], $params);
        if (empty($good)) return false;
        $detail = [
            'discount_price' => $good['goodsdetail']['discount_price'], 'coupon_price' => isset($good['goodsdetail']['coupon_price']) ? $good['goodsdetail']['coupon_price'] : 0, 'img' => ($good['goodsdetail']['img'] ? $good['goodsdetail']['img'] : ''),
            'shop_type' => isset($goods['goodsdetail']['shop_type']) ? $goods['goodsdetail']['shop_type'] : 'Z', 'title' => $good['goodsdetail']['title']
        ];
        $now = time();
        $activty = [
            'promotion_type' => isset($good['goodsdetail']['promotion_type']) ? $good['goodsdetail']['promotion_type'] : 0,
            'start_time' => isset($good['goodsdetail']['start_time']) ? ($good['goodsdetail']['start_time'] - $now > 0 ? $good['goodsdetail']['start_time'] - $now : 0) : 0,
            'end_time' => isset($good['goodsdetail']['end_time']) ? ($good['goodsdetail']['end_time'] - $now > 0 ? $good['goodsdetail']['end_time'] - $now : 0) : 0
        ];
        $data = ['detail' => $detail, 'goods_type' => $good['live_good']['goods_type'], 'live_status' => $good['live_good']['live_status'], 'goods_id' => $good['live_good']['goods_id'], 'id' => $good['live_good']['id'], 'top_time' => $good['live_good']['top_time'],
            'add_time' => $good['live_good']['add_time'], 'content' => ($good['live_good']['content'] ? $good['live_good']['content'] : ''), 'goods_activty' => $activty
        ];
        return $data;
    }


    //推流确认
    public function ackLive($room_id)
    {
        $this->where['id'] = $room_id;

        $this->where['status'] = 0;

        $res = Db::name('live')->where($this->where)->update(['status' => 1]);

        if (!$res) return make_error('error', '直播确认失败', ['room_id' => $room_id, 'user_id' => USERID]);

        return $res;
    }


    public function joinAgainAck($room_id)
    {
        $res = Db::name('live')->where(['id' => $room_id])->find();
        return empty($res) ? false : true;
    }


    //推送开播信息
    public function pushAnchorLiveMsg($user_id, $room_id)
    {
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.live', [
            'behavior' => 'live',
            'data' => [
                'user_id' => $user_id,
                'room_id' => $room_id
            ]
        ]);
    }


    //pk配置
    protected function pkConf()
    {
        $pk_conf = [
            [
                'image' => 'https://static.cnibx.cn/pkbg_1.png',
                'width' => '698',
                'height' => '260',
                'pk_type' => 'rand',
                'title' => '全民PK',
                'desc' => '系统根据规则进行自动匹配成功后即开始PK',
            ],
            [
                'image' => 'https://static.cnibx.cn/pkbg_2.png',
                'width' => '698',
                'height' => '260',
                'pk_type' => 'friend',
                'title' => '好友PK',
                'desc' => ''
            ]
        ];

        $act_pk = [
            'image' => 'https://static.cnibx.cn/d53a21cfc7c0309a070d740ff372079b19724079.png',
            'width' => '698',
            'height' => '260',
            'pk_type' => 'pk_rank',
            'title' => APP_PREFIX_NAME . 'PK排位赛',
            'desc' => 'PK排位赛，系统将随机进行匹配'
        ];

        $pk_rank_config = getActConfig('pk_rank');

        if (!empty($pk_rank_config)) {
            $now = time();

            if ($now > $pk_rank_config['start_time'] && $now < $pk_rank_config['end_time']) {
                array_push($pk_conf, $act_pk);
            }
        }

        return $pk_conf;
    }

    //是否有直播商品
    protected function getGoods($room_id)
    {
        $goodsList = Db::name("live_pre_goods")->where(['user_id' => USERID])->order('live_status DESC,top_time DESC,add_time desc')->select();
        $good_detail = [];
        $dataAll = [];
        if (empty($goodsList)) return [];

        foreach ($goodsList as $value) {
            $goodstype = $value['goods_type'] ? 'shop' : 'taoke';
            $goodsKey = 'live_goods_pre:goods:goodsid:' . $goodstype . USERID;

            $dataAll[] = array(
                'user_id' => USERID,
                'goods_id' => $value['goods_id'],
                'anchor_id' => $value['anchor_id'],
                'goods_type' => $value['goods_type'],
                'promotion_type' => $value['promotion_type'],
                'site_id' => $value['site_id'],
                'live_status' => 0,
                'add_time' => $value['add_time'],
                'top_time' => $value['top_time'],
                'status' => 1,
                'content' => $value['content'] ?: '',
                'room_id' => $room_id
            );

            $goods = new Goods();
            if ($value['goods_type'] == 0) {
                //第三方商品
                $goodsdetail = $goods->getTaokeGoods(['goods_id' => $value['goods_id']], 1);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => $goodsdetail['coupon_price'], 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => $goodsdetail['shop_type'], 'title' => $goodsdetail['title']
                ];
            } else {
                //自营商品
                $goodsdetail = $goods->getShopGoods(['goods_id' => $value['goods_id']], 1);
                if (empty($goodsdetail)) continue;
                $detail = [
                    'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => isset($goodsdetail['coupon_price']) ? $goodsdetail['coupon_price'] : 0, 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                    'shop_type' => 'Z', 'title' => $goodsdetail['title']
                ];
            }
            $good_detail[] = ['detail' => $detail, 'goods_type' => $value['goods_type'], 'live_status' => $value['live_status'], 'goods_id' => $value['goods_id'], 'id' => $value['id'], 'top_time' => $value['top_time'],
                'add_time' => $value['add_time'], 'content' => ($value['content'] ? $value['content'] : '')];

            $this->redis->sRem($goodsKey, $value['goods_id']);
        }

        if (!empty($dataAll)) {
            Db::startTrans();
            Db::name('live_goods')->insertAll($dataAll);
            Db::name('live_pre_goods')->where(['user_id' => USERID])->delete();
            Db::commit();
        }

        $key = "livegoods:" . $room_id;
        $data = ['room_id' => $room_id, 'goods' => $good_detail];
        $this->redis->set($key, json_encode($data));
        return $good_detail;
    }
}