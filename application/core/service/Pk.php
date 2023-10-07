<?php

namespace app\core\service;


use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class Pk extends Service
{

    protected static $pk_prefix = 'BG_PK:';


    //获取pk相关数据
    public function getPkId($user_id, $room_id, $flag)
    {
        $where = $flag == 'active' ? ['active_id' => $user_id, 'active_room_id' => $room_id] : ['target_id' => $user_id, 'target_room_id' => $room_id];

        $res = Db::name('live_pk')->field('id')->where($where)->find();

        if (empty($res)) return '';

        return $res['id'];
    }


    //是否在pk
    public function checkPk($user_id)
    {
        $redis = RedisClient::getInstance();

        $is_active = $redis->exists(self::$pk_prefix . 'pking:active:' . $user_id);

        $is_target = $redis->exists(self::$pk_prefix . 'pking:target:' . $user_id);

        if (empty($is_active) && empty($is_target)) {
            $is_pk = false;

            $flag = '';
        } else {
            $is_pk = true;

            $flag = $is_active ? 'active' : 'target';
        }

        return ['is_pk' => $is_pk, 'flag' => $flag];
    }



}