<?php
namespace app\api\service;
use app\common\service\Service;
use think\Db;
class Lottery extends Service
{

    /**
     * 添加中奖纪录
     */
    public function addLotteryLog($data)
    {
        $insertid = Db::name('lottery_record_log')->insertGetId($data);
        return $insertid;
    }

    /**
     * 添加参与记录
     */
    public function addLotteryJoin($data)
    {
        $insertid = Db::name('lottery_join')->insertGetId($data);
        return $insertid;
    }


    /**
     * 添加参与记录
     */
    public function addUserGift($data)
    {
        $insertid = Db::name('user_package')->insertGetId($data);
        return $insertid;
    }



}