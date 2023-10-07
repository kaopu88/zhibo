<?php

namespace app\recharge\controller;

use app\recharge\service\ThirdTrade;
use think\facade\Request;

class ThirdOrder extends Controller
{
    public function unifiedorder()
    {
        $params = Request::post();
        $thirdTrade = new ThirdTrade();
        $result = $thirdTrade->unifiedorder($params);
        if ($result === false) return json_error($thirdTrade->getError());
        return json_success($result);
    }


}
