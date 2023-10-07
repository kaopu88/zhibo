<?php

namespace app\timer;


use app\Common;
use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;


//未使用
class Anchor extends Common
{

    // 添加机器人(计时器)
    public static function addRobot($room_id, $timer_id)
    {
        global $redis;

        $zombieTaskKey = self::$livePrefix.$room_id.':robotTask';

        if(!empty($redis->llen($zombieTaskKey)))
        {
            $strData = $redis->rpop($zombieTaskKey);

            $data = json_decode($strData, true);

            $roomZombieKey = self::$livePrefix.$room_id.':robot';

            $msgTemp = array(
                'emit' => 'enterMsg',
                'data' => array(
                    'type' => 0,//0入房提示消息
                    'user_info'=> array(
                        'avatar' => $data['avatar'],
                        'user_id'=> $data['zid'],
                        'nice_name'=> $data['zname'],
                        'level'=> $data['zlevel'],
                        'vip_status' => 0,
                        'guard_status' => 0,
                        'control_status' => 0,
                    ),
                    'content' => '来捧场了~',
                ),
            );

            Gateway::sendToGroup($room_id, bin2hex(json_encode($msgTemp)));

            $redis->zadd($roomZombieKey, $data['zlevel'], $data['zid']);
        }
        else{
            Timer::del($timer_id);
        }
    }


    // 主播日常任务数据刷新(计时器)
    public static function anchorTask($room_id, $user_id)
    {
        global $db, $redis;

        $day = date('Ymd');

        $done = 0;

        $task_sql = "SELECT * FROM ".TABLE_PREFIX."live_task WHERE `user_id`={$user_id} AND `date_day`={$day} LIMIT 1";

        $live_sql = "SELECT * FROM ".TABLE_PREFIX."live WHERE `id`={$room_id} AND `status`=1 LIMIT 1";

        $task_info = $db->query($task_sql);

        $live_info = $db->query($live_sql);

        if (!empty($task_info[0]) && !empty($live_info[0]))
        {
            $task_setting = json_decode($task_info[0]['task_setting'], true);

            $now = time();

            $live_duration = $task_info[0]['live_duration'] + ($now-$live_info[0]['create_time']);

            $light_num = $task_info[0]['light_num'] + $redis->get("BG_LIVE:{$live_info[0]['id']}:like");

            $gift_profit = $task_info[0]['gift_profit'] + $redis->get("BG_LIVE:{$live_info[0]['id']}:incomeTotal");

            $new_fans = $task_info[0]['new_fans'] + $redis->zcount("fans:{$live_info[0]['user_id']}", $live_info[0]['create_time'], $now+86400);

            $pk_win_num = $task_info[0]['pk_win_num'] + $redis->get("BG_LIVE:{$live_info[0]['id']}:pk_num");

            $live_duration_progress = round($live_duration/$task_setting['live_duration'], 2);

            $light_num_progress = round($light_num/$task_setting['light_num'], 2);

            $gift_profit_progress = round($gift_profit/$task_setting['gift_profit'], 2);

            $new_fans_progress = round($new_fans/$task_setting['new_fans'], 2);

            $pk_win_num_progress = round($pk_win_num/$task_setting['pk_win_num'], 2);

            $live_duration_progress = $live_duration_progress > 1 ? 1 : $live_duration_progress;

            $light_num_progress = $light_num_progress > 1 ? 1 : $light_num_progress;

            $gift_profit_progress = $gift_profit_progress > 1 ? 1 : $gift_profit_progress;

            $new_fans_progress = $new_fans_progress > 1 ? 1 : $new_fans_progress;

            $pk_win_num_progress = $pk_win_num_progress > 1 ? 1 : $pk_win_num_progress;

            $done = $live_duration_progress+$light_num_progress+$gift_profit_progress+$new_fans_progress+$pk_win_num_progress;
        }

        $task_data = [
            'emit' => 'taskLive',
            'data' => ['done' => $done < 1 ? '1%' : (round($done/5, 1)*100).'%',]
        ];

        Gateway::sendToGroup($room_id, bin2hex(json_encode($task_data)));
    }

}