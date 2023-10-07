<?php

namespace app\core\controller;

use app\core\service\GiftLog;
use think\facade\Request;

class Gift extends Controller
{
    //送礼物
    public function give()
    {
        $params = Request::post();
        $gift = new GiftLog();
        $result = $gift->give($params);
        if (!$result) return json_error($gift->getError());
        return json_success($result);
    }


    //送礼物
    public function giveTest()
    {
        $params = Request::post();
        $gift = new GiftLog();
        $result = $gift->give([
            'gift_id' => 107,
            'num' => 1,
            'user_id' => 10152,
            'to_uid' => 10183,
            'consume_order' => 'user_package',
            'pay_scene' => 'video',
        ]);

        if (!$result) return json_error($gift->getError());
        return json_success($result);
    }
}
