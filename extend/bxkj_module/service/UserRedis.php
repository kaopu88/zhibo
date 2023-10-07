<?php

namespace bxkj_module\service;

use bxkj_common\RedisClient;
use think\Db;

class UserRedis extends Service
{
    public static $redisFields = "user_id,nickname,type,isvirtual,avatar,status,gender,birthday,province_id,city_id,district_id,
        phone,phone_code,exp,level,verified,is_creation,cover,sign,vip_expire,millet,total_millet,fre_millet,
        millet_status,his_millet,like_num,fans_num,film_num,follow_num,collection_num,download_num,create_time,bean_id,commission_price,commission_pre_price,commission_total_price,
        bean,loss_bean,pay_total,total_bean,fre_bean,pay_status,cash_status,comment_push,like_push,follow_push,follow_new_push,
        recommend_push,follow_live_push,msg_push,rank_stealth,download_switch,autoplay_switch,bind_weibo,bind_qq,bind_weixin,isset_pwd,live_status,film_status,comment_status,contact_status,
        credit_score,is_promoter,is_anchor,isvirtual_millet,his_isvirtual_millet,reg_meid,points,taoke_shop,teenager_model_open,shop_id,relation_id,special_id,pdd_pid,jd_pid,taoke_level,taoke_money,taoke_money_status,instance_id,bond,member_level,balance,score,weight,height,voice_sign,voice_time,cash,fenxiao_id,is_fenxiao,balance_money,order_num,balance_withdraw,balance_withdraw_apply,live_money,pay_password,live_withdraw_apply,goodnum";
    public static $arrowArr = ['like_num', 'fans_num', 'follow_num', 'collection_num', 'download_num', 'exp', 'level', 'credit_score', 'isvirtual_millet', 'his_isvirtual_millet', 'film_num', 'be_black_num', 'like_num2', 'points', 'taoke_level', 'taoke_money_status', 'relation_id', 'special_id', 'pdd_pid', 'jd_pid'];

    public static function updateData($userId, $update)
    {
        if (!is_array($update)) $update = [];
        self::preUpdate($userId, $update);
        $updateData = [];
        $fields = str_to_fields(self::$redisFields);
        foreach ($fields as $field) {
            if (is_array($update) && isset($update[$field])) {
                $updateData[$field] = $update[$field];
            }
        }
        if (!empty($updateData)) {
            $key = "user:{$userId}";
            $redis = RedisClient::getInstance();
            $json = $redis->get($key);
            $user = $json ? json_decode($json, true) : false;
            if (empty($user) || empty($user['user_id'])) return false;//没有有效的缓存则不需要更新
            if (isset($updateData['province_id'])) {
                $provinceName = Db::name('region')->where(['id' => $updateData['province_id']])->value('name');
                $updateData['province_name'] = $provinceName ? $provinceName : '';
            }
            if (isset($updateData['city_id'])) {
                $cityName = Db::name('region')->where(['id' => $updateData['city_id']])->value('name');
                $updateData['city_name'] = $cityName ? $cityName : '';
            }
            if (isset($updateData['district_id'])) {
                $districtName = Db::name('region')->where(['id' => $updateData['district_id']])->value('name');
                $updateData['district_name'] = $districtName ? $districtName : '';
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

    protected static function preUpdate($userId, &$update)
    {
        if (!empty($update['_credit_score'])) {
            $creditScoreInfo = is_array($update['_credit_score']) ? $update['_credit_score'] : json_decode($update['_credit_score'], true);
            if (is_array($creditScoreInfo)) {
                $userCreditLog = new UserCreditLog();
                $data = [
                    'user_id' => $userId,
                    'change_type' => $creditScoreInfo['change_type'],
                    'score' => $creditScoreInfo['score'],
                    'not_update_redis' => '1'
                ];
                $data = array_merge($creditScoreInfo, $data);
                $res = $userCreditLog->record($creditScoreInfo['type'], $data);
                if ($res) $update['credit_score'] = $res['credit_score'];
            }
        }
        if (isset($update['_credit_score'])) unset($update['_credit_score']);
    }
}