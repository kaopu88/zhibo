<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/27 0027
 * Time: 上午 11:34
 */
namespace app\service;

class LiveGoodsRedis
{
    protected static $redisFields = 'user_id,goods_id,type,live_status,room_id,update_time,status';

    /**
     * @param $liveId 直播商品的id
     * @param $roomId 直播放假id
     * @param $update 更新的数据
     * @return bool
     */
    public static function updateData($liveId, $roomId, $update)
    {
        global $redis;
        if (!is_array($update)) $update = [];
        $updateData = [];
        $fields = str_to_fields(self::$redisFields);
        foreach ($fields as $field) {
            if (is_array($update) && isset($update[$field])) {
                $updateData[$field] = $update[$field];
            }
        }
        if (!empty($updateData)) {
            $key = "livegoods:{$roomId}:{$liveId}";
            $json = $redis->get($key);
            $liveGoods = $json ? json_decode($json, true) : false;
            if (empty($liveGoods) || empty($liveGoods['user_id'])) return false;//没有有效的缓存则不需要更新
            foreach ($updateData as $fk => $value) {

            }
            $setRes = $redis->set($key, json_encode(array_merge($liveGoods, $updateData)));
            return $setRes;
        }

        return true;
    }
}