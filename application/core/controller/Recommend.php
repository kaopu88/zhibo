<?php

namespace app\core\controller;

use app\core\service\RecommendContent;
use think\facade\Request;

class Recommend extends Controller
{
    //获取推荐内容
    public function get_contents()
    {
        $params = Request::post();
        $space = $params['space'];
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = $params['length'] ? ($params['length'] > 50 ? 50 : $params['length']) : 10;
        $recommendContent = new RecommendContent();
        $recommend = $recommendContent->getContents($space, $params, $offset, $length);
        if ($recommend === false) return json_error($recommendContent->getError());
        return json_success($recommend);
    }

    public function get_content_total()
    {
        $params = Request::post();
        $space = $params['space'];
        $recommendContent = new RecommendContent();
        $total = $recommendContent->getContentTotal($space, $params);
        if ($total === false) return json_error($recommendContent->getError());
        return json_success($total);
    }


}
