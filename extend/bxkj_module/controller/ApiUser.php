<?php

namespace bxkj_module\controller;

use think\facade\Request;

class ApiUser extends Api
{
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $sysName = 'user';
        $user = session($sysName);
        if (!empty($user)) {
            //检查用户登录状态
        }
        $this->user = session($sysName);
        define('USERID', (!$this->user || empty($this->user['user_id'])) ? '' : $this->user['user_id']);
    }
}
