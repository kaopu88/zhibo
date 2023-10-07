<?php

namespace bxkj_module\controller;

use bxkj_common\RedisClient;
use think\facade\Request;

class Admin extends Web
{
    protected $loginRedirect;
    protected $admin;
    protected $appKey = 'bx_admin';

    public function __construct()
    {
        parent::__construct();
        $sysName = 'admin';
        $MODULE_NAME = strtolower(Request::module());
        $CONTROLLER_NAME = strtolower(Request::controller());
        $ACTION_NAME = strtolower(Request::action());
        $HOST = Request::domain() . '/admin';
        $self = Request::url();

      /*  if (!empty(ERP_URL) && $MODULE_NAME == 'admin' && $HOST != ERP_URL) {
            throw new \think\exception\HttpException(404, '您非法请求~~~');
        }*/

        if (Request::isGet() && $CONTROLLER_NAME != 'account' && $ACTION_NAME != 'logout') {
            $this->loginRedirect = url('/admin/account/login') . '?redirect=' . urlencode($self);
        } else {
            $this->loginRedirect = url('/admin/account/login');
        }
        $adminService = new \bxkj_module\service\Admin();
        $admin = session($sysName);
        if (!empty($admin)) {
            //更新session admin
            $life = config("session.life_time.{$sysName}");
            if ((int)$admin['session_time'] + (int)$life < time()) {
                $admin = $adminService->getUpdate($admin['id']);
                if (!$admin) {
                    session($sysName, null);
                    cookie("{$sysName}_auto_login", null);
                    return $this->syncReturn(array('act' => 'logout'), 1, $adminService->getError(), $this->loginRedirect);
                } else {
                    $admin['session_time'] = time();
                    session($sysName, $admin);
                }
            }
        } else {
            //cookie自动登录
            $cookie = cookie("{$sysName}_auto_login");
            $admin = null;
            if (!empty($cookie)) {
                $admin = $adminService->loginByCookie($cookie);
                if (empty($admin)) {
                    session($sysName, null);
                    cookie("{$sysName}_auto_login", null);
                    $this->syncReturn(array('act' => 'logout'), 1, (string)$adminService->getError(), $this->loginRedirect);
                }
            }
            if ($admin) {
                $admin['session_time'] = time();
                session($sysName, $admin);
                $this->syncReturn(array('act' => 'login', 'data' => array('uid' => $admin['id'])), 0, '自动登录成功', Request::url(), input());
            }
        }
        $this->admin = session($sysName);
        define('AID', (!$this->admin || empty($this->admin['id'])) ? '' : $this->admin['id']);
        define('AUTH_UID', AID);
        define('ROOT_UID', config('app.root_aid'));
        define('ALI_PATH',dirname(dirname(__DIR__)).'/alipay/newaop/');
        $this->assign('admin',$this->admin);
    }
}
