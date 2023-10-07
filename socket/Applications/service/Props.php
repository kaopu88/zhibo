<?php

namespace app\service;


use app\Common;
use app\service\payment\BagGift;
use \GatewayWorker\Lib\Gateway;


class Props extends Common
{
    protected static $props_type = [
        '0' => 'gift',
    ];

    protected static $access_method = ['lottery']; //表示不计算收益的背包礼物

    //使用背包道具
    public static function useGift(array $params)
    {
        global $db, $config;

        $now = time();

        $sql = 'SELECT id, use_time, expire_time, access_method FROM '.TABLE_PREFIX.'user_package WHERE status=1 AND user_id='.$params['user_id'].' AND gift_id='.$params['gift_id'].' ORDER BY create_time desc LIMIT 1';

        $props_info = $db->query($sql);

        if (empty($props_info))
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '道具已使用或已过期~', [], 1));
            return;
        }

        if ($now < $props_info[0]['use_time'])
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '道具未到生效时间~', [], 1));
            return;
        }

        if (!empty($props_info[0]['expire_time']))
        {
            if ($now > $props_info[0]['expire_time'])
            {
                //将道具置为失效状态
                $db->update(TABLE_PREFIX.'user_props')->cols(['status'=>0])->where('id=' . $props_info[0]['id'])->query();

                //递归拿取下一个
                self::useGift($params);
            }
        }

        //判断礼物来源
        $params['is_prifit'] = 0;
        $params['gift_from'] = $props_info[0]['access_method'];
        if (in_array($props_info[0]['access_method'], self::$access_method) && ($config['setting']['bag_prifit_status'] == 0)) {
            $params['is_prifit'] = 1; //是否计算收益 1表示不计算收益了
        }

        $static_method = 'use'.ucfirst(self::$props_type[0]).'Props';

        if (class_exists(__CLASS__, $static_method))
        {
            $params['props_id'] = $props_info[0]['id'];
            call_user_func_array([__CLASS__, $static_method], [$params]);
        }
    }



    //礼物类道具
    protected static function useGiftProps(array $params)
    {
        global $redis;

        //获取用户信息
        $user_info = self::getUserBasicInfo($params['user_id']);

        //获取礼物信息
        $gift_info = MilletTools::getGift($params['gift_id']);

        //获取主播信息
        $anchor_id = MilletTools::getAnchorIdByRoomId($params['room_id']);

        //获取主播信息
        $anchor_info = self::getUserBasicInfo($anchor_id);

        self::completeUserPropsUse($params['props_id']);

        $sendCost = $gift_info['price'] * $gift_info['discount'] * $params['gift_amount'];

        /**
         * 背包送礼物
         *
         */
        $gift_rs = BagGift::payment([
            'user_id' => $params['user_id'],
            'to_uid' => $anchor_id,
            'gift_id' => $params['gift_id'],
            'num' => $params['gift_amount'],
            'gift_from' => $params['gift_from'],
            'is_prifit' => $params['is_prifit'],
        ]);

        if ($gift_rs !== true)
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '系统繁忙~', [], 1));
            self::completeUserPropsUse($params['props_id'], 1);
            return;
        }

        //累加收益
        $redis->incrby(self::$livePrefix.$params['room_id'].':incomeTotal', $sendCost);

        //更新pk数据
        self::giftPkUpdate($params, $sendCost);

        self::sendGiftMsg($params['room_id'], $gift_info, $user_info, $anchor_info);

        Monitor::listen('send_props_after', $params);
    }


    //虚似况换类道具
    protected static function useVirtualProps(array $params)
    {

    }


    //特效类道具
    protected static function useEffectProps(array $params)
    {

    }


    //实物类道具
    protected static function useMaterialProps(array $params)
    {

    }

    protected static function completeUserPropsUse($props_id, $status = 2)
    {
        global $db;

        //将道具置为已使用状态
        $db->update(TABLE_PREFIX.'user_package')->cols(['status' => $status])->where('id=' . $props_id)->query();
    }

}