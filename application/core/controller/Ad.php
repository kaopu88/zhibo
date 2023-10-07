<?php

namespace app\core\controller;

use app\core\service\AdContent;
use think\facade\Request;

class Ad extends Controller
{
    //获取广告内容
    public function get_contents()
    {
        $params = Request::post();
        $space = $params['space'];
        $adContent = new AdContent();
        $ad = $adContent->getContents($space, $params);
        if ($ad === false) return json_error($adContent->getError());
        return json_success($ad);
    }

    public function get_content_total()
    {
        $params = Request::post();
        $space = $params['space'];
        $adContent = new AdContent();
        $total = $adContent->getContentTotal($space, $params);
        if ($total === false) return json_error($adContent->getError());
        return json_success($total);
    }


}
