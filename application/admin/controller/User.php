<?php

namespace app\admin\controller;

use app\admin\service\RechargeLog;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use think\Db;
use think\facade\Request;
use bxkj_module\service\UserRedis;
use bxkj_module\service\DsIM;
use bxkj_common\RedisClient;

class User extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $distributeStatus = config('giftdistribute.is_open');
        $distributeName = config('giftdistribute.name');
        $this->assign('distribute_status',$distributeStatus);
        $this->assign('distribute_name',$distributeName);
    }

    public function home()
    {
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:user:select');
        $userService = new \app\admin\service\User();
        $get = input();
        $total = $userService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $userService = new \app\admin\service\User();
        $result = $userService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:user:select');
        $userService = new \app\admin\service\User();
        $get = input();
        $source = '';
        if ($get['source'] == 'video_user') {
            $redis = RedisClient::getInstance();
            $uids = $redis->sUnion('video_user:one', 'video_user:another');
            $get['uidsoff'] = $uids ? $uids : [];
            $source = 'video_user';
        }

        $total = $userService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('source', $source);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:user:change_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $disable_length = input('disable_length');
        $disable_desc = input('disable_desc');
        $userService = new \app\admin\service\User();
        $num = $userService->changeStatus($ids, $status, null, $disable_length, $disable_desc, AID);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_live_status()
    {
        $this->checkAuth('admin:user:change_live_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $live_status = input('live_status');
        if (!in_array($live_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changeLiveStatus($ids, $live_status);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改直播状态：".($live_status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_film_status()
    {
        $this->checkAuth('admin:user:change_upload_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $film_status = input('film_status');
        if (!in_array($film_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changeFilmStatus($ids, $film_status);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改视频状态：".($film_status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_comment_status()
    {
        $this->checkAuth('admin:user:change_comment_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $comment_status = input('comment_status');
        if (!in_array($comment_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changeCommentStatus($ids, $comment_status);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改评论状态：".($comment_status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_contact_status()
    {
        $this->checkAuth('admin:user:change_contact_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $contact_status = input('contact_status');
        if (!in_array($contact_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changeContactStatus($ids, $contact_status);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改私信状态：".($contact_status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function detail()
    {
        $this->checkAuth('admin:user:select');
        $userId = input('user_id');
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($userId);
        if (empty($user)) $this->error('用户不存在');
        $this->assign('user', $user);
        $tmpArr = enum_array('recharge_pay_methods');
        $tmpArr2 = enum_array('deduction_methods');
        $arr = [];
        foreach ($tmpArr as $item) {
            if (check_auth($item['rules'], AID)) {
                $arr[] = $item;
            }
        }
        $arr2 = [];
        foreach ($tmpArr2 as $item2) {
            if (check_auth($item2['rules'], AID)) {
                $arr2[] = $item2;
            }
        }
        $this->assign('recharge_pay_methods', $arr);
        $this->assign('deduction_methods', $arr2);
        $startLogs = Db::name('start_log')->where('user_id', $userId)->limit(5)->order(['start_time' => 'desc'])->select();
        $loginLogs = Db::name('login_log')->where('user_id', $userId)->limit(5)->order(['login_time' => 'desc'])->select();
        $networkLogs = Db::name('network_status')->where('user_id', $userId)->limit(10)->order(['create_time' => 'desc'])->select();
        $userPackages = Db::name('user_package')->where('user_id', $userId)->limit(10)->order(['create_time' => 'desc'])->select();
        $this->assign('start_logs', $startLogs);
        $this->assign('login_logs', $loginLogs);
        $this->assign('network_logs', $networkLogs);
        $this->assign('user_packages', $userPackages);
        return $this->fetch();
    }

    public function refresh_redis()
    {
        $this->checkAuth('admin:user:select');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $userService = new \app\admin\service\User();
        $userService->refreshRedis($userId);
        $this->success('刷新成功');
    }

    public function change_pay_status()
    {
        $this->checkAuth('admin:user:change_pay_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $pay_status = input('pay_status');
        if (!in_array($pay_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changePayStatus($ids, $pay_status);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改支付状态：".($pay_status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_millet_status()
    {
        $this->checkAuth('admin:user:change_millet_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $millet_status = input('millet_status');
        if (!in_array($millet_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changeMilletStatus($ids, $millet_status);
        if (!$num) $this->error('切换状态失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.implode(",", $ids)."<br>修改提现状态：".($millet_status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function clear_sign()
    {
        $this->checkAuth('admin:user:clear_sign');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $userService = new \app\admin\service\User();
        $num = $userService->clearSign($userId);
        if (!$num) $this->error('清除失败');
        alog("user.user.edit", '编辑用户 USER_ID：'.$userId."<br>清除用户签名");
        $this->success('清除成功');
    }

    public function recharge()
    {
        $post = Request::post();
        $pay_method = $post['pay_method'];
        if (empty($pay_method)) $this->error('请选择付款方式');
        $pay_method_config = config("enum.recharge_pay_methods");
        if (empty($pay_method_config)) $this->error('付款方式不存在');
        $rules = enum_attr('recharge_pay_methods', $pay_method, 'rules');
        $this->checkAuth($rules);
        if (empty($post['user_id'])) $this->error('请选择用户');
        $post['rec_type'] = 'user';
        $post['rec_account'] = $post['user_id'];
        unset($post['user_id']);
        if ($pay_method != 'isvirtual') {
            $capital_fee = $post['capital_fee'];
            if (empty($capital_fee)) $this->error('请输入大写金额');
            $capital_fee = rtrim($capital_fee, '整') . '整';
            $capital_fee2 = number_2_rmb($post['total_fee']);
            if ($capital_fee != $capital_fee2) $this->error('请检查大写金额填写是否正确');
        }
        $rechargeLog = new RechargeLog();
        $result = $rechargeLog->add($post);
        if (!$result) $this->error($rechargeLog->getError());
        $str = $post['pay_method'] == 'isvirtual' ? '虚拟号充值成功' : '提交成功，请等待审核';
        $this->success($str);
    }

    public function deduction_recharge()
    {
        $post = Request::post();
        $pay_method_config = config("enum.deduction_methods");
        if (empty($pay_method_config))  return  $this->error('扣款方式不存在');
        if (empty($post['user_id']))  return  $this->error('请选择用户');
        $num = Db::name('bean')->where(array('user_id' => $post['user_id']))->find();
        if (empty($num)) return $this->error('未知错误');
        if (empty($post['bean_num'])) return $this->error('数量错误');
        if ($post['bean_num'] - $num['bean'] > 0) {
            return $this->error('金额错误');
        }
        $update['bean'] = ($num['bean'] - $post['bean_num']);
        $update['total_bean'] = ($num['total_bean'] - $post['bean_num']);
        $update['last_change_time'] = time();
        $res = Db::name('bean')->where(array('id' => $num['id']))->update($update);
        $userService = new \bxkj_module\service\User();
        $userService->updateRedis($post['user_id'], $update);
        alog("user.user.edit", '扣除用户 USER_ID：'.$post['user_id']."<br>钻石" . $post['bean_num']);
        $this->success('扣除成功');
    }

    public function remark()
    {
        $this->checkAuth('admin:user:remark');
        $userId = input('user_id');
        if (Request::isGet()) {
            $user = Db::name('user')->where(['delete_time' => null, 'user_id' => $userId])->field('user_id,nickname,remark_name,remark')->find();
            if (empty($user)) $this->error('用户不存在');
            $this->success('获取成功', [
                'user_id' => $user['user_id'],
                'remark_name' => $user['remark_name'] ? $user['remark_name'] : $user['nickname'],
                'remark' => $user['remark']
            ]);
        } else {
            $remarkName = input('remark_name');
            $remark = input('remark');
            $num = Db::name('user')->where(['user_id' => $userId])->update([
                'remark_name' => $remarkName ? $remarkName : '',
                'remark' => $remark ? $remark : ''
            ]);
            if (!$num) $this->error('保存失败');
            alog("user.user.edit", '编辑用户 USER_ID：'.$userId."<br>标记用户");
            $this->success('保存成功');
        }
    }

    public function get_region_name($id)
    {
        if (empty($id)) return '';
        $name = Db::name('region')->where(array('id' => $id))->value('name');
        return $name ? $name : '';
    }

    public function update()
    {
        $this->checkAuth('admin:user:update');
        $userId = input('user_id');
        if (Request::isGet()) {
            $user = Db::name('user')->where(['delete_time' => null, 'user_id' => $userId])->find();
            if (empty($user)) $this->error('用户不存在');
            $user['birthday'] = $user['birthday'] ? date("Y-m-d", $user['birthday']) : '';
            $user['area_id'] = implode('-', [$user['province_id'], $user['city_id'], $user['district_id']]);
            $user['area_name'] = implode('-', [$this->get_region_name($user['province_id']), $this->get_region_name($user['city_id']), $this->get_region_name($user['district_id'])]);
            $this->success('获取成功', $user);
        } else {
            $avatar = input('avatar');
            $nickname = input('nickname');
            if (strstr($nickname, " ")) {
                $this->error('昵称不能有空格');
            }
            $gender = input('gender');
            $birthday = input('birthday');
            $sign = input('sign');
            $area_id = explode('-', input('area_id'));
            $update = [
                'avatar' => $avatar ? $avatar : '',
                'nickname' => $nickname ? $nickname : '',
                'gender' => $gender ? $gender : '0',
                'birthday' => $birthday ? strtotime($birthday) : '0',
                'province_id' => $area_id[0] ? $area_id[0] : '',
                'city_id' => $area_id[1] ? $area_id[1] : '',
                'district_id' => $area_id[2] ? $area_id[2] : '',
            ];
            if ($sign != '') {
                $update['sign'] = $sign;
            }
            $num = Db::name('user')->where(['user_id' => $userId])->update($update);
            UserRedis::updateData($userId, $update);

            $DsIM = new DsIM();
            $DsIM->updateUserData($userId);

            if (!$num) $this->error('保存失败');
            alog("user.user.edit", '编辑用户 USER_ID：'.$userId."<br>修改用户信息");
            $this->success('保存成功');
        }
    }

    public function reg()
    {
        $this->checkAuth('admin:user:add');
        if (Request::isGet()) {
            $this->lastSendTime();
            return $this->fetch();
        } else {
            $mode = input('mode');
            $phone = input('phone');
            $code = input('code');
            $password = input('password');
            $confirm_password = input('confirm_password');
            if (empty($password)) $this->error('请设置密码');
            if ($password != $confirm_password) $this->error('密码两次输入不一致');
            ClientInfo::refreshByUserAgent(null, ['client_type' => 'web', 'client_object' => 'erp']);
            $data = array(
                'mode' => $mode,
                'password' => $password,
                'promoter_uid' => input('promoter_uid'),
                'client_seri' => ClientInfo::encode()
            );
            if ($mode == 'normal') {
                if (empty($phone)) $this->error('手机号不能为空');
                // if (empty($code)) $this->error('验证码不能为空');
                $data['phone'] = $phone;
                $data['code'] = $code;
            } else {
                if (!empty($phone)) {
                    // if (empty($code)) $this->error('验证码不能为空');
                    $data['phone'] = $phone;
                    $data['code'] = $code ? $code : '';
                } else {
                    $new_phone = '122' . get_ucode(8);
                    $user = Db::name('user')->where(['phone' => $new_phone])->field('user_id')->find();
                    while (!empty($user)) {
                        $new_phone = '122' . get_ucode(8);
                        $user = Db::name('user')->where(['phone' => $new_phone])->field('user_id')->find();
                    }

                    $data['phone'] = $new_phone;
                }
            }
            $core = new CoreSdk();
            $user = $core->post('user/create_by_phone', $data);
            if (!$user) $this->error($core->getError());
            alog("user.user.add", '新增用户 USER_ID：'.$user['user_id']);
            $this->success('注册成功', $user, url('user/detail', ['user_id' => $user['user_id']]));
        }
    }

    public function reset_nickname()
    {
        $this->checkAuth('admin:user:reset_nickname');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $update = ['nickname' => '已重置', 'last_renick_time' => null];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置昵称失败');
        \app\admin\service\User::updateRedis($userId, $update);
        alog("user.user.edit", '编辑用户 USER_ID：'.$userId ."<br>重置用户昵称");
        $this->success('重置昵称成功');
    }

    public static function createRandStr($len, $chars = null)
    {
        if (!$chars) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        }
        return substr(str_shuffle(str_repeat($chars, rand(5, 8))), 0, $len);
    }

    public function reset_password()
    {
        $this->checkAuth('admin:user:reset_password');
        $userId = input('user_id');
        $user = Db::name('user')->where(['delete_time' => null, 'user_id' => $userId])->find();
        if (empty($user)) $this->error('请选择用户');
        $code = self::createRandStr(6);
        $data['isset_pwd'] = '1';
        $data['salt'] = sha1(uniqid() . get_ucode(8));
        $data['password'] = sha1($code . $data['salt']);
        $data['change_pwd_time'] = time();
        $num = Db::name('user')->where(['user_id' => $userId])->update($data);
        if (!$num) $this->error('重置密码失败');
        $sdk = new CoreSdk();
        $sdk->post('common/send_sms_code', array(
            'phone' => $user['phone'],
            'scene' => 'admin_reset_pwd',
            'phone_code' => '86',
            'params' => ['user_id' => $userId, 'password' => $code]
        ));
        UserRedis::updateData($userId, $data);
        alog("user.user.edit", '编辑用户 USER_ID：'.$userId ."<br>重置用户密码");
        $this->success('新密码：' . $code);
    }

    public function reset_avatar()
    {
        $this->checkAuth('admin:user:reset_nickname');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $update = ['avatar' => img_url('', '', 'avatar')];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置头像失败');
        \app\admin\service\User::updateRedis($userId, $update);
        alog("user.user.edit", '编辑用户 USER_ID：'.$userId ."重置用户头像");
        $this->success('重置头像成功');
    }

    public function reset_cover()
    {
        $this->checkAuth('admin:user:reset_nickname');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $update = ['cover' => img_url('', '', 'cover')];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置封面失败');
        \app\admin\service\User::updateRedis($userId, $update);
        alog("user.user.edit", '编辑用户 USER_ID：'.$userId ."<br>重置用户封面");
        $this->success('重置封面成功');
    }

    public function reset_rename_time()
    {
        $this->checkAuth('admin:user:reset_rename_time');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $update = ['last_renick_time' => null];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置限制时间失败');
        \app\admin\service\User::updateRedis($userId, $update);
        alog("user.user.edit", '编辑用户 USER_ID：'.$userId ."<br>重置用户限制时间");
        $this->success('重置限制时间成功');
    }

    public function agent_list()
    {
        $this->checkAuth('admin:user:select');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $user = Db::name('user')->where(['user_id' => $userId])->find();
        if (empty($user)) $this->error('用户不存在');
        $this->assign('_user', $user);
        $promotionService = new \app\admin\service\PromotionRelation();
        $get = input();
        $total = $promotionService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $promotionService->getList($get, $page->firstRow, $page->listRows);
        if (count($users)=='0') $this->error('请先分配'.config('app.agent_setting.agent_name'));
        $this->assign('_list', $users);
        return $this->fetch();
    }
    
    public function sbjin()
    {
        $sb = input('sb');
        if(empty($sb))$this->error('设备号不能为空');
        $info  = Db::name('sbjin')->where(['sb' => $sb])->find();
        if($info)$this->error('该设备号已禁用');
        
        $res = Db::name('sbjin')->insert(['sb'=>$sb]);
        if($res)$this->success('禁用成功');
        $this->error('禁用失败');
    }

}
