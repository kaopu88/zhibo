<?php

namespace bxkj_module\service;

use think\Db;

class UserSys extends Service
{
    protected $sysName = 'user';
    protected $idName = '用户';
    protected $tabName = 'admin';

    public function __construct()
    {
        parent::__construct();
    }

    public function login($username, $password, $authType = 'salt', $authKey = '')
    {
        $where = array('delete_time' => null);
        if (validate_regex($username, 'phone')) {
            $where['phone'] = $username;
        } else {
            $where['username'] = $username;
        }
        if (empty($where)) return $this->setError('请输入用户名');
        $user = Db::name($this->tabName)->where($where)->find();
        if (empty($user)) return $this->setError('用户名或密码错误');
        $auth = $this->verifyPassword($user, $password);
        if (!$auth) return $this->setError('用户名或密码错误');
        return $this->loginByUser($user);
    }

    //验证密码
    public static function verifyPassword($user, $password)
    {
        $old = str_pad('', 40, '*');
        if ($user['salt'] == $old) {
            $auth = md5(md5('rCt52pF2cnnKNB3Hkp' . $password));
            return $auth == $user['password'];
        } else {
            $auth = sha1($password . $user['salt']);
            return $auth == $user['password'];
        }
    }

    public function loginByUser($user)
    {
        if (!is_array($user)) {
            $user = Db::name($this->tabName)->where(array('id' => $user, 'delete_time' => null))->find();
            if (empty($user)) return $this->setError("{$this->idName}不存在");
        }
        if (!$this->checkUser($user)) return false;
        $data['login_ip'] = get_client_ip();
        $data['login_time'] = time();
        $data['login_device'] = '';
        Db::name($this->tabName)->where(array('id' => $user['id']))->update($data);
        $this->expandInfo($user);
        unset($user['password'], $user['salt']);
        return $user;
    }

    public function loginByUid($uid)
    {
        $user = Db::name($this->tabName)->where(array('id' => $uid, 'delete_time' => null))->find();
        if (empty($user)) return $this->setError("{$this->idName}不存在");
        return $this->loginByUser($user);
    }

    //检查用户状态
    private function checkUser($user)
    {
        if ($user['status'] != '1') return $this->setError('账号已冻结');
        if ($user['delete_time']) return $this->setError('账号不存在');
        return true;
    }

    //手机号是否存在
    public function exist($phone)
    {
        $num = Db::name($this->tabName)->where(array('phone' => $phone, 'delete_time' => null))->count();
        return $num > 0;
    }

    public function changePwd($inputData, $where = array())
    {
        if (!$this->checkPwd($inputData['password'], $inputData['confirm_password'])) return false;
        if (empty($where)) {
            if (empty($inputData['phone'])) return $this->setError('手机号不能为空');
            $where['phone'] = $inputData['phone'];
        }
        $data['salt'] = sha1(uniqid() . get_ucode());
        $data['password'] = sha1($inputData['password'] . $data['salt']);
        $num = $this->where($where)->save($data);
        if (!$num) return $this->setError('修改失败');
        return $num;
    }

    public function changePhone($uid, $phone)
    {
        $has = Db::name($this->tabName)->where(array('phone' => $phone, 'delete_time' => null))->count();
        if ($has > 0) return $this->setError('手机号已经存在');
        $num = Db::name($this->tabName)->where(array('id' => $uid))->update(array(
            'phone' => $phone
        ));
        return $num;
    }

    //检查密码
    private function checkPwd($password, $confirm_password = null)
    {
        if (empty($password)) return $this->setError('密码不能为空');
        if (strlen($password) < 6) return $this->setError('密码不能小于6位字符');
        if (strlen($password) > 16) return $this->setError('密码不能大于16位字符');
        if (validate_regex($password, '/\s/')) return $this->setError('密码不能包含空格');
        if (isset($confirm_password) && $password != $confirm_password)
            return $this->setError('两次密码输入不一致');
        return true;
    }

    //获取用户更新信息
    public function getUpdate($uid)
    {
        $user = Db::name($this->tabName)->where(array('id' => $uid))->find();
        if (empty($user)) return $this->setError("{$this->idName}不存在");
        if (!$this->checkUser($user)) return false;
        $this->expandInfo($user);
        return $user;
    }

    public function expandInfo(&$user)
    {
    }

    public function getInfo($uid, $surname = '', $fields = '')
    {
        $this->expandInfo($user);
        return $user;
    }

    //获取自动登录凭证
    public function getAutoCookie($uid, $password, $ip, $expire)
    {
        $time = time();
        $str = http_build_query(array(
            'uid' => $uid,
            'password' => sha1($password . $time . $ip . '==='),
            'time' => $time,
            'ip' => $ip
        ));
        return sys_encrypt($str, null, $expire);
    }

    //通过cookie自动登录凭证登录
    public function loginByCookie($cookie)
    {
        parse_str(sys_decrypt($cookie), $cookieArr);
        if (empty($cookieArr) || !array_key_exists('uid', $cookieArr) || !array_key_exists('password', $cookieArr)) {
            return $this->setError('自动登录凭证已失效或损坏');
        }
        $ipAuth = config('session.ip_auth');
        if ($ipAuth && $cookieArr['ip'] != get_client_ip()) {
            return $this->setError('登录IP发生变化，请重新登录');
        }
        $expire = config('session.login_auto_expire');
        if (time() >= $expire + $cookieArr['time']) {
            return $this->setError('自动登录凭证已失效');
        }
        $uid = $cookieArr['uid'];
        $password = $cookieArr['password'];
        $authKey = $cookieArr['time'] . $cookieArr['ip'] . '===';
        $user = Db::name($this->tabName)->where(array('id' => $uid, 'delete_time' => null))->find();
        if ($password != sha1($user['password'] . $authKey)) return $this->setError('登录凭证已失效');
        return $this->loginByUser($user);
    }

}