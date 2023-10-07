<?php
namespace bxkj_module\controller;

use think\Db;
use think\facade\Request;

class Agent extends Web
{
    protected $loginRedirect;
    protected $admin;
    protected $appKey = 'bx_agent';

    public function __construct()
    {
        parent::__construct();
        $sysName = 'agent_admin';
        $CONTROLLER_NAME = strtolower(Request::controller());
        $ACTION_NAME = strtolower(Request::action());
        $self = Request::url();
        if (Request::isGet() && $CONTROLLER_NAME != 'account' && $ACTION_NAME != 'logout') {
            $this->loginRedirect = url('account/login') . '?redirect=' . urlencode($self);
        } else {
            $this->loginRedirect = url('account/login');
        }
        $adminService = new \bxkj_module\service\AgentAdmin();
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
        $this->assign('admin', $this->admin);
    }
}
