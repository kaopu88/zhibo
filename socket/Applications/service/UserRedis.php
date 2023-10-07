<?php

namespace app\service;



class UserRedis
{
    protected static $redisFields = "user_id,nickname,type,isvirtual,avatar,status,gender,birthday,province_id,city_id,district_id,
        phone,exp,level,verified,is_creation,promoter_uid,sign,vip_expire,millet,total_millet,fre_millet,
        millet_status,his_millet,like_num,fans_num,follow_num,collection_num,download_num,create_time,bean_id,commission_price,commission_pre_price,commission_total_price,
        bean,pay_total,total_bean,fre_bean,pay_status,cash_status,comment_push,like_push,follow_push,follow_new_push,
        recommend_push,follow_live_push,msg_push,rank_stealth,download_switch,autoplay_switch,bind_weibo,bind_qq,bind_weixin,isset_pwd,live_status,film_status,
        credit_score,agent_id,is_promoter,is_anchor,isvirtual_millet,his_isvirtual_millet,reg_meid,loss_bean";
    protected static $arrowArr = array('like_num', 'fans_num', 'follow_num', 'collection_num', 'download_num', 'exp', 'level', 'credit_score', 'isvirtual_millet', 'his_isvirtual_millet');

    public static function updateData($userId, $update)
    {
        global $redis;
        if (!is_array($update)) $update = [];
        if (isset($update['_credit_score'])) unset($update['_credit_score']);
        $updateData = [];
        $fields = str_to_fields(self::$redisFields);
        foreach ($fields as $field) {
            if (is_array($update) && isset($update[$field])) {
                $updateData[$field] = $update[$field];
            }
        }
        if (!empty($updateData)) {
            $key = "user:{$userId}";
            $json = $redis->get($key);
            $user = $json ? json_decode($json, true) : false;
            if (empty($user) || empty($user['user_id'])) return false;//没有有效的缓存则不需要更新
            if (isset($updateData['province_id'])) {
            }
            if (isset($updateData['city_id'])) {
            }
            if (isset($updateData['district_id'])) {
            }
            foreach ($updateData as $fk => $value) {
                $arr = self::$arrowArr;
                if (is_string($value) && in_array($fk, $arr)) {
                    if (preg_match('/^\+\d+/', $value)) {
                        $updateData[$fk] = $user[$fk] + (ltrim($value, '+'));
                    } elseif (preg_match('/^\-\d+/', $value)) {
                        $updateData[$fk] = $user[$fk] - (ltrim($value, '-'));
                    }
                }
            }
            $redis->hIncrBy("loginstate:{$user['user_id']}", 'update_v', 1);
            $setRes = $redis->set($key, json_encode(array_merge($user, $updateData)));
            return $setRes;
        }
        return true;
    }

}