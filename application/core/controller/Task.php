<?php

namespace app\core\controller;


use think\Request;

class Task extends Controller
{

    //生成任务指标
    public function generateTaskQuota(Request $request)
    {
        $params = $request->param();

        $TaskService = new \app\core\service\Task();

        $TaskService->generateTaskQuota($params);
    }






}