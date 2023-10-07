<?php

namespace app\api\controller;

use app\admin\service\SysConfig;
use app\common\controller\Controller;
use app\common\service\DsSession;
use app\taoke\service\Position;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use think\Db;
use think\Exception;
use think\facade\Session;


class Account extends Controller
{
    //登录接口
    public function login()
    {
        $params = input();
        $sdk = new CoreSdk();
        $user = $sdk->post('user/login', array(
            'phone_code' => $params['phone_code'],
            'username' => $params['username'],
            'password' => $params['password'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$user) return $this->jsonError($sdk->getError()->message, $sdk->getError()->status);
        $this->loginAfter($user);
        //登录检测是否已生成拼多多和京东pid

        $taokeSwicth =  config('taoke.taoke_swicth') ? config('taoke.taoke_swicth') : 0;
        if ($taokeSwicth == 1) {
            $position = new Position();
            $position->checkPid($user['user_id']);
        }

        \app\common\service\User::safeFiltering($user);
        return $this->success($user, '登录成功');
    }

    protected function loginAfter($user)
    {
        DsSession::set('user', $user);
        DsSession::set('write_time', time());
        //对接rabbitMQ
        try{
            $rabbitChannel = new RabbitMqChannel(['user.credit']);
            $rabbitChannel->exchange('main')->sendOnce('user.credit.login', ['user_id' => $user['user_id']]);
        }catch (\Exception $e)
        {
          
        }
    }

    //快捷登录 添加邀请码注册的逻辑
    public function quickLogin()
    {
        $params = input();
        $sdk = new CoreSdk();
        $user = $sdk->post('user/quick_login', array(
            'phone_code' => $params['phone_code'],
            'phone' => $params['phone'],
            'invite_code' => $params['invite_code'],
            'code' => $params['code'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$user) return $this->jsonError($sdk->getError()->message, $sdk->getError()->status);
        $this->loginAfter($user);
        \app\common\service\User::safeFiltering($user);
        return $this->success($user, '登录成功');
    }
    
    public function reg()
    {
        $params = input();
        $sdk = new CoreSdk();
        $data = array(
            'phone' => $params['phone'],
            'username'=>$params['username'],
            'phone_code' => $params['phone_code'],
            'invite_code' => $params['invite_code'] ? $params['invite_code'] : 0,
            'code' => $params['code'],
            'password' => $params['password'],
            'after_login' => '1',
            'client_seri' => ClientInfo::encode()
        );
        $username = $params['username'];
        $password = $params['password'];
        if (empty($username)) $this->jsonError('用户名不能为空');
        if (empty($password)) $this->jsonError('密码不能为空');
        $length = mb_strlen($username);
        if ($length < 4 || $length > 25) $this->jsonError('用户名4-25个字符');
        if (!validate_regex($username, '/^[0-9a-zA-Z_]{4,25}$/')) $this->jsonError('用户名格式不正确');
        
        // if (validate_regex($username, 'number')) return $this->jsonError('用户名不能为纯数字');
        if (!validate_regex($password, 'require')) return $this->jsonError('密码不能为空');
        if (!validate_regex($password, 'no_blank')) return $this->jsonError('密码不能包含空格');
        if (validate_regex($password, 'number')) return $this->jsonError('密码不能为纯数字');
        if (strlen($password) < 6) return $this->jsonError('密码不能小于6位');
        if (strlen($password) > 16) return $this->jsonError('密码不能大于16位');
        
        $user = Db::name('user')->where(['username' => $username, 'delete_time' => null])->field('user_id,username,nickname,phone')->find();
        if (!empty($user)) return $this->jsonError('用户已注册，请直接登录');
        
        if (isset($params['username'])) $data['username'] = $params['username'];
        $user = $sdk->post('user/create_account', $data);
        if (!$user) return $this->jsonError($sdk->getError()->message, 1);
        $this->loginAfter($user);
        \app\common\service\User::safeFiltering($user);
        return $this->success($user, '注册成功');
    }
    public function reg_back()
    {
        $params = input();
        $sdk = new CoreSdk();
        $data = array(
            'phone' => $params['phone'],
            'phone_code' => $params['phone_code'],
            'invite_code' => $params['invite_code'] ? $params['invite_code'] : 0,
            'code' => $params['code'],
            'password' => $params['password'],
            'after_login' => '1',
            'client_seri' => ClientInfo::encode()
        );
        if (isset($params['username'])) $data['username'] = $params['username'];
        $user = $sdk->post('user/create_by_phone', $data);
        if (!$user) return $this->jsonError($sdk->getError()->message, 1);
        $this->loginAfter($user);
        \app\common\service\User::safeFiltering($user);
        return $this->success($user, '注册成功');
    }

    //通过设备注册账号
    public function regByDevice()
    {
        if (empty(APP_MEID)) return $this->jsonError('设备码不能为空');
        if (empty(APP_OS_NAME)) return $this->jsonError('系统标识符不能为空');
        $result = Db::name('user_third')->where([
            'type' => 'device',
            'appid' => APP_OS_NAME,
            'openid' => APP_MEID,
            'status' => 'bind'
        ])->find();
        $sdk = new CoreSdk();
        $data = array(
            'os' => APP_OS_NAME,
            'meid' => APP_MEID,
            'client_seri' => ClientInfo::encode()
        );
        if ($result) {
            $user = $sdk->post('user/login_by_device', $data);
            $actName = '登录';
        } else {
            $data['after_login'] = '1';
            $user = $sdk->post('user/reg_by_device', $data);
            $actName = '注册';
        }
        if (!$user) return $this->jsonError($sdk->getError()->message, 1);
        $this->loginAfter($user);
        \app\common\service\User::safeFiltering($user);
        return $this->success($user, $actName . '成功');
    }

    //第三方登录
    public function loginByThird()
    {
        $params = input();
        $type = $params['type'];
        if (empty($type) || !enum_has('third_type', $type)) return $this->jsonError('第三方平台不支持');
        $sdk = new CoreSdk();
        $data = array(
            'type' => $type,
            'openid' => $params['openid'],
            'uuid' => $params['uuid'] ? $params['uuid'] : '',
            'nickname' => $params['nickname'] ? $params['nickname'] : '',
            'avatar' => $params['avatar'] ? $params['avatar'] : '',
            'gender' => $params['gender'] ? $params['gender'] : '0',
            'client_seri' => ClientInfo::encode()
        );
        $user = $sdk->post('user/login_by_third', $data);
        if (!$user) return $this->jsonError($sdk->getError()->message, $sdk->getError()->status);
        //2018-3-30修正方案
        if ($user['unregistered'] == '1') {
            $user = $data;
            $user['unregistered'] = '1';
            DsSession::set('third_data', $user);
            $redis = new RedisClient();
            $redis->set('third_data:' . $params['access_token'], json_encode($user), 3600);
            $user['phone'] = '';
            $user['phone_code'] = '';
        } else {
            $user['unregistered'] = '0';
        }
        if ($user['user_id']) {
            $this->loginAfter($user);
        }
        \app\common\service\User::safeFiltering($user, 'longitude,latitude,os,device_brand,v,meid,client_ip');
        return $this->success($user, '登录成功');
    }

    //绑定手机号(仅限第三方登录后使用)
    public function bindPhone()
    {
        $params = input();
        $sdk = new CoreSdk();
        $phone = $params['phone'];
        $phoneCode = $params['phone_code'];
        $code = $params['code'];
        if (empty($phone) || !validate_regex($phone, 'phone')) return $this->jsonError('手机号不正确');
        $redis = RedisClient::getInstance();
        $getphone = $redis->get('mob_phone' . $phone);
        if (empty($getphone)) {
            if (empty($code) || !validate_regex($code, '/\d{6}/')) return $this->jsonError('验证码不正确');
        }
        /*$third_data = DsSession::get('third_data');
        if (empty($third_data)) return $this->jsonError('请重新通过第三方平台登录');*/
        $third_data = json_decode($redis->get('third_data:' . $params['access_token']), true);
        if (empty($third_data)) return $this->jsonError('请重新通过第三方平台登录');

        $third_data['unregistered'] = '1';
        $third_data['phone'] = $phone;
        $third_data['phone_code'] = $phoneCode;
        $third_data['code'] = $code;
        $third_data['scene'] = $params['scene'];
        $third_data['after_login'] = '1';
        $third_data['client_seri'] = ClientInfo::encode();
        $third_data['invite_code'] = $params['invite_code'] ? $params['invite_code'] : 0;
        $user = $sdk->post('user/create_by_third', $third_data);
        if (!$user) return $this->jsonError($sdk->getError());
        DsSession::set('third_data', null);
        $redis->del('third_data:' . $params['access_token']);
        $this->loginAfter($user);
        \app\common\service\User::safeFiltering($user);
        return $this->success($user, '绑定成功');
    }

    public function logout()
    {
        $this->user = null;
        DsSession::set('user', null);
        return $this->success(array('login_status' => '0'), '退出成功');
    }

    //找回密码
    public function resetPwd()
    {
        $phone = input('phone');
        $password = input('password');
        $confirm_password = input('confirm_password');
        $code = input('code');
        $phoneCode = input('phone_code');
        $sdk = new CoreSdk();
        $result = $sdk->post('user/reset_pwd', array(
            'phone_code' => $phoneCode,
            'phone' => $phone,
            'password' => $password,
            'confirm_password' => $confirm_password,
            'code' => $code
        ));
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '重置成功');
    }

    /**
     * 判断手机号码是否注册过
     * 用来告诉前端是否需要进行邀请码输入操作
     */
    public function checkPhone()
    {
        try {
            $params = input();
            $phone = $params['phone'];
            apiAsserts ((empty($phone) || !validate_regex($phone, 'phone')), '手机号不正确');
            $scene = $params['scene'];
            $phoneCode = $params['phone_code'];
            $sms = Db::name('sms_code')->where(array(
                'scene' => $scene,
                'phone' => $phone,
                'phone_code' => $phoneCode,
                'is_check' => '0'
            ))->where('expiration', '>', time())->order('send_time desc')->find();
            apiAsserts (empty($sms),'验证码错误');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $where = array('phone' => $phone, 'delete_time' => NULL);
        $user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where($where)->find();
        if (!empty($user))  return $this->success(['is_reg' => 1], '已注册');
        
        return $this->success(['is_reg' => 0], '未注册');
    }

    public function checkWxPhone()
    {
        try {
            $params = input();
            $phone = $params['phone'];
            apiAsserts ((empty($phone) || !validate_regex($phone, 'phone')), '手机号不正确');
            apiAsserts ((empty($params['uuid'])), 'uuid不能为空');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $where = array('phone' => $phone, 'delete_time' => NULL);
        $user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where($where)->find();
        $site = config('site.');
        $type_reg = isset($site['register_type']) ? $site['register_type']: 0;
        if ($type_reg == 1) {
            if (!empty($user))  return $this->success(['is_reg' => -1], '该手机号已注册,请更换手机号');
            //不需要进行是否注册查询 必须要未注册的手机号
            $resBind = Db::name('user_third')->where(['uuid' => $params['uuid'], 'status' => 'unbind'])->find();
            if ($resBind && !empty($resBind['invite_code'])) return $this->success(['is_reg' => 1], '可注册并有邀请码');
            return $this->success(['is_reg' => 0], '可注册无邀请码');
        } else {
            if (!empty($user))  return $this->success(['is_reg' => 1], '已注册');
            return $this->success(['is_reg' => 0], '未注册需输入邀请码');
        }
    }

    public function checkIsPwd()
    {
        try {
            $params = input();
            $phone = $params['phone'];
            apiAsserts ((empty($phone) || !validate_regex($phone, 'phone')), '手机号不正确', 10003);
            $where = array('phone' => $phone, 'delete_time' => NULL);
            $user = Db::name('user')->field('user_id,username,phone,status,salt,isset_pwd')->where($where)->find();
            apiAsserts(empty($user),  '账号不存在', 10002);
            apiAsserts($user['isset_pwd'] != '1', '尚未设置密码，请先设置密码', 10001);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), $e->getCode());
        }
        return $this->success('请去登录');
    }

    public function getInviteUser()
    {
        try {
            $params = input();
            $inviteCode = $params['invite_code'];
            apiAsserts ((empty($inviteCode)), '邀请码不能为空');
            $user = Db::name('user')->field('user_id,username,nickname,avatar')->where(['invite_code' => $inviteCode])->find();
            apiAsserts ((empty($user)), '用户不存在');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
        return $this->success($user, '未注册');
    }
}
