<?php

namespace app\api\controller;

use app\api\service\UserPhotoWall;
use app\common\controller\UserController;
use app\common\service\AppleReceipt;
use app\common\service\DsSession;
use app\api\service\live\Lists;
use app\api\service\Rank;
use app\core\service\SmsCode;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use bxkj_module\service\GiftLog;
use bxkj_module\service\User_task;
use think\Db;

class User extends UserController
{
    public function verification()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $user = new \app\common\service\User;
        $result = $user->verification($params);
        if (!$result) return $this->jsonError($user->getError());
        DsSession::set('user.verified', $result['verified']);
        //0未认证，1已认证，2处理中，3验证失败
        switch ($result['verified']) {
            case '1':
                $msg = '已通过实名认证';
                break;
            case '2':
                $msg = '实名认证处理中';
                break;
            case '3':
                $msg = '实名认证失败';
                break;
            default:
                $msg = '';
                break;
        }
        return $this->success($result, $msg);
    }

    //获取用户信息
    public function getUserInfo()
    {
        $userService = new \bxkj_module\service\User();
        $user = $userService->getUser(USERID);

        $find = Db::name('user_data_deal')->where(['user_id' => USERID, 'audit_status' => '-1'])->find();
        if ($find['data']) {
            $find_data = json_decode($find['data'], true);
            if (is_array($find_data)) {
                $user = array_merge($user, $find_data);
            }
        } else {
            $find = Db::name('user_data_deal')->where(['user_id' => USERID, 'audit_status' => '0'])->find();
            if ($find['data']) {
                $find_data = json_decode($find['data'], true);
                if (is_array($find_data)) {
                    $user = array_merge($user, $find_data);
                }
            }
        }
        \app\common\service\User::safeFiltering($user);
        $followModel = new \app\api\service\Follow();
        $like_num = Db::name('video_like')->where(['user_id' => USERID])->count();
        $video_num = Db::name('video')->where(['user_id' => USERID])->count();
        $zan_sum2_num = Db::name('video')->where(['user_id' => USERID])->sum('zan_sum2');
        $follow_num = $followModel->followCount(USERID);
        $fans_num = $followModel->fansCount(USERID);
        $UserService = new \app\common\service\User();
        if ($user['is_anchor']) {
            $user['anchor_level'] = Db::name('anchor')->where('user_id', $user['user_id'])->value('anchor_lv');;
            $user['anchor_level_progress'] = $UserService->getAnchorLevelProcess($user);
        }
        $impression = Db::name('user_my_impression')->where(['user_id' => USERID])->column('impression_id');
        $impression_data = $UserService->getImpression($impression);

        $like_num = $like_num + $zan_sum2_num;
        $user['impression'] = $impression_data;
        $user['video_num'] = (int)$video_num ?: 0;
        $user['like_num'] = (int)$like_num ?: 0;
        $user['follow_num'] = (int)$follow_num ?: 0;
        $user['fans_num'] = (int)$fans_num ?: 0;
        $user['follow_num_str'] = number_format2($user['follow_num']);
        $user['fans_num_str'] = number_format2($user['fans_num']);
        $user['like_num_str'] = number_format2($like_num);
        $userTask = new User_task();
        $isComplete = $userTask->getUserTaskStatus(USERID);
        $photoWall = new UserPhotoWall();
        $photoWallImage = $photoWall->getlist();
        $user['isComplete'] = $isComplete;
        $user['photo_wall_image'] = $photoWallImage['image']?: [];
        $user['nickname'] = emoji_decode($user['nickname']);
        return $this->success($user, '获取成功');
    }

    public function saveAvatar()
    {
        $avatar = request()->param('avatar');
        if (empty($avatar)) return $this->jsonError('请上传头像');
        $params = array('avatar' => $avatar);
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        $result = $sdk->post('user/save_info', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '更新成功');
    }

    public function saveCover()
    {
        $cover = request()->param('cover');
        if (empty($cover)) return $this->jsonError('请上传封面');
        $params = array('cover' => $cover);
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        $result = $sdk->post('user/save_info', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '更新成功');
    }

    public function saveInfo()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        // var_dump($params);die;
        $result = $sdk->post('user/save_info', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '更新成功');
    }

    //修改或设置密码
    public function changePwd()
    {
        $password = request()->param('password');
        $confirm_password = request()->param('confirm_password');
        $code = request()->param('code');
        $sdk = new CoreSdk();
        $result = $sdk->post('user/change_pwd', array(
            'user_id' => USERID,
            'password' => $password,
            'confirm_password' => $confirm_password,
            'code' => $code,
            'old_password' => request()->param('old_password')
        ));
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '修改密码成功');
    }

    //切换设置状态
    public function switchStatus()
    {
        $params = request()->param();
        if (empty($params)) return $this->jsonError('请选择设置项');
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        $result = $sdk->post('user/switch_status', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result);
    }

    //绑定手机号
    public function bindPhone()
    {
        $params = request()->param();
        $sdk = new CoreSdk();
        $phone = $params['phone'];
        $code = $params['code'];
        $phoneCode = $params['phone_code'];
        if (empty($phone) || !validate_regex($phone, 'phone')) return $this->jsonError('手机号不正确');
        if (empty($code) || !validate_regex($code, '/\d{6}/')) return $this->jsonError('验证码不正确');
        $result = $sdk->post('user/bind_phone', array(
            'phone_code' => $phoneCode,
            'phone' => $phone,
            'code' => $code,
            'user_id' => USERID
        ));
        if (!$result) return $this->jsonError($sdk->getError());
        DsSession::set('user.phone', $phone);//更新会话中的用户手机号
        return $this->success($result, '更换手机号成功');
    }

    //我的粉丝贡献榜
    public function getContrRank()
    {
        $params = request()->param();
        $USERID = isset($params['user_id']) && !empty($params['user_id']) ? $params['user_id'] : USERID;
        $rank = new Rank();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $get = array(
            'interval' => $params['interval'],
            'order' => 'desc'
        );
        $get['name'] = "contr:total:{$USERID}";
        $get['numkeys'] = ["contr:real:{$USERID}", "contr:isvirtual:{$USERID}"];
        $get['period'] = 180;
        $result = $rank->getList($get, $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$USERID]
        ));
        return $this->success($result, '获取成功');
    }

    //绑定第三方账号
    public function bindThird()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        $type = $params['type'];
        $openid = $params['openid'];
        if (!enum_in($type, 'third_type')) return $this->jsonError('第三方平台不支持');
        if (empty($openid)) return $this->jsonError('缺少openid');
        $result = $sdk->post('user/bind_third', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        DsSession::set("user.bind_{$type}", '1');
        $name = enum_attr('third_type', $type, 'name');
        return $this->success(array(
            'id' => $result['id'],
            "bind_{$type}" => '1'
        ), "绑定{$name}成功");
    }

    public function savePwd()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        $result = $sdk->post('user/save_pwd', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '设置成功');
    }

    //苹果收据统一核销接口
    public function appleWriteOff()
    {
        $params = request()->param();
        $appleRec = new AppleReceipt();
        $result = $appleRec->writeOff(USERID, $params['receipt'], [
            'v' => APP_V,
            'client_ip' => get_client_ip()
        ]);
        if (!$result) {
            $error = $appleRec->getError();
            return $this->jsonError('核销失败' . (string)$error);
        }

        return $this->success($result, '核销成功');
    }

    //下次修改昵称时间
    public function nextRenickTime()
    {
        $result = Db::name('user')->where(['user_id' => USERID])->field('user_id,last_renick_time,nickname')->find();

        $lastRenickTime = ($result && $result['last_renick_time']) ? $result['last_renick_time'] : 0;
        $renickLimitTime = config('app.app_setting.renick_limit_time');
        $now = time();
        $diff = ($lastRenickTime + $renickLimitTime) - $now;
        $diff = $diff < 0 ? 0 : $diff;
        $day = time_str($diff, 'd');
        $str = $diff ? "距离下次更改昵称还需等待{$day}" : '昵称代表了您在平台形象，30天内仅限修改一次';
        $dateStr = date('Y-m-d', $diff ? ($lastRenickTime + $renickLimitTime) : time());
        return $this->success([
            'tip' => '温馨提示：' . $str,
            'date' => $dateStr,
            'limit' => $diff
        ], '查询成功');
    }

    public function selectProduct()
    {
        $params = request()->param();
        if (empty($params['id'])) return $this->jsonError('请选择产品');
        if (empty($params['type'])) return $this->jsonError('类型不存在');
        if (!in_array($params['type'], ['vip', 'recharge'])) return $this->jsonError('类型不存在');
        $ip = get_client_ip();
        $data = [
            'user_id' => USERID,
            'app_v' => APP_V,
            'meid' => APP_MEID,
            'type' => strtolower($params['type']),
            'product_id' => $params['id'],
            'client_ip' => $ip ? $ip : '',
            'create_time' => time()
        ];
        $data = Db::name('select_product_log')->insertGetId($data);
        if (!$data) return $this->jsonError('记录失败');
        return $this->success($data, '记录成功');
    }

    //获取用户动态
    public function getUserDynamic()
    {
        $params = request()->param();

        $user_id = $params['user_id'];
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        $live = [(object)[]];

        $liveDomain = new Lists();
        $myLive = $liveDomain->getRoomByUserId($user_id);
        if (!empty($myLive)) $live = $liveDomain->initializeLive([$myLive]);

        $Film = new \app\api\service\Video();
        $rs = Db::name('video')->where(['user_id' => $user_id])->order('id desc')->limit($offset, $length)->select();
        !empty($rs) && $rs = $Film->initializeFilm($rs, \app\common\service\Video::$allow_fields['user_dynamic']);

        return $this->success(['live' => $live[0], 'film' => $rs]);

    }

    /**
     * 开启青少年模式
     * @return \think\response\Json
     */
    public function setTeenagerPass()
    {
        $teenagerOpen = config("site.teenager_model_switch");
        if($teenagerOpen == 0){
            return $this->jsonError("青少年模式未开启");
        }
        $params = request()->param();
        $pass = $params['password'];
        $userId = USERID;
        $redis = RedisClient::getInstance();
        $status = $redis->set('teenager-password:' . $userId, $pass);
        if(!$status){
            return $this->jsonError("密码设置失败");
        }
        $user = new \app\admin\service\User();
        $user->updateData($userId, ["teenager_model_open" => 1]);
        return $this->jsonSuccess(1, "设置成功");
    }

    /**
     * 解除青少年模式
     * @return \think\response\Json
     */
    public function releaseTeenagerModel()
    {
        $params = request()->param();
        $pass = $params['password'];
        $userId = USERID;
        $redis = RedisClient::getInstance();
        $oriPass = $redis->get('teenager-password:' . $userId);
        if($oriPass != $pass){
            return $this->jsonError("密码错误");
        }
        $status = $redis->del('teenager-password:' . $userId);
        if(!$status){
            return $this->jsonError("解除青少年模式失败");
        }
        $user = new \app\admin\service\User();
        $user->updateData($userId, ["teenager_model_open" => 0]);
        return $this->jsonSuccess(0, "解除青少年模式成功");
    }

    public function confirmTeenCode()
    {
        $params = request()->param();
        $phone = $params['phone'];
        $phoneCode = $params['phone_code'];
        $code = $params['code'];
        if (empty($code)) return json_error(make_error('短信验证码不能为空'));
        $smsCodeModel = new SmsCode();
        $result       = $smsCodeModel->checkCode('reset_pwd', $phone, $code, $phoneCode);
        if (!$result) return json_error($smsCodeModel->getError());
        return $this->jsonSuccess(0, "验证成功");
    }

    /**
     * 注销用户
     * @return \think\response\Json
     */
    public function logOffUser()
    {
        $params = request()->param();
        $phone = $params['phone'];
        $phoneCode = $params['phone_code'];
        $code = $params['code'];
        if (empty($code)) return $this->jsonError(make_error('短信验证码不能为空'));

        $smsCodeModel = new SmsCode();
        $result = $smsCodeModel->checkCode('user_logoff', $phone, $code, $phoneCode);
        if (!$result) return $this->jsonError($smsCodeModel->getError());

        $userId = USERID;
        $user = new \app\admin\service\User();
        $status = $user->deleteUser($userId);
        if(!$status){
            return $this->jsonError("用户注销失败");
        }
        $this->user = null;
        DsSession::set('user', null);
        return $this->jsonSuccess("", "用户注销成功");
    }

    /***
     * 搜索用户
     */
    public function searchUser()
    {
        $params = input();
        $type = 'user';
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $userWhere = [
            'offset' => $offset,
            'length' => $length,
            'keyword' => $params['keyword'],
            'self_uid' => USERID
        ];
        $sdk = new CoreSdk();
        $result = $sdk->post('user/search', $userWhere);
        return $this->success($result ? $result : [], '获取成功');
    }

    /**
     * 用户保存同步标签
     */
    public function saveUserImpression()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $sdk = new CoreSdk();
        $result = $sdk->post('user/save_user_impression', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '设置成功');
    }

    /**
     * 获取所有同步标签
     */
    public function getAllImpression()
    {
        $all_impression = Db::name('impression')->field('id,name,color')->where(['status' => 1, 'type' => 1])->select();
        return $this->success($all_impression ? $all_impression :[], '获取成功');
    }

    /**
     * 用户获取自己的同步标签
     */
    public function getUserImpression()
    {
        $impression = Db::name('user_my_impression')->field('im.id,name,color')->alias('umi')->join('impression im','umi.impression_id = im.id')->where(['umi.user_id' => USERID])->select();
        return $this->success($impression ? $impression :[], '获取成功');
    }

    /**
     * 获取用户是否在直播
     */
    public function getIsLive()
    {
        $params = request()->param();
        $params['user_id'] = isset($params['user_id']) ? $params['user_id'] : USERID;
        $sdk = new CoreSdk();
        $result = $sdk->post('user/get_is_live', $params);
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result);
    }

    /**
     * 获取用户的粉丝贡献榜的日、周、月榜的第一个
     */
    public function getOneContrRank()
    {
        $params = request()->param();
        $USERID = isset($params['user_id']) && !empty($params['user_id']) ? $params['user_id'] : USERID;
        $rank = new Rank();
        $offset = 0;
        $length = 1;
        $todayGet = array('interval' => '', 'order' => 'desc', 'name' => "contr:total:{$USERID}", 'numkeys' => ["contr:real:{$USERID}", "contr:isvirtual:{$USERID}"], 'period' => 180);
        $weekGet = array('interval' => 'w', 'order' => 'desc', 'name' => "contr:total:{$USERID}", 'numkeys' => ["contr:real:{$USERID}", "contr:isvirtual:{$USERID}"], 'period' => 180);
        $monthGet = array('interval' => 'm', 'order' => 'desc', 'name' => "contr:total:{$USERID}", 'numkeys' => ["contr:real:{$USERID}", "contr:isvirtual:{$USERID}"], 'period' => 180);
        $todeyResult = $rank->getList($todayGet, $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$USERID]
        ));
        $weekResult = $rank->getList($weekGet, $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$USERID]
        ));
        $monthkResult = $rank->getList($monthGet, $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$USERID]
        ));
        $result = [
            'todey_result_status' => $todeyResult['list'] ? 1 : 0,
            'week_result_status' => $weekResult['list'] ? 1 : 0,
            'monthk_result_status' => $monthkResult['list'] ? 1 : 0,
            'todey_result' => $todeyResult['list'] ? $todeyResult['list'][0] : (object)[],
            'week_result' => $weekResult['list'] ? $weekResult['list'][0] : (object)[],
            'monthk_result' => $monthkResult['list'] ? $monthkResult['list'][0] : (object)[],
        ];
        return $this->success($result, '获取成功');
    }

    /**
     * 获取自己收取的礼物
     */
    public function getGift()
    {
        $params = request()->param();
        $params['user_id'] = $params['user_id'] ? $params['user_id'] : USERID;
        $params['offset'] = empty($params['offset']) ? 0 : $params['offset'];
        $params['length'] = empty($params['length']) ? PAGE_LIMIT : $params['length'];

        if (empty($params['user_id']))  return $this->jsonError('非法操作');

        $userService = new \app\common\service\User;
        $user = $userService->getUser($params['user_id']);
        if (empty($user)) return $this->jsonError('用户不存在');
        $gift = new GiftLog();
        $result = $gift->getList($params);
        return $this->success($result);
    }
}