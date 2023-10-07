<?php

namespace app\core\model;

use bxkj_common\RedisClient;
use think\Db;
use think\Model;

class User extends Model
{
    protected $pk = 'user_id';

    public function setting()
    {
        return $this->hasOne('UserSetting', 'user_id', 'user_id');
    }

}