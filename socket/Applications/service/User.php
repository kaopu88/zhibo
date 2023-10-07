<?php

namespace app\service;



class User
{
    //获取用户信息
    public static function getUser($userId)
    {
        global $db, $redis;
        $isRobot = $redis->sIsMember('robot_sets', $userId);//检查是否是机器人
        $table = $isRobot ? 'robot' : 'user';
        $key = $isRobot ? "robot:{$userId}" : "user:{$userId}";
        $userJson = $redis->get($key);
        $user = $userJson ? json_decode($userJson, true) : null;
        if (empty($user) || empty($user['user_id']) || $user['cache_expired_time'] <= time()) {
            $res = $db->query('SELECT `user`.`user_id`,`user`.`pid`,`user`.`nickname`,`user`.`avatar`,`user`.`vip_expire`,`user`.`credit_score`,`user`.`gender`,`user`.`birthday`,`user`.`phone`,`user`.`level`,`user`.`isvirtual`,`user`.`type`,`user`.`is_anchor`,`user`.`is_promoter`,`bean`.`bean`,`bean`.`loss_bean`,`bean`.`last_pay_time`,`bean`.`pay_total`,`bean`.`total_bean`,`bean`.`fre_bean`,bean.id bean_id,`bean`.`recharge_total`,`bean`.`pay_status`,`bean`.`commission_price`,`bean`.`commission_pre_price`,`bean`.`commission_total_price`,`bean`.`cash_status`,`user`.`millet`,`user`.`millet_status`,`user`.`total_millet`,`user`.`fre_millet`,`user`.`his_millet`,`user`.`isvirtual_millet`,`user`.`his_isvirtual_millet`,`user`.`millet_cash_total`,`user`.`goodnum`,`user`.`exp`,`user`.`level` FROM `' . TABLE_PREFIX . $table . '` `user` LEFT JOIN `' . TABLE_PREFIX . 'bean` `bean` ON `bean`.`user_id`=`user`.`user_id` WHERE  `user`.`user_id` = ' . $userId . '  AND `user`.`delete_time` IS NULL  AND `status` = \'1\' LIMIT 1');
            $user = $res[0];
        }
        //机器人没有bean表
        if ($user && $isRobot) {
            $user['total_bean'] = 0;
            $user['bean'] = 0;
            $user['fre_bean'] = 0;
            $user['bean_id'] = 0;
            $user['pay_status'] = '1';
            $user['cash_status'] = '1';
            $user['recharge_total'] = 0;
        }
        return $user;
    }

    public static function getBasicInfo($user)
    {
        return array(
            'user_id' => $user['user_id'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'gender' => $user['gender'],
            'phone' => $user['phone'],
            'bean' => $user['bean'],
            'bean_id' => $user['bean_id'],
            'millet' => $user['millet'],
            'birthday' => $user['birthday'],
            'goodnum'  =>$user['goodnum']
        );
    }

    //更新用户的Redis缓存
    public static function updateRedis($userId, $update)
    {
        return UserRedis::updateData($userId, $update);
    }
}