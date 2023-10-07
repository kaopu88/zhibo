<?php

namespace app\core\controller;

use bxkj_module\service\Work;
use think\facade\Request;
use app\core\service\Millet as MilletService;


class Millet extends Controller
{
    public function cash()
    {
        $params = Request::post();
        $millet = new MilletService();
        $result = $millet->cash($params);
        if (!$result) return json_error($millet->getError());
        return json_success($result);
    }

    public function inc()
    {
        $params = Request::post();
        $millet = new MilletService();
        $result = $millet->inc($params);
        if (!$result) return json_error($millet->getError());
        return json_success($result);
    }

    public function exp()
    {
        $params = Request::post();
        $millet = new MilletService();
        $result = $millet->exp($params);
        if (!$result) return json_error($millet->getError());
        return json_success($result);
    }

    public function commisson_cash()
    {
        $params = Request::post();
        $millet = new MilletService();
        $result = $millet->commisonCash($params);
        if (!$result) return json_error($millet->getError());
        $workService = new Work();
        $data['audit_aid'] = $workService->allocation('commison_withdrawal');
        return json_success($result);
    }
}
