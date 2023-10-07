<?php

namespace app\core\controller;

use app\core\service\Bean as BeanService;
use think\facade\Request;

class Bean extends Controller
{
    public function get_info()
    {
        $userId = Request::post('user_id');
        $bean = new BeanService();
        $info = $bean->getInfo($userId);
        if (!$info) return json_error($bean->getError());
        return json_success($info);
    }

    public function get_batch_info()
    {
        $userIds = Request::post('user_ids');
        $bean = new BeanService();
        $info = $bean->getBatchInfo($userIds);
        if (!$info) return json_error($bean->getError());
        return json_success($info);
    }

    public function inc()
    {
        $params = Request::post();
        $bean = new BeanService();
        $result = $bean->inc($params);
        if (!$result) return json_error($bean->getError());
        return json_success($result);
    }

    //支付
    public function pay()
    {
        $params = Request::post();
        $bean = new BeanService();
        $result = $bean->exp($params);
        if (!$result) return json_error($bean->getError());
        return json_success($result);
    }

    //支付并转化为他人的金币
    public function conversion()
    {
        $params = Request::post();
        $bean = new BeanService();
        $result = $bean->conversion($params);
        if (!$result) return json_error($bean->getError());
        return json_success($result);
    }

    public function kpi_cons()
    {
        $trade = Request::post();
        //$res = Kpi::cons_trade($trade);
        $res = false;
        if (!$res) return json_error('统计失败');
        return json_success(1, '统计成功');
    }


}
