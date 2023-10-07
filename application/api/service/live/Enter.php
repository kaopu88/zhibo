<?php


namespace app\api\service\live;


use app\api\service\Goods;
use app\api\service\LiveBase2;
use app\core\service\User as UserService;
use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use bxkj_module\service\Bean;

class Enter extends LiveBase2
{
    protected $anchor = '';
    protected $guard = [];
    protected $activty_goods = '';
    protected $say_goods = '';

    //初始化客户端数据
    public function initializeClient($room)
    {
        $this->anchor = $room['user_id'];
        $Guard = new Guard();
        $userModel = new UserService();
        $anchor = $userModel->getUser($room['user_id'], USERID);

        $Live_config = get_live_config(); //直播配置信息
        $pk_status = $this->checkPk($room['user_id']);

        if ($pk_status['is_pk']) {
            $target = $this->getPkData($room['user_id'], $room['room_id'], $pk_status['flag']);

            if (!empty($target)) {
                $our_guard = $Guard->getGuard($room['user_id']); //守护
                $target_user_info = $userModel->getUser($target['user_id'], USERID);
                $target_guard = $Guard->getGuard($target['user_id']); //守护
                $smoke_user_id = $this->redis->get('activity:pk_rank:gift_effects:' . $room['room_id']);
                $smoke_time = $this->redis->ttl('activity:pk_rank:gift_effects:' . $room['room_id']);

                if ($pk_status['flag'] == 'active') {
                    $active_info = [
                        'user_id' => $room['user_id'],
                        'room_id' => $room['room_id'],
                        'is_follow' => $anchor['is_follow'],
                        'nickname' => $anchor['nickname'],
                        'avatar' => $anchor['avatar'],
                        'pull_url' => $room['pull'],
                        'guard_list' => array_slice($our_guard, 0, 3)
                    ];

                    $target_info = [
                        'user_id' => $target['user_id'],
                        'room_id' => $target['pk_room_id'],
                        'is_follow' => $target_user_info['is_follow'],
                        'avatar' => $target_user_info['avatar'],
                        'nickname' => $target_user_info['nickname'],
                        'pull_url' => $target['pull'],
                        'guard_list' => array_slice($target_guard, 0, 3)
                    ];
                } else {

                    $target_info = [
                        'user_id' => $room['user_id'],
                        'nickname' => $anchor['nickname'],
                        'is_follow' => $anchor['is_follow'],
                        'room_id' => $room['room_id'],
                        'avatar' => $anchor['avatar'],
                        'pull_url' => $room['pull'],
                        'guard_list' => array_slice($our_guard, 0, 3)

                    ];

                    $active_info = [
                        'user_id' => $target['user_id'],
                        'room_id' => $target['pk_room_id'],
                        'is_follow' => $target_user_info['is_follow'],
                        'avatar' => $target_user_info['avatar'],
                        'nickname' => $target_user_info['nickname'],
                        'pull_url' => $target['pull'],
                        'guard_list' => array_slice($target_guard, 0, 3)
                    ];
                }

                $active_user = $this->redis->zRevRange('BG_LIVE:pk_rank_' . $target['pk_id'] . '_' . $active_info['room_id'], 0, 3, true);
                $target_user = $this->redis->zRevRange('BG_LIVE:pk_rank_' . $target['pk_id'] . '_' . $target_info['room_id'], 0, 3, true);
                $active_user_detail = [];
                $target_user_detail = [];
                if (!empty($active_user)) {
                    foreach ($active_user as $user_id => $user_score) {
                        $user_info = $this->getUserBasicInfo($user_id);
                        if (empty($user_info)) continue;
                        $active_user_detail[] = [
                            'coin' => $user_score,
                            'user_id' => $user_id,
                            'avatar' => $user_info['avatar'],
                            'nickname' => $user_info['nickname']
                        ];
                    }
                }
                if (!empty($target_user)) {
                    foreach ($target_user as $user_id => $user_score) {
                        $user_info = $this->getUserBasicInfo($user_id);
                        if (empty($user_info)) continue;
                        $target_user_detail[] = [
                            'coin' => $user_score,
                            'user_id' => $user_id,
                            'avatar' => $user_info['avatar'],
                            'nickname' => $user_info['nickname']
                        ];
                    }
                }

                $room['pk_data'] = [
                    'pk_id' => $target['pk_id'],
                    'pk_type' => $target['pk_type'],
                    'energy' => $target['energy'],
                    'active_info' => $active_info,
                    'target_info' => $target_info,
                ];

                $room['pk_energy'] = [
                    'active_energy' => $target['active_energy'],
                    'target_energy' => $target['target_energy'],
                    'active_rank' => $active_user_detail,
                    'target_rank' => $target_user_detail,
                    'active_info' => ['user_id' => $room['pk_data']['active_info']['user_id']],
                    'energy' => $target['energy'],
                ];

                $room['is_pk'] = (int)$pk_status['is_pk'];
                $smoke_user_id && $smoke_user_id != USERID && $room['smoke_data'] = ['user_id' => $smoke_user_id, 'time' => $smoke_time];
                $room['pk_time'] = (string)$target['pk_time'];
            }
        }

        $params = array_merge($Live_config, $anchor, $room);
        $pkSevice = new Pk();
        $roomAudiences = $pkSevice->getNumber($room['id']);
        $params['audience'] = $roomAudiences;
        $bean = new Bean();
        $bean = $bean->getInfo(USERID);

        // $weekNum = "anchor_millet:w:".DateTools::getWeekNum();
        $dayNum = "anchor_millet:d:" . date('Ymd');
        $params['total_millet'] = $this->redis->zScore($dayNum, $room['user_id']);
        $rank = $this->redis->zRevRank($dayNum, $room['user_id']);
        if ($rank !== false) $params['rank'] = $rank + 1;
        $params['act_balance'] = $bean['bean']; //用户余额
        $guard = $Guard->guardAvatar($room['user_id']); //主播守护
        $Activity = new Activity();
        $Activity->getLiveActivity(['live_guard' => $guard]); //初始化活动数据
        $this->guard = $Guard->getThreeAvatar($room['user_id']); //主播守护
        $this->initializeAnchorData($params); //初始化主播数据
        $this->initializeUserData($params); //初始化用户数据
        $this->initializeRoomData($params); //初始化房间数据

        switch ($room['room_model']) {
            case self::FILM_MODE :
                $this->initializeFilmData($room['id'], $room['create_time']);
                break;
            case self::RECORD_MODE :
                break;
            case self::GAME_MODE :
                break;
        }
        return $this;
    }

    protected function linkMic()
    {
        $LinkMic = new LinkMic();

        $rs = $LinkMic->getEnterRoomLinkMic($this->room_data['room_id']);

        $this->room_data['link_mic_data'] = empty($rs) ? [] : $rs;
    }

    //获取客户端数据
    public function getClientParams()
    {
        $this->linkMic();

        $message_key = self::$livePrefix . $this->room_data['room_id'] . ':message';
        $message = []; //$this->redis->lrange($message_key, 0, -1);

        if (!empty($message)) {
            foreach ($message as &$value) {
                $value = json_decode(hexTobin($value), true);
            }
        } else {
            $message = [];
        }

        $data = [
            'room' => $this->room_data,
            'anchor' => $this->anchor_data,
            'user' => $this->user_data,
            'activity' => self::$activity_data,
            'new_activity' => $this->guard,
            'activty_goods' => $this->getActivtyGoods(['promotion_type' => 1, 'room_id' => $this->room_data['room_id']]) ?: (object)[],
            'say_goods' => $this->getActivtyGoods(['live_status' => 1, 'room_id' => $this->room_data['room_id']]) ?: (object)[],
            'coupon' => (new \app\api\service\Goods())->getCoupon($this->anchor_data['user_id'], USERID) ?: (object)[],
            'film' => $this->film_data,
            'voice' => $this->voice_data,
            'message' => $message,
        ];

        return $data;
    }

    //获取是否有限时抢购或者讲解商品
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

    /**
     * 验证直播间
     */
    public function verifyClient($room_id)
    {
        if ($this->redis->zscore('blacklist:' . $room_id, USERID)) return make_error('请求错误,无法进入直播间');//黑名单
        if ($this->redis->sismember(self::$livePrefix . $room_id . self::$kickingKey, USERID) == 1) return make_error('您已被踢出直播间,无法再进入');

        //return make_error('您的等级不够，不能进入');
        $room = $this->getRoomOne($room_id);

        if (empty($room)) return make_error('直播已结束');
        if (USERID == $room['user_id']) return make_error('您不可以进入自已的直播间');

        switch ($room['type']) {
            case self::PRIVATE_TYPE:
                $res = ['type' => $room['type'], 'tips' => self::$room_type_msg[$room['type'] - 1], 'mode' => $room['room_model']]; //私密
                $is_pass = $this->verifyPassword($room_id); //有无输入过密码
                $is_pass && $res['type'] = (string)self::NORMAL_TYPE; //有则通过
                $res['tips'] = parse_tpl($res['tips'], ['mode' => self::$room_mode[$room['room_model']]]);
                $res['money'] = "0";
                break;
            case self::CHARGE_TYPE:
                $res = ['type' => $room['type'], 'tips' => self::$room_type_msg[$room['type'] - 1], 'mode' => $room['room_model']]; //付费
                $is_pay = $this->verifyPay($room['id']); //有无付费
                $is_pay && $res['type'] = (string)self::NORMAL_TYPE; //有则通过
                $res['tips'] = parse_tpl($res['tips'], ['value' => $room['type_val'], 'mode' => self::$room_mode[$room['room_model']]]);
                $res['money'] = $room['type_val'];
                break;

            case self::TIME_CHARGE_TYPE:
                $res = ['type' => $room['type'], 'tips' => self::$room_type_msg[$room['type'] - 1], 'mode' => $room['room_model']]; //计费
                $res['tips'] = parse_tpl($res['tips'], ['value' => $room['type_val'], 'mode' => self::$room_mode[$room['room_model']]]);
                $res['money'] = $room['type_val'];
                break;

            case self::VIP_TYPE:
                $res = ['type' => $room['type'], 'tips' => self::$room_type_msg[$room['type'] - 1], 'mode' => $room['room_model']]; //VIP
                $is_vip = $this->verifyVip(USERID);
                $is_vip && $res['type'] = (string)self::NORMAL_TYPE; //vip直接通过
                $res['tips'] = parse_tpl($res['tips'], ['value' => self::$room_mode[$room['room_model']], 'mode' => self::$room_mode[$room['room_model']]]);
                $res['money'] = "0";
                break;

            case self::LEVEL_TYPE:
                $res = ['type' => $room['type'], 'tips' => self::$room_type_msg[$room['type'] - 1], 'mode' => $room['room_model']]; //等级
                $is_level = $this->verifyLevel(USERID, $room['type_val']);
                $is_level && $res['type'] = (string)self::NORMAL_TYPE; //等级够了直接通过
                $res['tips'] = parse_tpl($res['tips'], ['value' => $room['type_val'], 'mode' => self::$room_mode[$room['room_model']]]);
                $res['money'] = "0";
                break;

            default:
                $res = ['type' => $room['type'], 'tips' => '', 'mode' => $room['room_model'],'money'=>"0"];
                break;
        }

        return $res;
    }

    //获取用户基础信息
    public function getUserBasicInfo($user_id)
    {
        $users = $this->redis->get('user:' . $user_id);
        if (empty($users)) return [];

        $users = json_decode($users, true);
        $users['vip_status'] = $users['vip_expire'] > time() ? 1 : 0;

        return $users;
    }
}