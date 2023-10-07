<?php

namespace app\h5\controller;

use bxkj_common\CoreSdk;
use think\Db;
use think\Request;

class Setting extends Controller
{
    //每日任务设置
    public function taskSetting(Request $request)
    {
        $params = $request->param();

        $day = date('Ymd');

        $res = Db::name('live_task')->field('task_type, task_setting')->where(['user_id' => $params['user_id'], 'date_day' => $day])->find();

        if (empty($res))
        {
            $CoreSdk = new CoreSdk();

            $CoreSdk->post('task/generateTaskQuota', ['user_id'=>$params['user_id']]);

            $res = Db::name('live_task')->field('task_type, task_setting')->where(['user_id' => $params['user_id'], 'date_day' => $day])->find();
        }

        $task_config = config('app.task_setting');

        $task_setting = json_decode($res['task_setting'], true);

        $task_config['live_duration']['min'] /= 60;

        $taskItem = [
            'task_type' => [
                '/static/h5/images/setting/system_task.png',
                '/static/h5/images/setting/user_task.png',
            ],

            'task_detail' => [
                [
                    'icon' => '/static/h5/images/setting/live.png',
                    'title' => $task_config['live_duration']['title'],
                    'unit' => '分',
                    'num' => round($task_setting['live_duration']/60),
                    'min' => $task_config['live_duration']['min'],
                    'name' => 'live_duration'
                ],

                [
                    'icon' => '/static/h5/images/setting/linght.png',
                    'title' => $task_config['light_num']['title'],
                    'unit' => '次',
                    'num' => $task_setting['light_num'],
                    'min' => $task_config['light_num']['min'],
                    'name' => 'light_num'
                ],

                [
                    'icon' => '/static/h5/images/setting/profet.png',
                    'title' => $task_config['gift_profit']['title'],
                    'unit' => '金币',
                    'num' => $task_setting['gift_profit'],
                    'min' => $task_config['gift_profit']['min'],
                    'name' => 'gift_profit'
                ],

                [
                    'icon' => '/static/h5/images/setting/fans.png',
                    'title' => $task_config['new_fans']['title'],
                    'unit' => '人',
                    'num' => $task_setting['new_fans'],
                    'min' => $task_config['new_fans']['min'],
                    'name' => 'new_fans'
                ],

                [
                    'icon' => '/static/h5/images/setting/pk.png',
                    'title' => $task_config['pk_win_num']['title'],
                    'unit' => '场',
                    'num' => $task_setting['pk_win_num'],
                    'min' => $task_config['pk_win_num']['min'],
                    'name' => 'pk_win_num'
                ]
            ]
        ];

        $this->assign('task_type', $res['task_type']);

        $this->assign('user_id', $params['user_id']);

        $this->assign('taskItem', $taskItem);

        return $this->view->fetch();
    }


    //自定义任务
    public function defineTaskSetting(Request $request)
    {
        $params = $request->param();

        $day = date('Ymd');

        $is_modify = false;

        $task_config = config('app.task_setting');

        $res = Db::name('live_task')->field('task_type, task_setting')->where(['user_id' => $params['user_id'], 'date_day' => $day])->find();

        if ($res['task_type'] == 1) $this->error('今日任务已设!');

        $task_setting = json_decode($res['task_setting'], true);

        foreach ($task_config as $key => $val)
        {
            if (array_key_exists($key, $params))
            {
                if ($key == 'live_duration') $params[$key] *= 60;

                if ($params[$key] < $val['min'])
                {
                    return $this->error($val['title'].', 设定值不能小于'.$val['min']);
                }
                else{
                    if ($task_setting[$key] != $params[$key])
                    {
                        $task_setting[$key] = $params[$key];

                        $is_modify = true;
                    }
                }
            }
        }

        if ($is_modify)
        {
            $rs = Db::name('live_task')->where(['user_id' => $params['user_id'], 'date_day' => $day])->update(['task_setting' => json_encode($task_setting), 'task_type'=>1]);

            if (!$rs) return $this->error('自定义错误，请得重试!');
        }

        return $this->success('自定义成功');
    }



}