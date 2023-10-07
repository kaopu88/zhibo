<?php

namespace app\h5\service\activity;

use app\common\service\Service;
use think\Db;

class Lottery extends Service
{

    /**
     * 添加中奖纪录
     */
    public function addLotteryLog($parmas)
    {
        $data = [
            'name' => $parmas['name'],
            'user_id' => $parmas['user_id'],
            'gift_type' => isset($parmas['gift_type']) ? $parmas['gift_type'] : 'gift',
            'gift_source' => isset($parmas['gift_source']) ? $parmas['gift_source'] : 'egg',
            'num' => isset($parmas['num']) ? $parmas['num'] : 1,
            'gift_id' => $parmas['gift_id'],
            'create_time' => time(),
        ];
        $insertid = Db::name('lottery_prize_log')->insertGetId($data);
        return $insertid;
    }

    /**
     * 添加参与记录
     */
    public function addUserGift($parmas)
    {
        $data = [
            'name' => $parmas['name'],
            'icon' => $parmas['image'],
            'user_id' => $parmas['user_id'],
            'gift_id' => $parmas['gift_id'],
            'access_method' => 'egg',
            'create_time' => time(),
            'gift_type' => 1
        ];
        $insertid = Db::name('user_package')->insertGetId($data);
        return $insertid;
    }



}