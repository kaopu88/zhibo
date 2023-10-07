<?php

namespace app\core\service;

use think\Db;

class UserSetting extends \bxkj_module\service\UserSetting
{
    public function create($userId, $setting = [])
    {
        if (empty($userId)) return false;
        $setting = array_merge(array(
            'comment_push' => '1',
            'like_push' => '1',
            'follow_push' => '1',
            'follow_new_push' => '1',
            'recommend_push' => '1',
            'follow_live_push' => '1',
            'msg_push' => '1',
            'rank_stealth' => '0',
            'download_switch' => '1'
        ), $setting);
        $setting['user_id'] = $userId;
        $setRes = Db::name('user_setting')->insert($setting);
        return $setRes;
    }
}