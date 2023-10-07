<?php

namespace app\core\controller;

use bxkj_module\service\Work;

class Admin extends Controller
{
    //分配管理员
    public function assign_admin()
    {
        /*$workService = new Work();
        $type = input('type');
        if (empty($type)) return json_success(0, '分配失败');
        $relId = input('rel_id');
        $orderNo = input('order_no');
        $incr = input('incr');
        $relId = $relId ? $relId : '';
        $orderNo = $orderNo ? $orderNo : '';
        $incr = isset($incr) ? $incr : 1;
        $aid = $workService->allocation($type, $relId, $orderNo, (int)$incr);
        return json_success((int)$aid, '分配成功');*/
    }
}
