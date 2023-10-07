<?php

namespace app\core\controller;

use bxkj_common\RedisClient;
use think\facade\Request;

class Timer extends Controller
{
    public function add()
    {
        $timer = new \app\core\service\Timer();
        $data = Request::post();
        $key = $timer->add($data);
        if (!$key) return json_error($timer->getError());
        return json_success(['key' => $key], '定时器添加成功');
    }

    public function remove()
    {
        $key = Request::post('key');
        if (empty($key)) return json_error(make_error('定时器标识符不能为空'));
        $timer = new \app\core\service\Timer();
        $timer->remove($key);
        return json_success(1, '移除成功');
    }
}
