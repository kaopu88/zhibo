<?php

namespace app\h5\controller;

use bxkj_common\CoreSdk;
use think\Db;
use think\facade\Request;

class Invite extends WxController
{
    public function index()
    {
        $site = config('site.');
        $uploadImg = config('upload.');

        $sk = input('sk');
        $userId = input('user_id');
        $anchorId = input('anchor');
        $user = Db::name('user')->where(array('delete_time' => null, 'user_id' => $userId))->field('user_id,avatar,nickname,gender,invite_code')->find();
        if (!empty($anchorId)) {
            $anchor = Db::name('user')->where(array('delete_time' => null, 'user_id' => $anchorId))->field('user_id,avatar,nickname,gender')->find();
        }
        if ($site['invite_code'] == 2 && empty($user)) return $this->error('进入方式有点问题哦!');

        $default = array('user_id' => '', 'nickname' => '未知用户', 'avatar' => img_url('', '200_200', 'avatar'));
        $info = $anchor ? $anchor : ($user ? $user : $default);
        $this->assign('info', $info);
        $this->assign('user', $user ? $user : []);
        $this->assign('anchor', $anchor ? $anchor : []);
        $bean = config('app.product_setting.reg_bean');

        $this->assign('product_slogan', config('app.product_setting.slogan'));
        $this->assign('product_name', config('app.product_setting.name'));
        $this->assign('bean_name', config('app.product_setting.bean_name'));
        $this->assign('bean', $bean);
        $this->assign('site', $site);
        $last_send_time = session('last_send_time');
        $countdown = 60 - (time() - $last_send_time);
        $countdown = $countdown < 0 ? 0 : $countdown;
        $this->assign('countdown', $countdown);
        $this->assign('wx', input('wx'));
        $this->assign('invite_imgs', $uploadImg['invite_imgs']);
        if (!empty($sk)) {
            Db::name('share_record')->where('share_key', $sk)->setInc('pv', 1);
        }
        $type = isset($site['register_type']) ? $site['register_type']: 0;
        if ($type == 2) return $this->fetch('invite_code');

        $this->assignJsapiConfig();
        if ($type == 1) {
            $wxUser = $this->getWxUserInfo();

            if (!$wxUser) {
                $params = ['wx' => '1', 'user_id' => $userId];
                if (!empty($anchorId)) $params['anchor'] = $anchorId;
                $this->authorize([
                    'redirect' => url('h5/invite/index', $params)
                ]);
                exit();
            } else {
                $resBind = Db::name('user_third')->where(['uuid' => $wxUser['unionid']])->find();
                if (empty($resBind)) {
                    $bindData = array(
                        'openid' => $wxUser['openid'],
                        'type' => 'weixin',
                        'status' => 'unbind',
                        'user_id' => '',
                        'is_from' => 1,
                        'uuid' => $wxUser['unionid'] ? $wxUser['unionid'] : '',
                        'appid' => $this->wxAppId,
                        'invite_code' => $user['invite_code'] ? $user['invite_code'] : ''
                    );
                    $bindId = Db::name('user_third')->insertGetId($bindData);
                }
            }
            return $this->redirect('/h5/download/index');
        }

        return $this->fetch();
    }

    public function reg()
    {
        if (Request::isPost()) {
            $type = input('type');
            if (!in_array($type, ['phone', 'weixin', 'qq','account'])) {
                $this->error('注册方式不支持');
            }
            switch ($type) {
                case 'phone':
                    return $this->commonReg(input('phone'), input('code'), input('user_id'), input('anchor'),'', input('invite_code'));
                    break;
                case 'weixin':
                    return $this->weixinReg();
                    break;
                case 'qq':
                    break;
                case 'account':
                    return $this->accountReg(input('username'), input('password'), input('user_id'), input('anchor'),'', input('invite_code'));
                    break;
            }
        }

    }

    private function weixinReg()
    {
        $userId = input('user_id');
        $anchorId = input('anchor');
        $wxUser = $this->getWxUserInfo();
        if (!$wxUser) {
            $params = ['wx' => '1', 'user_id' => $userId];
            if (!empty($anchorId)) $params['anchor'] = $anchorId;
            $this->authorize([
                'redirect' => url('h5/invite/index', $params)
            ]);
            exit();
        }
        $uuid = $wxUser['unionid'];
        $bind = Db::name('user_third')->where(['status' => 'bind', 'type' => 'weixin', 'uuid' => $uuid])->find();
        if ($bind) $this->error('用户已注册，请直接登录', 2002, '', ['wx_nickname' => $wxUser['nickname']]);

        $thirdData = array(
            'nickname' => $wxUser['nickname'],
            'openid' => $wxUser['openid'],
            'gender' => in_array($wxUser['sex'], ['1', '2']) ? $wxUser['sex'] : '0',
            'language' => $wxUser['language'] ? $wxUser['language'] : '',
            'city' => $wxUser['city'] ? $wxUser['city'] : '',
            'province' => $wxUser['province'] ? $wxUser['province'] : '',
            'country' => $wxUser['country'] ? $wxUser['country'] : '',
            'avatar' => $wxUser['headimgurl'] ? $wxUser['headimgurl'] : '',
            'uuid' => $uuid,
            'type' => 'weixin',
            'appid' => $this->wxAppId
        );

        $phone = input('phone');
        if (!isset($phone)) $this->error('请绑定手机号', 2003);
        return $this->commonReg($phone, input('code'), $userId, $anchorId, $thirdData, input('invite_code'));
    }

    private function commonReg($phone, $code, $userId, $anchorId, $thirdData = null, $invite_code = 0)
    {
        if (empty($phone)) $this->error('手机号不能为空');
        if (empty($code)) $this->error('手机号不能为空');
        $core = new CoreSdk();
        $res = $core->post('common/check_sms_code', array(
            'phone' => $phone,
            'scene' => $thirdData ? 'third_bind' : 'reg',
            'code' => $code
        ));
        if (!$res) return json_error($core->getError());
        $user = Db::name('user')->where(['phone' => $phone, 'delete_time' => null])->field('user_id,username,nickname,phone')->find();
        if (!empty($user)) return $this->error('用户已注册，请直接登录', 2002, '', $user);

        $data = array(
            'phone' => $phone,
            'promoter_uid' => $userId ? $userId : '',
            'anchor_uid' => $anchorId ? $anchorId : '',
            'invite_code' => $invite_code ? $invite_code : 0,
        );

        if (empty($thirdData)) {
            //手机号注册用户
            $user = $core->post('user/create_by_phone2', $data);
            if (!$user) return $this->error($core->getError());
            return $this->success('注册成功', ['phone' => $user['phone'], 'user_id' => $user['user_id'], 'nickname' => $user['nickname']]);
            exit();
        }
        $data = array_merge($data, $thirdData);
        $data['unregistered'] = '1';
        $user = $core->post('user/create_by_third', $data);
        if (!$user) return $this->error($core->getError());
        return $this->success('注册成功', ['phone' => $user['phone'], 'user_id' => $user['user_id'], 'nickname' => $user['nickname']]);
    }
    private function accountReg($username, $password, $userId, $anchorId, $thirdData = null, $invite_code = 0)
    {
        if (empty($username)) $this->error('用户名不能为空');
        if (empty($password)) $this->error('密码不能为空');
        $length = mb_strlen($username);
        if ($length < 4 || $length > 25) $this->error('用户名4-25个字符');
        if (!validate_regex($username, '/^[0-9a-zA-Z_]{4,25}$/')) $this->error('用户名格式不正确');
        if (validate_regex($username, 'number')) return $this->error('用户名不能为纯数字');

        if (!validate_regex($password, 'require')) return $this->error('密码不能为空');
        if (!validate_regex($password, 'no_blank')) return $this->error('密码不能包含空格');
        if (validate_regex($password, 'number')) return $this->error('密码不能为纯数字');
        if (strlen($password) < 6) return $this->error('密码不能小于6位');
        if (strlen($password) > 16) return $this->error('密码不能大于16位');
        
        $user = Db::name('user')->where(['username' => $username, 'delete_time' => null])->field('user_id,username,nickname,phone')->find();
        if (!empty($user)) return $this->error('用户已注册，请直接登录', 2002, '', $user);
        $core = new CoreSdk();
        $data = array(
            'username' => $username,
            'password'=>$password,
            'promoter_uid' => $userId ? $userId : '',
            'anchor_uid' => $anchorId ? $anchorId : '',
            'invite_code' => $invite_code ? $invite_code : 0,
        );

        $user = $core->post('user/create_account',$data);
        return $this->success('注册成功', ['phone' => $user['username'], 'user_id' => $user['user_id'], 'nickname' => $user['nickname']]);
    }
}
