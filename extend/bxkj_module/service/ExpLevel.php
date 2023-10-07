<?php

namespace bxkj_module\service;

use think\Db;

class ExpLevel extends Service
{
    public static function getLevelInfo($level, $default = null)
    {
        if (!isset($default)) {
            $default = array(
                'levelid' => '',
                'level_name' => '未知',
                'level_up' => 0,
                'level_icon' => img_url('', '', 'lv_icon')
            );
        }
        $key = 'level:list';
        $expLevels = cache($key);
        if (empty($expLevels) || config('app.app_debug')) {
            $expLevels = Db::name('exp_level')->field('levelid,name level_name,level_up,icon level_icon')->select();
            if (!empty($expLevels)) cache($key, $expLevels);
        }
        $lvInfo = self::getItemByList($level, $expLevels, 'levelid');
        $lvInfo = array_merge($default, is_array($lvInfo) ? $lvInfo : []);
        return $lvInfo;
    }
}