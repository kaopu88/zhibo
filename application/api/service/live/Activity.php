<?php


namespace app\api\service\live;


use app\api\service\LiveBase2;
use think\Db;

class Activity extends LiveBase2
{
    //初始化活动数据
    public function getLiveActivity($data = [], $type = 'client')
    {
        self::$activity_data['guard'] = $data['live_guard'];
        self::$activity_data['guard']['guard_show'] = 1;

        $activity_show = config('app.live_setting.activity_show', ['client' => 1, 'service' => 1]);

        if (($type == 'client' && !$activity_show['client']) || ($type == 'service' && !$activity_show['service'])) self::$activity_data['activity_url'] = [];

        $activity_name = config('app.live_setting.activity_name', '');

        if (!empty($activity_name))
        {
            $activity_name = parse_name($activity_name);

            $activity_config = getActConfig($activity_name);

            $now = time();

            if (empty($activity_config) || ($now > $activity_config['start_time'] && $now < $activity_config['end_time'])) self::$activity_data['ad_url'] = [];
        }
    }



}


