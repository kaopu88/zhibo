<?php

namespace bxkj_module\service;

use think\Db;

class UserSetting extends Service
{
    public function setting($userId, $name, $value = null)
    {
        $userService = new User();
        if (!isset($value)) {
            $user = $userService->getUser($userId);
            if (!$user) return null;
            return isset($name) ? $user[$name] : $user;
        } else {
            if (is_array($value)) {
                $data = $value;
            } else {
                $data[$name] = $value;
            }
            $num = Db::name('user_setting')->where(array('user_id' => $userId))->update($data);
            if (!$num) return false;
            $userService->updateRedis($userId, $data);
            return $num;
        }
    }
}