<?php

namespace app\agent\controller;

use bxkj_module\service\AgentAdmin;
use think\Db;
use think\facade\Request;

class Account extends Controller
{
    protected $sysName = 'agent_admin';

    public function login()
    {
        $redirect = empty(input('redirect')) ? url('index/index') : input('redirect');
        if (!empty($this->admin)) $this->redirect($redirect);
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $username = input('username');
            $password = input('password');
            $autoLogin = input('auto_login');
            if (empty($username)) $this->error('用户名不能为空');
            if (empty($password)) $this->error('密码不能为空');
            $adminService = new AgentAdmin();
            $admin = $adminService->login($username, $password);
            if (!$admin) $this->error($adminService->getError());
            if ($admin['is_root'] != '1') $this->error(config('app.agent_setting.promoter_name').'账号不允许登录');
            $agentService = new \app\agent\service\Agent();
            $agentInfo = $agentService->checkLogin($admin['agent_id']);
            if (!$agentInfo) $this->error($agentService->getError());
            $admin['session_time'] = time();
            session($this->sysName, $admin);
            //自动登录cookie
            if ($autoLogin == '1') {
                $adminInfo = Db::name('agent_admin')->field('password,is_root')->where(['id' => $admin['id']])->find();
                if ($adminInfo['is_root'] != '1') $this->error(config('app.agent_setting.promoter_name').'账号不允许登录');
                $ip = get_client_ip();
                $exp = (int)config('session.login_auto_expire');
                cookie($this->sysName . '_auto_login', $adminService->getAutoCookie($admin['id'], $adminInfo['password'], $ip, $exp), $exp);
            }
            //sso
            $result['uid'] = $admin['id'];
            $result['username'] = $admin['username'];
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
            $adminService = new AgentAdmin();
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

    //传送登录
    public function transfer()
    {
        $params = input();
        if (!is_sign($params['sign'], $params, TRANSFER_AGENT_KEY)) $this->error('登录凭证无效');
        if ($params['time'] + 300 <= time()) $this->error('登录凭证已过期');
        $agent = Db::name('agent')->where(['id' => $params['id'], 'delete_time' => null])->find();
        if (empty($agent)) $this->error(config('app.agent_setting.agent_name').'不存在');
        $adminService = new AgentAdmin();
        $admin = $adminService->loginByUid($agent['root_id']);
        if (!$admin) $this->error($adminService->getError());
        $admin['session_time'] = time();
        session($this->sysName, $admin);
        return redirect(url('index/index'));
    }

}
