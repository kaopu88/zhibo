<?php

namespace app\service;


//活动入口(删除)
class Activity
{
    public static function getActConfig($act_name)
    {
        return static::getConfig($act_name);
    }


    protected static function getConfig($act_name)
    {
        global $db, $redis, $config;

        $arr = [];

        $update = $redis->get('cache:update_activity');

        if (array_key_exists($act_name, $config) && !$update) return $config[$act_name];

        $update && $redis->del('cache:update_activity');

        $actKey = $config['activity']['redis_key'];

        $salesActRes = $redis->exists($actKey);

        if (empty($salesActRes) || $update)
        {
            $sql = 'SELECT id, mark, name, link, icon, rule, start_time, end_time, create_time FROM '.TABLE_PREFIX.'activity WHERE `status`=1';

            $salesActRes = $db->query($sql);

            $time = time();

            foreach ($salesActRes as $key => &$val)
            {
                if (!empty($val['start_time']) || !empty($val['end_time']))
                {
                    //未开始
                    if ($time < $val['start_time']) continue;

                    //结束
                    if ($time > $val['end_time'])
                    {
                        $db->update(TABLE_PREFIX.'activity')->cols(['status' => 0])->where('id = '.$val['id'])->query();

                        continue;
                    }
                }

                $mark = parse_name($val['mark'], 1);

                $val['rule'] = json_decode($val['rule'], true);

                $arr[$mark] = $val;
            }

            $redis->set($actKey, json_encode($arr));

            $redis->expire($actKey, 4 * 3600);
        }
        else{
            $salesActRes = $redis->get($actKey);

            $arr = json_decode($salesActRes, true);
        }

        foreach ($arr as $name => $conf)
        {
            if (isset($config[$name])) $config[$name] = [];

            $config[$name] = $conf;
        }

        return array_key_exists($act_name, $config) ? $config[$act_name] : [];
    }

}



