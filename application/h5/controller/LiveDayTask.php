<?php

namespace app\h5\controller;



use think\Db;
use think\Request;

/**
 * 主播每日任务h5
 * Class LiveDayTask
 * @package app\h5\controller
 */

class LiveDayTask extends Live
{

    //主播当日任务详情
    public function taskDetail(Request $request)
    {
        $params = $request->param();

        if (empty($params['room_id'])) $this->error();

        $day = date('Ymd');

        $live_info = Db::name('live')->where(['id'=>$params['room_id'], 'status' => 1])->find();

        $day_task = Db::name('live_task')->where(['user_id'=>$live_info['user_id'], 'date_day' => $day])->find();

        if (empty($day_task)) $this->error();

        $task_rule = json_decode($day_task['task_setting'], true);

        if (!empty($live_info))
        {
            $this->getRealTaskData($live_info, $day_task, $task_rule);

            $this->assign('user_info', ['avatar' => $live_info['avatar']]);
        }

        $task_star = Db::name('live_task')->where(['user_id'=>$live_info['user_id'], 'is_complete' =>1])->count();

        $task_config =  config('app.task_setting');
        $taskItem = array();
        if($task_config['live_duration']['status']==1){
            $live_duration =  [
                'icon' => '/static/h5/images/setting/live.png',
                'target' => '今日'.$task_config['live_duration']['title'].'：'.$this->diffTime($task_rule['live_duration'], ''),
                'progress' => $this->live_duration_progress*100,
                'bg_color'=>'255, 63, 110',
                'complete' => $this->diffTime($this->live_duration, '')
            ];
            $taskItem[] = $live_duration;
        }
        if($task_config['light_num']['status']==1){
            $light_num =  [
                'icon' => '/static/h5/images/setting/linght.png',
                'target' => '今日'.$task_config['light_num']['title'].'：'.$task_rule['light_num'].'次',
                'progress' => $this->light_num_progress*100,
                'bg_color'=>'254, 123, 91',
                'complete' => $this->light_num.'次'
            ];
            $taskItem[] = $light_num;
        }
        if($task_config['gift_profit']['status']==1){
            $gift_profit =   [
                'icon' => '/static/h5/images/setting/profet.png',
                'target' => '今日'.$task_config['gift_profit']['title'].'：'.$task_rule['gift_profit'].'金币',
                'progress' => $this->gift_profit_progress*100,
                'bg_color'=>'255, 159, 0',
                'complete' => $this->gift_profit.'金币'
            ];
            $taskItem[] = $gift_profit;
        }
        if($task_config['new_fans']['status']==1){
            $new_fans =  [
                'icon' => '/static/h5/images/setting/fans.png',
                'target' => '今日'.$task_config['new_fans']['title'].'：'.$task_rule['new_fans'].'人',
                'progress' => $this->new_fans_progress*100,
                'bg_color'=>'210, 119, 254',
                'complete' => $this->new_fans.'人'
            ];
            $taskItem[] = $new_fans;
        }
        if($task_config['pk_win_num']['status']==1){
            $pk_win_num=  [
                'icon' => '/static/h5/images/setting/pk.png',
                'target' => '今日'.$task_config['pk_win_num']['title'].'：'.$task_rule['pk_win_num'].'场',
                'progress' => $this->pk_win_num_progress*100,
                'bg_color'=>'64, 218, 228',
                'complete' => $this->pk_win_num.'场'
            ];
            $taskItem[] = $pk_win_num;
        }

        foreach ($taskItem as &$value)
        {
            $value['progress'] = $value['progress'] > 100 ? 100 : $value['progress'];
        }

        $this->assign('taskItem', $taskItem);

        $this->assign('task_star', $task_star);

        return $this->view->fetch();
    }







}