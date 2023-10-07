<?php

namespace app\admin\controller;

use bxkj_module\service\Admin;
use bxkj_common\BaseError;
use bxkj_module\service\UserRedis;
use think\Db;
use think\facade\Request;

class Account extends Controller
{
    protected $sysName = 'admin';

    public function login()
    {
        $redirect = empty(input('redirect')) ? url('index/index') : input('redirect');
        if (!empty($this->admin)) $this->redirect($redirect);
        $site =  config('site.');

        if (Request::isGet()) {
            $this->assign('site', $site);

            if(!empty($password = input('password', "")) && !empty( $uid = input('uid', ""))) {
                if (empty($password)) $this->error('密码不能为空');
                $adminInfo = Db::name("admin")->where(["id" => $uid, "password" => $password])->find();
                $adminService = new Admin();
                if ($adminInfo['password'] != $password) {
                    $this->error('密码不正确');
                }
                $admin = $adminService->loginByUser($adminInfo);
                if (!$admin) $this->error($adminService->getError());
                $admin['session_time'] = time();
                session($this->sysName, $admin);
                $result['uid'] = $admin['id'];
                $result['username'] = $admin['username'];
                alog("account.user.login", '管理员：' . $admin['username'] . '登录', $admin['id']);
                return $this->syncReturn(array('act' => 'login', 'data' => $result), 0, '登录成功', url('index/index'));
            }
            return $this->fetch();
        } else {
            $username = input('username');
            $password = input('password');
            $autoLogin = input('auto_login');

            if (empty($username)) $this->error('用户名不能为空');
            if (empty($password)) $this->error('密码不能为空');
            $adminService = new Admin();
            $admin = $adminService->login($username, $password);
            if (!$admin) $this->error($adminService->getError());
            $admin['session_time'] = time();
            session($this->sysName, $admin);
            //自动登录cookie
            if ($autoLogin == '1') {
                $adminInfo = Db::name('admin')->field('password')->where(['id' => $admin['id']])->find();
                $ip = get_client_ip();
                $exp = (int)config('session.login_auto_expire');
                cookie($this->sysName . '_auto_login', $adminService->getAutoCookie($admin['id'], $adminInfo['password'], $ip, $exp), $exp);
            }
            //sso
            $result['uid'] = $admin['id'];
            $result['username'] = $admin['username'];
            alog("account.user.login", '管理员：'.$admin['username'].'登录', $admin['id']);
            return $this->syncReturn(array('act' => 'login', 'data' => $result), 0, '登录成功', $redirect);
        }
    }

    public function logout()
    {
        $input = input();
        $redirect = isset($input['redirect']) ? $input['redirect'] : url('account/login');
        $uid = session("{$this->sysName}.id");
        $username = session("{$this->sysName}.username");
        $this->commonLogout();
        $result['uid'] = $uid;
        $result['username'] = $username;
        return $this->syncReturn(array('act' => 'logout'), 0, '退出成功', $redirect);
    }

    private function commonLogout()
    {
        $user = session($this->sysName);
        if ($user) {
            session($this->sysName, null);
        }
        cookie($this->sysName . '_auto_login', null);
    }

    public function sync()
    {
        $message = input('message');
        $token = config('app.sync_token');
        parse_str(sys_decrypt($message, $token), $data);
        $result = 'nothing';
        if (!empty($data) && !empty($data['act'])) {
            $act = 'sync' . ucfirst($data['act']);
            if (method_exists($this, $act)) {
                $result = call_user_func_array(array($this, $act), array($data));
            }
        }
        return jsonp($result);
    }

    //同步登录
    private function syncLogin($data)
    {
        if (!empty($data['uid'])) {
            $adminService = new Admin();
            $admin = $adminService->loginByUid($data['uid']);
            if (!empty($admin)) {
                $admin['session_time'] = time();
                session($this->sysName, $admin);
                return 'success';
            }
        }
        return 'failed';
    }

    //同步退出
    private function syncLogout($data)
    {
        $this->commonLogout();
        return 'success';
    }

    public function forget_password()
    {
        if (Request::isGet()) {
            return $this->fetch();
        } else {

        }
    }

}
