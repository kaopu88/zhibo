<?php

namespace bxkj_module\service;

use think\Db;
use think\facade\Request;

class Admin extends UserSys
{
    protected $sysName = 'admin';
    protected $idName = '管理员';
    protected $tabName = 'admin';
}