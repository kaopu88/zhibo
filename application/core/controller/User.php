<?php

namespace app\core\controller;

use app\core\service\SmsCode;
use app\core\service\Socket;
use bxkj_common\ClientInfo;
use think\Db;
use think\facade\Request;
use app\core\service\User AS UserService;
use bxkj_common\HttpClient;

class User extends Controller
{
    //手机号注册用户(需要短信验证码)
    public function create_by_phone()
    {
        $code      = Request::post('code');
        $phone     = Request::post('phone');
        $phoneCode = Request::post('phone_code');
        $mode      = Request::post('mode');
        $mode      = $mode ? $mode : 'normal';
        if ($mode == 'normal' && empty($phone)) return json_error(make_error('手机号不能为空2'));
        //显示的设置了code就需要验证短信验证码真伪
//        if (isset($code)) {
//            if (empty($code)) return json_error(make_error('短信验证码不能为空1'));
//            $smsCodeModel = new SmsCode();
//            $result = $smsCodeModel->checkCode('reg', $phone, $code, $phoneCode);
//            if (!$result) return json_error($smsCodeModel->getError());
//        }
        $user   = new UserService();
        $post   = Request::post();
        $myUser = $user->createByPhone($post);
        if (!$myUser) return json_error($user->getError());
        //注册之后是否需要登录
        if ($post['after_login'] == '1') {
            $post['login_way'] = 'after';
            $myUser            = $user->loginUser($myUser['user_id'], $post);
            if (!$myUser) return json_error($user->getError());
        }
        return json(array('status' => 0, 'data' => $myUser));
    }

    //手机号注册用户(不需要密码)
    public function create_by_phone2()
    {
        $code      = Request::post('code');
        $phone     = Request::post('phone');
        $phoneCode = Request::post('phone_code');
        if (empty($phone)) return json_error(make_error('手机号不能为空'));
        //显示的设置了code就需要验证短信验证码真伪
        // if (isset($code)) {
        //     // if (empty($code)) return json_error(make_error('短信验证码不能为空'));
        //     $smsCodeModel = new SmsCode();
        //     $result       = $smsCodeModel->checkCode('reg', $phone, $code, $phoneCode);
        //     if (!$result) return json_error($smsCodeModel->getError());
        // }
        $user = new UserService();
        $post = Request::post();
        bxkj_console($post);
        $myUser = $user->createByPhone2($post);
        if (!$myUser) return json_error($user->getError());
        //注册之后是否需要登录
        if ($post['after_login'] == '1') {
            $post['login_way'] = 'after';
            $myUser            = $user->loginUser($myUser['user_id'], $post);
            if (!$myUser) return json_error($user->getError());
        }
        return json(array('status' => 0, 'data' => $myUser));
    }
    
    //账号密码注册用户
    public function create_account()
    {
        $username     = Request::post('username');
        $password      = Request::post('password');
        if (empty($username)) return json_error(make_error('登录账号不能为空'));
        if (empty($password)) return json_error(make_error('登录密码不能为空'));
        $user = new UserService();
        $post = Request::post();
        $myUser = $user->createByAccount($post);
        if (!$myUser) return json_error($user->getError());
        //注册之后是否需要登录
        if ($post['after_login'] == '1') {
            $post['login_way'] = 'after';
            $myUser            = $user->loginUser($myUser['user_id'], $post);
            if (!$myUser) return json_error($user->getError());
        }
        return json(array('status' => 0, 'data' => $myUser));
    }
    
    public function reg_by_device()
    {
        $meid = Request::post('meid');
        if (empty($meid)) return json_error(make_error('设备码不能为空'));
        $user   = new UserService();
        $post   = Request::post();
        $myUser = $user->createByDevice($post);
        if (!$myUser) return json_error($user->getError());
        //注册之后是否需要登录
        if ($post['after_login'] == '1') {
            $post['login_way'] = 'after';
            $myUser            = $user->loginUser($myUser['user_id'], $post);
            if (!$myUser) return json_error($user->getError());
        }
        return json(array('status' => 0, 'data' => $myUser));
    }

    public function login_by_device()
    {
        $meid = Request::post('meid');
        if (empty($meid)) return json_error(make_error('设备码不能为空'));
        $user   = new UserService();
        $myUser = $user->loginByDevice(Request::post());
        if (!$myUser) return json_error($user->getError());
        return json(array('status' => 0, 'data' => $myUser));
    }

    //通过第三方登录
    public function login_by_third()
    {
        $user   = new UserService();
        $myUser = $user->loginByThird(Request::post());
        if (!$myUser) return json_error($user->getError());
        return json(array('status' => 0, 'data' => $myUser));
    }

    //快捷登录(手机号已存在则登录，未存在则注册登录)
    public function quick_login()
    {
        $code      = Request::post('code');
        $phone     = Request::post('phone');
        $phoneCode = Request::post('phone_code');
        if (empty($phone)) return json_error(make_error('手机号不能为空'));
        $user   = new UserService();
        $myUser = $user->quickLogin(Request::post());
        if (!$myUser) return json_error($user->getError());
        return json(array('status' => 0, 'data' => $myUser));
    }

    //用户名(手机号)+密码登录
    public function login()
    {
        $user   = new UserService();
        $myUser = $user->login(Request::post());
        if (!$myUser) return json_error($user->getError());
        return json(array('status' => 0, 'data' => $myUser));
    }

    //通过第三方账号注册
    public function create_by_third()
    {
        $post         = Request::post();
        $code         = Request::post('code');
        $phone        = Request::post('phone');
        $phoneCode    = Request::post('phone_code');
        $unregistered = Request::post('unregistered');
        if ($unregistered != '1') return json_error(make_error('请重新通过第三方平台登录'));
        if (empty($phone)) return json_error(make_error('手机号不能为空'));
        $user   = new UserService();
        $myUser = $user->createByThird($post);
        if (!$myUser) return json_error($user->getError());
        //注册之后是否需要登录
        if ($post['after_login'] == '1') {
            $post['login_way'] = 'after';
            $myUser            = $user->loginUser($myUser['user_id'], $post);
            if (!$myUser) return json_error($user->getError());
        }
        return json(array('status' => 0, 'data' => $myUser));
    }

    //登录后修改手机号
    public function bind_phone()
    {
        $code      = Request::post('code');
        $phone     = Request::post('phone');
        $phoneCode = Request::post('phone_code');
        if (empty($phone)) return json_error(make_error('手机号不能为空'));
        if (isset($code)) {
            if (empty($code)) return json_error(make_error('短信验证码不能为空'));
            $smsCodeModel = new SmsCode();
            $result       = $smsCodeModel->checkCode('bind', $phone, $code, $phoneCode);
            if (!$result) return json_error($smsCodeModel->getError());
        }
        $user = new UserService();
        $num  = $user->bindPhone(Request::post());
        if (!$num) return json_error($user->getError());
        return json(array('status' => 0, 'data' => $num));
    }

    //一键登录(手机号已存在则登录，未存在则注册登录)
    public function login_mob()
    {
        $site  = config('site.');
        $param = Request::post();
        $phone = $param['phone'];
        if (empty($phone)) return json_error(make_error('手机号不能为空'));
        $user   = new UserService();
        $myUser = $user->loginMob($param);
        if (!$myUser) return json_error($user->getError());
        return json(array('status' => 0, 'data' => $myUser));
    }

    //获取用户信息
    public function get_user()
    {
        $user_id    = Request::post('user_id');
        $selfUserId = Request::post('self_uid');
        $fields     = Request::post('fields');
        if (empty($user_id)) return json_error(make_error(APP_ACCOUNT_NAME . '不能为空'));
        $userModel = new UserService();
        $user      = $userModel->getUser($user_id, $selfUserId, $fields);
        if (empty($user)) return json_error(make_error('用户不存在'));
        return json(array('status' => 0, 'data' => $user));
    }

    public function get_users()
    {
        $userIds    = trim(Request::post('user_ids'));
        $selfUserId = Request::post('self_uid');
        $fields     = Request::post('fields');
        if (empty($userIds)) return json_success([], '');
        $userModel = new UserService();
        $users     = $userModel->getUsers($userIds, $selfUserId, $fields);
        return json(array('status' => 0, 'data' => $users ? $users : []));
    }

    //保存信息
    public function save_info()
    {
        $params = Request::post();
        $user   = new UserService();
        $data   = $user->saveInfo($params);
        if (!$data) return json_error($user->getError());
        return json_success($data);
    }

    //设置用户密码
    public function change_pwd()
    {
        $params = Request::post();
        $user   = new UserService();
        $data   = $user->changePwd($params);
        if (!$data) return json_error($user->getError());
        return json_success($data);
    }

    //重置密码
    public function reset_pwd()
    {
        $params = Request::post();
        $user   = new UserService();
        $data   = $user->resetPwd($params);
        if (!$data) return json_error($user->getError());
        return json_success($data);
    }

    public function switch_status()
    {
        $params = Request::post();
        $user   = new UserService();
        $data   = $user->switchStatus($params);
        if (!$data) return json_error($user->getError());
        return json_success($data, '切换成功');
    }

    //延长用户VIP时间
    public function extended_vip_expire()
    {
        $params = Request::post();
        if (empty($params['unit']) || empty($params['length'])) return json_error(make_error('缺少参数'));
        $vip  = array(
            'unit'   => $params['unit'],
            'length' => $params['length']
        );
        $user = new UserService();
        $data = $user->extendedVipExpire($params['user_id'], $vip);
        if ($data === false) return json_error($user->getError());
        return json_success($data);
    }

    //绑定第三方账号
    public function bind_third()
    {
        $params = Request::post();
        $user   = new UserService();
        $data   = $user->bindThird($params);
        if ($data === false) return json_error($user->getError());
        return json_success($data);
    }

    public function update_redis()
    {
        $params = Request::post();
        $userId = $params['user_id'];
        if (isset($userId)) {
            unset($params['user_id']);
            if (isset($params['_credit_score'])) {
                $params['_credit_score'] = json_decode($params['_credit_score'], true);
            }
            UserService::updateRedis($userId, $params);
        } else {
            $data = json_decode($params['data'], true);
            if ($data) {
                foreach ($data as $userId => $item) {
                    UserService::updateRedis($userId, $item);
                }
            }
        }
    }

    public function search()
    {
        $params     = Request::post();
        $selfUserId = !empty($params['self_uid']) ? $params['self_uid'] : null;
        $offset     = isset($params['offset']) ? $params['offset'] : 0;
        $length     = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        if (empty($params['keyword'])) return json_success([], '获取成功');
        $db      = Db::name('user');
        $list    = $db
            ->where([ 'user_id' => (int)$params['keyword'], 'status' => '1', 'delete_time' => null])
            //->setKeywords($params['keyword'], '', 'number user_id', 'nickname')
            ->field('user_id, create_time')
            ->union('select user_id,create_time from '. config('database.prefix') .'user where nickname like"' . trim($params['keyword']) . '%"')
            ->order('create_time desc')
            ->limit($offset, $length)
            ->select();
        $userIds = $users = [];
        foreach ($list as $item) {
            $userIds[] = $item['user_id'];
        }
        if (!empty($userIds)) {
            $userModel = new UserService();
            $users     = $userModel->getUsers($userIds, $selfUserId, '_list');
        }
        return json_success($users, '获取成功');
    }

    public function save_pwd()
    {
        $params    = Request::post();
        $userModel = new UserService();
        $result    = $userModel->savePwd($params);
        if (!$result) return json_error($userModel->getError());
        return json_success($result, '设置成功');
    }

    public function forced_offline()
    {
        $userId = input('user_id');
        $params = input();
        if (!empty($userId)) {
            $lives       = Db::name('live')->where(['user_id' => $userId, 'room_model' => 0])->limit(5)->select();
            $liveService = new \app\core\service\Live();
            if (!empty($lives)) {
                foreach ($lives as $live) {
                    $liveService->superDestroyRoom($live['id'], '主播账号已在其他客户端登录，强制关播', $params);
                }
            } else {
                (new Socket())->leaveUserByRoomGroup($userId, '强制下线，账号已在其他客户端登录', $params);
            }
        }
    }

    public function save_user_impression()
    {
        $params    = Request::post();
        $userModel = new UserService();
        $result    = $userModel->saveImpression($params);
        if (!$result) return json_error($userModel->getError());
        return json_success($result, '设置成功');
    }

    public function get_is_live()
    {
        $params    = Request::post();
        $user_id = $params['user_id'];
        if (empty($user_id))  return json_error('非法操作');
        $userService = new \app\common\service\User;
        $user = $userService->getUser($user_id);
        if (empty($user)) return json_error('用户不存在');
        if (!empty($user['is_anchor'])) {
            $live = Db::name('live')->where(['user_id' => $user_id])->field('id,user_id')->find();
            $anchorLevel = Db::name('anchor')->where(['user_id' => $user_id])->field('id,user_id')->value('anchor_lv');
        }
        $result = ['is_anchor' => $user['is_anchor'], 'anchor_lv' => isset($anchorLevel) ? $anchorLevel : 0, 'room_id' => isset($live['id']) ? $live['id'] : 0];
        return json_success($result);
    }
}
