<?php

namespace app\taokeshop\controller;

use bxkj_module\controller\Admin;
use think\Db;
use think\facade\Request;

class Controller extends Admin
{
    //登录检查白名单方法
    protected $allows = array(
        "account" => array("login", "sync", 'forget_password'),
        "common" => array('__un__'),
        "setting" => array("clear_redis_callback")
    );

    public function __construct()
    {
        parent::__construct();
        //未登录
        if (!is_allow($this->allows) && empty($this->admin)) {
            $this->redirect($this->loginRedirect);
        }
        if (Request::isGet()) {
            $this->createMenu('admin', 'admin', AID, 'current');
        }

        if (!empty($this->admin) && is_allow(['site' => ['index'], 'index' => ['index'], 'article' => ['index'], 'setting' => ['index']])) {
            if (weak_password($this->admin['salt'], $this->admin['password'], $this->admin['username'])) {
                $this->error('密码过于简单，请修改密码', 1, url('personal/change_pwd') . '?redirect=' . urlencode(Request::url()));
                exit();
            }
        }

        $workTypes = Db::name('work_types')->field('name, type as value, default_aid')->select();
        $this->assign('work_types', $workTypes);
    }
}
