<?php

namespace app\core\service;


use bxkj_module\service\Service;
use think\Db;

class Task extends Service
{

    //生成任务指标
    public function generateTaskQuota(array $params)
    {
        $task_setting = [];

        $day = date('Ymd');

        $Task_db = Db::name('live_task');

        $is_rs = $Task_db->where(['user_id' => $params['user_id'], 'date_day' => $day])->find();

        if (empty($is_rs) || empty($is_rs['task_setting']))
        {
            $task_config = config('app.task_setting');

            foreach ($task_config as $key=>$val)
            {
                $task_setting[$key] = mt_rand($val['min'], $val['max']);
            }

            $Task_db->where(['user_id' => $params['user_id'], 'date_day' => $day])->insert([
                'user_id' => $params['user_id'],
                'create_time' => time(),
                'task_setting' => json_encode($task_setting),
                'date_day' => $day
            ]);
        }
    }


    //记录任务完成
    public function generateTaskDetails(array $params)
    {
        $day = date('Ymd');

        $user_id = $params['user_id'];

        unset($params['user_id']);

        $star_num = 0;

        $task_res = Db::name('live_task')->where(['user_id' => $user_id, 'date_day' => $day])->find();

        if (!empty($task_res))
        {
            $task_setting = json_decode($task_res['task_setting'], true);

            foreach ($task_res as $key => &$val)
            {
                if (array_key_exists($key, $params))
                {
                    $val += $params[$key];

                    if ($val >= $task_setting[$key]) ++$star_num;
                }
            }

            Db::name('live_task')->where(['user_id' => $user_id, 'date_day' => $day])->update([
                'live_duration' => $task_res['live_duration'],
                'light_num' => $task_res['light_num'],
                'gift_profit' => $task_res['gift_profit'],
                'new_fans' => $task_res['new_fans'],
                'pk_win_num' => $task_res['pk_win_num'],
                'is_complete' => $star_num == 5 ? 1 : 0,
                'star_num' => $star_num
            ]);
        }
    }

}