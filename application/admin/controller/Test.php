<?php

namespace app\admin\controller;

use bxkj_common\RedisClient;

class Test extends \think\Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function user()
    {
        $user=new \bxkj_module\service\User();
        $user->getUsersByIds('10000035',6811306);
    }

    public function transfer()
    {
        $userTransfer = new \bxkj_module\service\UserTransfer();



        exit();

        $res = $userTransfer
            ->setAsync(true)
            ->setFromUsers(10000035)
            ->setAdmin('erp', 1)//操作者 erp 后台操作 agent config('app.agent_setting.agent_name')操作
            ->setTargetAgent(6811306)->transfer();
        var_dump($res);exit();



        //【总后台转用户】
        //1、指定客户10000035转给config('app.agent_setting.agent_name')6811306

        $res = $userTransfer
            ->setAsync(true)
            ->setFromUsers(10000035)
            ->setAdmin('erp', 1)//操作者 erp 后台操作 agent config('app.agent_setting.agent_name')操作
            ->setTargetAgent(6811306)->transfer();

        //2、config('app.agent_setting.promoter_name')10000165的所有客户转给config('app.agent_setting.promoter_name')10000512
        $res2=$userTransfer->setAsync(true)
            ->setFromPromoter(10000165)
            ->setAdmin('erp', 1)
            ->setTargetPromoter(10000584)->transfer();







        if (!$res) {
            var_dump($userTransfer->getError());
        }
    }


}
