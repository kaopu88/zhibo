<?php

namespace app\service;

use app\Common;
use app\service\payment\GiveGift;
use \GatewayWorker\Lib\Gateway;


class Gift extends Common
{
    // 用户赠送礼物
    public static function useGift(array $params)
    {
        global $redis, $config;

        if(!isset($params['room_id']) || $params['user_id'] != $_SESSION['user_id'] || $params['gift_amount'] == 0)
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '数据错误~4', [], 1));
            return;
        }

        $user_info = User::getUser($params['user_id']);

        if (empty($user_info))
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '登录异常~', [], 1));
            return;
        }
        if (!isset($params['gift_id'])) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '数据错误哦~', [], 1));
            return;
        }
        $gift_info = MilletTools::getGift($params['gift_id']);

        if (empty($gift_info)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '礼物不存在~', [], 1));
            return;
        }

        if ($gift_info['is_vip'] == 1) {
            if ($user_info['vip_expire'] < time()) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '该礼物会员专享~', [], 1));
                return;
            }
        }

        $params['sendCost'] = $gift_info['price'] * $gift_info['discount'] * $params['gift_amount'];

        if($user_info['bean'] < $params['sendCost'])
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，余额不足~', [], 1005));
            return;
        }

        //获取主播ID
        $params['anchor_id'] = MilletTools::getAnchorIdByRoomId($params['room_id']);

        //获取主播信息
        $anchor_info = self::getUserBasicInfo($params['anchor_id']);

        //处理守护
        $gift_ids = json_decode($redis->get('BG_GIFT:guard_all'), true);
        if (!empty($gift_ids) && in_array($params['gift_id'], $gift_ids))
        {
            if (self::checkGuard($params['anchor_id'], $params['user_id']))
            {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，守护席位已满。', [], 1));
                return;
            }
        }

        /**
         * 直播间赠送礼物
         *
         */
        $gift_rs = GiveGift::payment([
            'user_id' => $params['user_id'],
            'to_uid' => $params['anchor_id'],
            'gift_id' => $params['gift_id'],
            'num' => $params['gift_amount'],
            'room_id' => $params['room_id']
        ]);

        if($gift_rs !== true)
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', GiveGift::getMessage(), [], 1));
            return;
        }
        $gift_ids = json_decode($redis->get('BG_GIFT:guard_all'), true);
        if (!empty($gift_ids) && in_array($params['gift_id'], $gift_ids)) self::addGuard($params['anchor_id'], $params['user_id'], $params['gift_id'], $params['room_id']);

        //累加收益
        $redis->incrby(self::$livePrefix.$params['room_id'].':incomeTotal', $params['sendCost']);

        //更新pk数据
        $pk_info = self::giftPkUpdate($params, $params['sendCost']);

        if ($pk_info !== true && !empty($pk_info) && in_array($params['gift_id'], [208]) && $pk_info['pk_type'] == 'pk_rank')
        {
            $send_room_id = $pk_info['active_room_id'] == $params['room_id'] ? $pk_info['target_room_id'] : $pk_info['active_room_id'];

            $other_anchor_id = $pk_info['active_id'] == $anchor_info['user_id'] ? $pk_info['target_id'] : $pk_info['active_id'];

            $other_anchor_info = self::getUserBasicInfo($other_anchor_id);

            //发送送礼物信息到指定直播间
            self::sendGiftMsgToRoom($send_room_id, $gift_info, $user_info, $other_anchor_info);

            $redis->set('activity:pk_rank:gift_effects:'.$params['room_id'], $params['user_id']);
            $redis->expire('activity:pk_rank:gift_effects:'.$params['room_id'], 20);
            $redis->set('activity:pk_rank:gift_effects:'.$send_room_id, $params['user_id']);
            $redis->expire('activity:pk_rank:gift_effects:'.$send_room_id, 20);
        }

        $dayNum = "anchor_millet:d:".date('Ymd');
        $rank = $redis->zrevrank($dayNum, $params['anchor_id']);
        if ($rank !== false) {
            $rank = $rank + 1;
            Gateway::sendToGroup($params['room_id'], self::genMsg('showRank', 'ok', ['rank' => $rank]));
        }

        //推送账户余额信息
        Gateway::sendToUid($params['user_id'], self::genMsg('cuckooInfo', '', ['cuckoo'=>$user_info['bean'] - $params['sendCost']]));

        //发送送礼物相关信息
        self::sendGiftMsg($params['room_id'], $gift_info, $user_info, $anchor_info);

        Monitor::listen('send_gift_after', $params);
    }



    private static function checkGuard($anchor_id, $user_id)
    {
        global $redis, $config;

        $guardCount = $redis->zcard($config['guard']['redis_key'].$anchor_id); //当前主播的守护量

        $scoreUid = $redis->zscore($config['guard']['redis_key'].$anchor_id, $user_id); //当前用户是否已送过

        return $guardCount > $config['guard']['max_seat'] && empty($scoreUid);
    }


    private static function addGuard($anchor_id, $user_id, $gift_id, $room_id = '')
    {
        global $redis, $config, $db;

        $scoreUid = $redis->zscore($config['guard']['redis_key'].$anchor_id, $user_id); //当前用户是否已送过

        $res = $db->query('SELECT id, isguard, guard_day FROM `' . TABLE_PREFIX . 'gift` WHERE  `status` = \'1\'  AND `delete_time` IS NULL  AND `id` = ' . $gift_id . ' LIMIT 1');

        if ($res[0]['isguard'] !=1) return false;

        $time = $res[0]['guard_day'] * 86400;

        $guard_time = !empty($scoreUid) ? $time + $scoreUid : $time + time();
        /*if ($gift_id == 131) {
            $guard_time = !empty($scoreUid) ? $config['guard']['seven_time']+$scoreUid : $config['guard']['seven_time']+time();
        }
        else {
            $guard_time = !empty($scoreUid) ? $config['guard']['month_time']+$scoreUid : $config['guard']['month_time']+time();
        }*/

        $redis->zadd($config['guard']['redis_key'].$anchor_id, $guard_time, $user_id);

        $top = $redis->zrevrange($config['guard']['redis_key'].$anchor_id, 0, 2); //获取第一个用户id

        if (!empty($top)) {
            $default = [];
            for ($i= 0; $i < 3; $i++) {
                if (empty($top[$i])) continue;
                $topUser = self::getUserBasicInfo($top[$i]);
                if (!empty($topUser)) {
                    if (!empty($topUser['avatar'])) {
                        $default[$i]['guard_avatar'] = $topUser['avatar'] ? $topUser['avatar'] : '';
                        $default[$i]['guard_uid'] = $topUser['user_id'] ? $topUser['user_id'] : '';
                        $default[$i]['guard_show'] = 1;
                    }
                }
            }
            Gateway::sendToGroup($room_id, self::genMsg('guardlist', 'ok', $default));
        }

        return true;
    }



}