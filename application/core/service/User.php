<?php

namespace app\core\service;

use bxkj_common\ClientInfo;
use bxkj_common\KeywordCheck;
use bxkj_module\exception\ApiException;
use bxkj_module\service\DsIM;
use bxkj_module\service\UserRedis;
use think\Db;
use bxkj_common\RedisClient;
use bxkj_common\RabbitMqChannel;
use think\facade\Request;

class  User extends \bxkj_module\service\User
{

    //通过手机号注册
    public function createByPhone($inputData)
    {
        $site = config('site.');
        $code = $inputData['code'];
        $phoneCode = $inputData['phone_code'];
        $phone = $inputData['phone'];
        if ($site['invite_code'] == 2 && empty($inputData['invite_code'])) return $this->setError('邀请码不能为空');
        if (!empty($inputData['invite_code'])) {
            //表示必须要填写邀请码
            $invite_user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where(['invite_code' => $inputData['invite_code']])->find();
            if (empty($invite_user)) return $this->setError('推荐用户不存在');
            $data['pid'] = $invite_user['user_id'];
        }

        if (isset($code)) {
            if (empty($code)) return $this->setError('短信验证码不能为空');
            $smsCodeModel = new SmsCode();
            $result = $smsCodeModel->checkCode('reg', $phone, $code, $phoneCode);
            if (!$result) return $this->setError($smsCodeModel->getError());
        }

        $mode = empty($inputData['mode']) ? 'normal' : $inputData['mode'];
        if (!in_array($mode, ['normal', 'isvirtual'])) return $this->setError('账号模式不支持');
        if ($mode == 'normal') {
            unset($inputData['client_seri']);
            $data = $this->df->process('create@user', $inputData)->output();
            if ($data === false) return $this->setError($this->df->getError());
        } else {
            if (!empty($inputData['phone'])) {
                if (!validate_phone($inputData['phone'])) return $this->setError('手机号格式不正确');
            }
            if (!self::checkPassword($inputData['password'])) return false;
            $data['password'] = $inputData['password'];
            $data['phone'] = $inputData['phone'] ? $inputData['phone'] : '';
            $data['phone_code'] = $inputData['phone_code'] ? $inputData['phone_code'] : '86';
            $data['promoter_uid'] = $inputData['promoter_uid'] ? $inputData['promoter_uid'] : '';
            $data['agent_id'] = $inputData['agent_id'] ? $inputData['agent_id'] : '';
        }

        if (!empty($phone)) {
            $num = Db::name('user')->where(array('phone' => $phone, 'delete_time' => NULL))->count();
            if ($num > 0) return $this->setError('手机号已注册');
        }
        $data['mode'] = $mode;
        $data['reg_way'] = 'phone';
        $data['reg_meid'] = ClientInfo::get('meid');
        $this->attachRegInfo($inputData, $data);
        $user = $this->insertUser($data);
        if (!$user) return $this->setError('注册失败');
        return $user;
    }

    //通过手机号注册(无需密码)
    public function createByPhone2($inputData)
    {
        $phone = $inputData['phone'];
        if (empty($phone)) return $this->setError('手机号不能为空');
        if (!validate_regex($phone, 'phone')) return $this->setError('手机号格式不正确');
        $where = array('phone' => $phone, 'delete_time' => NULL);
        $user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where($where)->find();
        if ($user) return $this->setError('手机号已存在');

        $site = config('site.');
        if ($site['invite_code'] == 2 && empty($inputData['invite_code'])) return $this->setError('邀请码不能为空');
        if (!empty($inputData['invite_code'])) {
            //表示必须要填写邀请码
            $invite_user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where(['invite_code' => $inputData['invite_code']])->find();
            if (empty($invite_user)) return $this->setError('推荐用户不存在');
            $data['pid'] = $invite_user['user_id'];
        }

        $data['phone'] = $phone;
        $data['phone_code'] = $inputData['phone_code'] ? $inputData['phone_code'] : '86';
        $data['reg_way'] = 'phone';
        $data['reg_meid'] = ClientInfo::get('meid');
        $this->attachRegInfo($inputData, $data);
        $user = $this->insertUser($data);
        if (!$user) return $this->setError('注册失败');
        return $user;
    }
    
    //通过账号密码注册
    public function createByAccount($inputData)
    {
        $username = $inputData['username'];
        $password = $inputData['password'];
        $uchp = self::checkUsername($username);
        if (is_error($uchp)) return $this->setError($uchp);
        $pchp = self::checkPassword($password);
        if (is_error($pchp)) return $this->setError($pchp);
        $site = config('site.');
        if ($site['invite_code'] == 2 && empty($inputData['invite_code'])) return $this->setError('邀请码不能为空');
        if (!empty($inputData['invite_code'])) {
            //表示必须要填写邀请码
            $invite_user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where(['invite_code' => $inputData['invite_code']])->find();
            if (empty($invite_user)) return $this->setError('推荐用户不存在');
            $data['pid'] = $invite_user['user_id'];
        }
        $data['username'] = $username;
        $salt = sha1(uniqid() . get_ucode(6, '1aA'));
        $data['salt'] = $salt;
        $data['password'] = sha1($password. $salt);
        $data['reg_meid'] = ClientInfo::get('meid');
        //直接实名成功
        $data['verified'] = 1;
        $this->attachRegInfo($inputData, $data);
        $user = $this->insertUser($data);
        if (!$user) return $this->setError('注册失败');
        return $user;
    }
    
    //通过设备注册
    public function createByDevice($inputData)
    {
        $meid = $inputData['meid'];
        $os = strtolower($inputData['os']);
        if (empty($meid)) return $this->setError('设备码不能为空');
        if (empty($os)) return $this->setError('系统标识符不能为空');
        $bind = Db::name('user_third')->where(['type' => 'device', 'appid' => $os, 'openid' => $meid, 'status' => 'bind'])->find();
        if ($bind) return $this->setError('设备码已绑定账号');
        $data['reg_way'] = 'device';
        $data['reg_meid'] = $meid;
        self::startTrans();
        $this->attachRegInfo($inputData, $data);
        $user = $this->insertUser($data);
        if (!$user) {
            self::rollback();
            return $this->setError('注册失败');
        }
        $bindData = array(
            'openid' => $meid,
            'type' => 'device',
            'status' => 'bind',
            'user_id' => $user['user_id'],
            'uuid' => $meid,
            'bind_time' => time(),
            'invite_code' => '',
            'appid' => $os
        );
        $bindId = Db::name('user_third')->insertGetId($bindData);
        if (!$bindId) {
            self::rollback();
            return $this->setError('注册失败[02]');
        }
        self::commit();
        return $user;
    }

    public function manual_handling($user_id, $field, $image = '')
    {
        if (!is_array($field) && empty($image)) return false;
        $obj = is_array($field) ? $field : array($field => $image);
        $rs = Db::name('user_data_deal')->insertGetId(array(
            'user_id' => $user_id,
            'audit_status' => '-1',
            'create_time' => time(),
            'data' => json_encode($obj)
        ));
        return $rs;
    }

    //通过第三方账号注册
    public function createByThird($inputData)
    {
        $type = $inputData['type'];
        if (!enum_has('third_type', $type)) return $this->setError('不支持的第三方平台');
        $appid = !empty($inputData['appid']) ? $inputData['appid'] : enum_attr('third_type', $type, 'appid');
        $openid = $inputData['openid'];
        $uuid = $inputData['uuid'];
        if (empty($openid) || empty($uuid)) return $this->setError('OPENID不能为空');

        $phone = $inputData['phone'];
        $phoneCode = $inputData['phone_code'];
        $code = $inputData['code'];
        $redis = RedisClient::getInstance();
        if (isset($code)) {
            $get = $redis->get('mob_phone' . $phone);
            if (empty($get)) {
                if (empty($code)) return $this->setError('短信验证码不能为空~');
                $smsCodeModel = new SmsCode();
                $result = $smsCodeModel->checkCode('third_bind', $phone, $code, $phoneCode);
                if (!$result) return $this->setError($smsCodeModel->getError());
            }
        }

        $chm = self::checkPhone($phone, false);
        if (is_error($chm)) return $this->setError($chm);
        self::startTrans();
        $where = array('phone' => $phone, 'delete_time' => null);
        $user = Db::name('user')->where($where)->find();
        $manualId = null;
        $site = config('site.');
        $type_reg = isset($site['register_type']) ? $site['register_type'] : 0;
        if ($type_reg == 1 && !empty($user)) return $this->setError('该手机号码已经存在,请直接登录吧');

        if (!empty($user)) {
            $bind = Db::name('user_third')->where(array(
                'user_id' => $user['user_id'],
                'type' => $type,
                'status' => 'bind',
                //'appid' => $appid, //废弃appid 20180705同一类的账号不再区分APP，引入UUID概念
            ))->find();
            //解绑老的
            if ($bind) {
                $unNum = Db::name('user_third')->where('id', $bind['id'])->update(array(
                    'status' => 'unbind',
                    'unbind_time' => time()
                ));
                if (!$unNum) {
                    self::rollback();
                    return $this->setError('绑定失败[01]');
                }
            }
        } else {
            if ($type_reg == 1) {
                $resBind = Db::name('user_third')->where(['uuid' => $uuid, 'status' => 'unbind'])->find();
                if ($resBind) {
                    $inputData['invite_code'] = !empty($resBind['invite_code']) ? $resBind['invite_code'] : $inputData['invite_code'];
                }
            }

            if ($site['invite_code'] == 2 && empty($inputData['invite_code'])) return $this->setError('邀请码不能为空');
            if (!empty($inputData['invite_code'])) {
                //表示必须要填写邀请码
                $invite_user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where(['invite_code' => $inputData['invite_code']])->find();
                if (empty($invite_user)) return $this->setError('推荐用户不存在');
                $userData['pid'] = $invite_user['user_id'];
            }

            $avatar = $this->handlerAvatar($inputData['avatar']);
            $userData['nickname'] = $inputData['nickname'];
            $userData['avatar'] = $inputData['avatar'];
            if (!isset($userData['avatar'])) {
                $avatars = config('upload.reg_avatar');
                $userData['avatar'] = $avatars[mt_rand(0, count($avatars) - 1)];
            }

            $userData['gender'] = isset($inputData['gender']) ? $inputData['gender'] : '0';
            $userData['phone'] = $phone;
            $userData['phone_code'] = $phoneCode ? $phoneCode : '86';
            $userData['reg_way'] = 'third_' . $type;
            $userData['reg_meid'] = ClientInfo::get('meid');
            $userData['create_time'] = time();
            $this->attachRegInfo($inputData, $userData);
            $user = $this->insertUser($userData);
            if (!$user) {
                self::rollback();
                return $this->setError('绑定失败[02]');
            }
            //第三方登录头像是否违规检测
            $manualId = $this->manual_handling($user['user_id'], 'avatar', $avatar);
        }
        if (!empty($uuid)) {
            $num = Db::name('user_third')->where(['uuid' => $uuid, 'status' => 'bind', 'type' => $type])->count();
            if ($num > 0) {
                self::rollback();
                return $this->setError('绑定失败，已绑定其他账号[03]');
            }
        }

        if ($type_reg == 1 && !empty($resBind)) {
            $unNum = Db::name('user_third')->where('id', $resBind['id'])->update(array(
                'status' => 'bind',
                'type' => $type,
                'user_id' => $user['user_id'],
                'bind_time' => time()
            ));
            if (!$unNum) {
                self::rollback();
                return $this->setError('绑定失败[01]');
            }
        } else {
            $bindData = array(
                'openid' => $openid,
                'type' => $type,
                'status' => 'bind',
                'user_id' => $user['user_id'],
                'uuid' => $uuid ? $uuid : '',
                'bind_time' => time(),
                'invite_code' => '',
                'appid' => $appid
            );
            $bindId = Db::name('user_third')->insertGetId($bindData);
            if (!$bindId) {
                self::rollback();
                return $this->setError('绑定失败[04]');
            }
        }

        $updateData = [];
        $updateData["bind_{$type}"] = '1';
        $this->updateRedis($user['user_id'], $updateData);
        self::commit();
        if ($manualId) {
            //对接rabbitMQ
            try {
                $rabbitChannel = new RabbitMqChannel(['user.user_data_deal']);
                $rabbitChannel->exchange('main')->sendOnce('user.user_data_deal.audit', ['id' => $manualId]);
            } catch (\Exception $e) {

            }
        }

        $redis->del('mob_phone' . $phone);
        return $user;
    }

    //将输入数据按照严格的数据格式处理
    private function attachRegInfo($inputData, &$data)
    {
        if (isset($inputData['agent_id'])) $data['agent_id'] = $inputData['agent_id'];
        if (isset($inputData['promoter_uid'])) $data['promoter_uid'] = $inputData['promoter_uid'];
        if (isset($inputData['anchor_uid'])) $data['anchor_uid'] = $inputData['anchor_uid'];
    }

    //插入一条用户数据
    protected function insertUser($userData)
    {
        self::startTrans();
        $agentInfo = [
            //'promoter_uid' => $userData['promoter_uid'],
            'promoter_uid' => $userData['pid'],
            'agent_id' => $userData['agent_id'],
            'anchor_uid' => $userData['anchor_uid']
        ];
        $userData['vip_expire'] = 0;
        $userData['isvirtual'] = ($userData['mode'] == 'isvirtual') ? '1' : '0';
        $defaultCredit = config('app.app_setting.default_credit_score');
        $userData['credit_score'] = (int)$defaultCredit;
        try {
            if (!isset($userData['user_id'])) $userData['user_id'] = self::generateUserId();
        } catch (\Exception $exception) {
            return make_error($exception->getMessage());
        }
        if (empty($userData['user_id'])) return make_error('UID未生成');
        if (!isset($userData['nickname']) || empty($userData['nickname'])) $userData['nickname'] = $this->nicknamePrefix . $userData['user_id'];
        if (!isset($userData['username'])) $userData['username'] = $this->usernamePrefix . $userData['user_id'];
        if (!isset($userData['avatar'])) {
            $avatars = config('upload.reg_avatar');
            $userData['avatar'] = $avatars[mt_rand(0, count($avatars) - 1)];
        }
        if (!isset($userData['level'])) $userData['level'] = 1;
        if (!isset($userData['sign'])) $userData['sign'] = $this->defaultSign;
        //对原文密码进行加密
        if (isset($userData['password']) && !isset($userData['salt'])) {
            $salt = sha1(uniqid() . get_ucode(6, '1aA'));
            $userData['salt'] = $salt;
            $userData['password'] = sha1($userData['password'] . $salt);
        }
        $userData['isset_pwd'] = $userData['password'] ? '1' : '0';
        $userData['status'] = isset($userData['status']) ? $userData['status'] : '1';
        $userData['create_time'] = isset($userData['create_time']) ? $userData['create_time'] : time();
        //废弃type和anchor_uid字段
        unset($userData['mode'], $userData['anchor_uid'], $userData['type'], $userData['promoter_uid'], $userData['agent_id']);
        $userData['reg_meid'] = $userData['reg_meid'] ? $userData['reg_meid'] : '';
        $invite_code = get_ucode(6, 'aA');
        $hasuser = Db::name('user')->where(['invite_code' => $invite_code])->find();
        while (!empty($hasuser)) {
            $invite_code = get_ucode(6, 'aA');
            $hasuser = Db::name('user')->where(['invite_code' => $invite_code])->find();
        }
        $userData['invite_code'] = $invite_code;
        $userData['member_level_name'] = '';
        $insRes = Db::name('user')->insert($userData);
        if (!$insRes) {
            self::rollback();
            return make_error('创建用户错误');
        }
        //创建账号
        $beanModel = new Bean();
        $result = $beanModel->createAccount($userData);
        if (!$result) {
            self::rollback();
            return make_error('创建帐户错误');
        }
        $userSetting = new \app\core\service\UserSetting();
        //创建用户设置
        $setRes = $userSetting->create($userData['user_id'], []);
        if (!$setRes) {
            self::rollback();
            return make_error('创建设置错误');
        }
        $handlerRes = $this->newUserAgentHandler($userData, $agentInfo);
        if (!$handlerRes) {
            self::rollback();
            return make_error('代理错误');
        }
        $res = $this->addLevel($userData, $agentInfo);
        self::commit();
        $userData['user_id'] = (string)$userData['user_id'];
        $this->insertUserAfter($userData);

        try {
            $rabbitChannel = new RabbitMqChannel(['user.distribute']);
            $rabbitChannel->exchange('main')->sendOnce('user.distribute.process', ['user_id' => $userData['user_id']]);
        } catch (\Exception $e) {

        }
        if (!empty($userData['pid'])) {
            finish_task($userData['pid'], 'inviteFriends', 1);
        }

        return $userData;
    }

    protected function insertUserAfter(&$userData)
    {
        //增加信用值
        if (!empty($userData['phone'])) {
            $userCreditLog = new UserCreditLog();
            $res = $userCreditLog->record('bind_phone', [
                'user_id' => $userData['user_id'],
                'phone_str' => str_hide($userData['phone'], 3, 2),
                'not_update_redis' => '1'
            ]);
            if ($res) $userData['credit_score'] = $res['credit_score'];
        }
        //关注小助手
        $followRes = $this->follow($userData['user_id'], 10000);
        if ($followRes) {
            $this->follow(10000, $userData['user_id']);
        }
    }

    /**
     * @param $uid 当前注册会员的id
     * 进行层级插入
     */
    protected function addLevel($user, $agentInfo)
    {
        $promoterUid = $agentInfo['promoter_uid'];
        if (!empty($promoterUid)) {
            $parentUser = Db::name('user')->where(['user_id' => $promoterUid, 'delete_time' => null])->find();
            $preuid = $parentUser['user_id'];
            $data = [];
            if (!empty($preuid)) {
                $level = 1;
                $data[] = ['parent_id' => $preuid, 'uid' => $user['user_id'], 'level' => $level, 'add_time' => time()];
                $rankRes = Db::name('user_rank')->where(['uid' => $parentUser['user_id']])->order('level', 'ASC')->select();
                if (!empty($rankRes)) {
                    foreach ($rankRes as $key => $value) {
                        $level = $level + 1;
                        $data[] = ['parent_id' => $value['parent_id'], 'uid' => $user['user_id'], 'level' => $level, 'add_time' => time()];
                    }
                }
            }
            if (!empty($data)) {
                $res = Db::name('user_rank')->insertAll($data);
            }
        }
    }

    protected function follow($userId, $followUid)
    {
        $nowTime = time();
        $followData = ['user_id' => $userId, 'follow_id' => $followUid, 'type' => 1, 'create_time' => $nowTime];
        $id = Db::name('follow')->insertGetId($followData);
        if ($id) {
            $redis = RedisClient::getInstance();
            $redis->zAdd("fans:{$followUid}", $nowTime, $userId);
            $redis->zAdd("follow:{$userId}", $nowTime, $followUid);
            $followInfo = Db::name('user')->where(['user_id' => $followUid])->field('user_id,fans_num')->find();
            $update1 = ['fans_num' => $followInfo['fans_num'] > 0 ? (int)$followInfo['fans_num'] : 0];
            $update1['fans_num']++;
            Db::name('user')->where(['user_id' => $followUid])->update($update1);
            $userInfo = Db::name('user')->where(['user_id' => $userId])->field('user_id,follow_num')->find();
            $update2 = ['follow_num' => $userInfo['follow_num'] > 0 ? (int)$userInfo['follow_num'] : 0];
            $update2['follow_num']++;
            Db::name('user')->where(['user_id' => $userId])->update($update2);
            UserRedis::updateData($followUid, $update1);
            UserRedis::updateData($userId, $update2);
        }
        return $id;
    }

    protected function newUserAgentHandler($user, $agentInfo)
    {
        $promoterUid = $agentInfo['promoter_uid']; //这个ID有可能是普通用户的ID，普通用户也可以分享邀请好友的
        $agentId = $agentInfo['agent_id'];//代理商ID

        if (!empty($promoterUid)) {
            //推广员邀请的
            $inviteUser = Db::name('user')->where(['user_id' => $promoterUid, 'delete_time' => null])->find();
            if ($inviteUser) {
                $invitePromoter = Db::name('promoter')->where(['user_id' => $promoterUid])->find();
                if ($invitePromoter) {
                    try {
                        $relationId = Db::name('promotion_relation')->insertGetId([
                            'user_id' => $user['user_id'],
                            'agent_id' => $invitePromoter['agent_id'],
                            'promoter_uid' => $invitePromoter['user_id'],
                            'create_time' => time()
                        ]);
                    } catch (\Exception $e) {
                        return false;
                    }

                    if (!$relationId) return false;

                    $update = [
                        'first_promoter_uid' => $invitePromoter['user_id'],
                        'first_agent_id' => $invitePromoter['agent_id']
                    ];
                    $num = Db::name('user')->where('user_id', $user['user_id'])->update($update);
                    if (!$num) return false;
                    Db::name('promoter')->where('user_id', $invitePromoter['user_id'])->setInc('client_num', 1);
                    //拉新
                    $kpi = new Kpi();
                    $kpi->fans($invitePromoter, $user, $agentInfo['anchor_uid']);
                }
                $inviteRecord = new InviteRecord();
                $inviteRecord->add($inviteUser['user_id'], $agentInfo['anchor_uid'], $user);
                $inviteUser['promoter'] = $invitePromoter;
                $this->regReward($user, $inviteUser);
            }
        } else if (!empty($agentId)) {
            $agent = Db::name('agent')->field('id,name')->where(['delete_time' => null, 'id' => $agentId])->find();
            if (!$agent) return false;
            $relationId = Db::name('promotion_relation')->insertGetId([
                'user_id' => $user['user_id'],
                'agent_id' => $agentId,
                'promoter_uid' => 0,
                'create_time' => time()
            ]);
            if (!$relationId) return false;
            $num = Db::name('user')->where('user_id', $user['user_id'])->update(['first_agent_id' => $agentId]);
            if (!$num) return false;
        }
        return true;
    }

    //注册奖励
    protected function regReward($userData, $invitePeople)
    {
        $rewardBean = config('app.product_setting.reg_bean');
        if ($invitePeople && $rewardBean > 0) {
            $bean = new Bean();
            $bean->reward([
                'user_id' => $userData['user_id'],
                'bean' => $rewardBean,
                'type' => 'reg_reward_bean'
            ]);
        }
    }

    //快捷登录
    public function quickLogin($inputData)
    {
        $phone = $inputData['phone'];
        $code = $inputData['code'];
        $phoneCode = $inputData['phone_code'];
        if (empty($phone)) return $this->setError('手机号不能为空');
        $where = array('phone' => $phone, 'delete_time' => NULL);
        $user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where($where)->find();

        //自动注册用户
        if (empty($user)) {
            $site = config('site.');
            if ($site['invite_code'] == 2 && empty($inputData['invite_code'])) return $this->setError('邀请码不能为空');
            if (!empty($inputData['invite_code'])) {
                //表示必须要填写邀请码
                $invite_user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where(['invite_code' => $inputData['invite_code']])->find();
                if (empty($invite_user)) return $this->setError('推荐用户不存在');
                $data['pid'] = $invite_user['user_id'];
            }

            if (isset($code)) {
                if (empty($code)) return $this->setError('短信验证码不能为空');
                $smsCodeModel = new SmsCode();
                $result = $smsCodeModel->checkCode('login', $phone, $code, $phoneCode);
                if (!$result) return $this->setError($smsCodeModel->getError());
            }

            if (preg_match('/^00(\d{4,14})$/', $phone)) return $this->setError('此类手机号不能进行快捷注册');
            $data['phone'] = $phone;
            $data['phone_code'] = $inputData['phone_code'] ? $inputData['phone_code'] : '86';
            $data['reg_way'] = 'quick';
            $data['reg_meid'] = ClientInfo::get('meid');
            $this->attachRegInfo($inputData, $data);
            $user = $this->insertUser($data);
            if (is_error($user)) return $this->setError($user->getMessage());
        } else {
            if (isset($code)) {
                if (empty($code)) return $this->setError('短信验证码不能为空');
                $smsCodeModel = new SmsCode();
                $result = $smsCodeModel->checkCode('login', $phone, $code, $phoneCode);
                if (!$result) return $this->setError($smsCodeModel->getError());
            }
        }
        $inputData['login_way'] = 'quick';
        return $this->loginUser($user['user_id'], $inputData);
    }

    //一键登录
    public function loginMob($inputData)
    {
        $phone = $inputData['phone'];
        if (empty($phone)) return $this->setError('手机号不能为空');
        $where = array('phone' => $phone, 'delete_time' => NULL);
        $user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where($where)->find();
        //自动注册用户
        if (empty($user)) {
            $site = config('site.');
            if ($site['invite_code'] == 2 && empty($inputData['invite_code'])) return $this->setError('邀请码不能为空', 10000);
            if (!empty($inputData['invite_code'])) {
                //表示必须要填写邀请码
                $invite_user = Db::name('user')->field('user_id,username,phone,status,password,salt')->where(['invite_code' => $inputData['invite_code']])->find();
                if (empty($invite_user)) return $this->setError('推荐用户不存在');
                $data['pid'] = $invite_user['user_id'];
            }
            if (preg_match('/^00(\d{4,14})$/', $phone)) return $this->setError('此类手机号不能进行快捷注册');
            $data['phone'] = $phone;
            $data['phone_code'] = $inputData['phone_code'] ? $inputData['phone_code'] : '86';
            $data['reg_way'] = 'login_mob';
            $data['reg_meid'] = ClientInfo::get('meid');

            $this->attachRegInfo($inputData, $data);
            $user = $this->insertUser($data);
            if (is_error($user)) return $this->setError($user->getMessage());
        }
        $inputData['login_way'] = 'login_mob';
        return $this->loginUser($user['user_id'], $inputData);
    }

    //用户名、密码登录
    public function login($inputData)
    {
        $phoneCode = $inputData['phone_code'];
        $username = $inputData['username'];
        $password = $inputData['password'];
        if (empty($password)) return $this->setError('密码不能为空');
        $isPhone = false;
        //检测是不是虚拟号
        $matches = [];
        $user = null;
        $fields = 'user_id,username,phone,status,password,salt,isset_pwd';
        if (preg_match('/^00(\d{4,14})$/', $username, $matches)) {
            $userId = $matches[1];
            $where = array('delete_time' => null);
            $where['isvirtual'] = '1';
            $where['user_id'] = $userId;
            $user = Db::name('user')->field($fields)->where($where)->find();
        }
        if (!$user) {
            $where = array('delete_time' => null);
            if (false) {//validate_regex($username, 'phone')
                $isPhone = true;
                $where['phone'] = $username;
            } else {
                $where['username'] = $username;
            }
            $user = Db::name('user')->field($fields)->where($where)->find();
        }
        if (empty($user)) return $this->setError('账号不存在',10002);
        if ($isPhone && $user['isset_pwd'] != '1') return $this->setError('尚未设置密码，请先设置密码', 10001);
        $vp = $this->verifyPassword($user, $password);
        if (!$vp) return $this->setError('账号或密码不正确');
        $inputData['login_way'] = 'login';
        return $this->loginUser($user['user_id'], $inputData);
    }

    //设备登录
    public function loginByDevice($inputData)
    {
        $meid = $inputData['meid'];
        $os = strtolower($inputData['os']);
        if (empty($meid)) return $this->setError('设备码不能为空');
        if (empty($os)) return $this->setError('系统标识符不能为空');
        $bind = Db::name('user_third')->where(['type' => 'device', 'appid' => $os, 'openid' => $meid, 'status' => 'bind'])->find();
        if (empty($bind)) return $this->setError('账号不存在');
        $inputData['login_way'] = 'device';
        return $this->loginUser($bind['user_id'], $inputData);
    }

    //通过第三方账号登录
    public function loginByThird($inputData)
    {
        $type = $inputData['type'];
        $openid = $inputData['openid'];
        $uuid = isset($inputData['uuid']) ? $inputData['uuid'] : '';
        if (empty($type)) return $this->setError('第三方平台标识符不能为空');
        if (empty($uuid) || empty($openid)) return $this->setError('OPENID不能为空');
        if (!enum_has('third_type', $type)) return $this->setError('不支持的第三方平台');
        //weixin 小程序有点特别
        $appid = !empty($inputData['appid']) ? $inputData['appid'] : enum_attr('third_type', $type, 'appid');
        $where1 = '';
        $where['type'] = $type;
        $where['status'] = 'bind';
        //优先使用全局UUID
        if (!empty($uuid)) {
            $where['uuid'] = $uuid;
        } else {
            $where['openid'] = $openid;
            $where['appid'] = $appid;
        }
        $bind = Db::name('user_third')->where($where)->find();
        if ($bind) {
            $inputData['login_way'] = 'third';
            return $this->loginUser($bind['user_id'], $inputData);
        }
        if ($type !='apple' && empty($inputData['nickname']) && empty($inputData['avatar'])) {
            return $this->setError('缺少用户资料');
        }
        $userData['unregistered'] = '1';
        return $userData;
    }

    //登录
    public function loginUser($userId, $clientInfo)
    {
        $user = $this->getUser($userId);
        if (empty($user)) return $this->setError('读取用户数据失败');
        ClientInfo::refreshByParams($clientInfo);
        if (!ClientInfo::isFull()) return $this->setError('缺少必要的客户端信息');
        if (!$this->checkUserStatus($user)) return false;
        $meid = ClientInfo::get('meid');
        $info = Db::name('sbjin')->where('sb',$meid)->find();
        if($info)return $this->setError('改设备已禁用,请更换设备重试');
        $device_type = ClientInfo::get('device_type');
        $exclusiveType = $device_type == 'pc' ? 'web' : $device_type;//独占类型
        $exclusive = sha1(uniqid() . get_ucode(6, '1aA'));//独占标识
        $stateKey = "loginstate:{$userId}";
        $redis = RedisClient::getInstance();
        $time = time();
        $loginState = $redis->hGetAll($stateKey);
        $eName = "exclusive_{$exclusiveType}";
        if (!$loginState || $loginState['create_time'] === false) {
            //首次创建登录态
            $loginState['create_time'] = $time;
            $loginState['update_v'] = 0;
        }
        $loginState['update_time'] = $time;
        //推送下线通知
        if (!empty($loginState[$eName])) {
            $this->pushOfflineNotice($userId, $loginState[$eName], $exclusive);
        }
        $loginState['status'] = $user['status'];
        $loginState[$eName] = $exclusive;
        $user['exclusive'] = $exclusive;
        $user['exclusive_type'] = $exclusiveType;
        $user['update_v'] = (int)$loginState['update_v'];
        $redis->hMset($stateKey, $loginState);
        $updateData = array(
            'login_time' => $time,
            'login_ip' => ClientInfo::getClientIp()
        );
        $teenagerPass = $redis->get('teenager-password:' . $userId);
        if (empty($teenagerPass)) {
            $user['teenager_model_open'] = 0;
        } else {
            $user['teenager_model_open'] = 1;
        }
        Db::name('user')->where('user_id', '=', $user['user_id'])->update($updateData);
        $this->updateRedis($user['user_id'], $updateData);
        $this->log($clientInfo['login_way'], $user);
        return $user;
    }

    //记录登录日志
    protected function log($login_way, $user)
    {
        $data = array(
            'meid' => ClientInfo::get('meid'),
            'device_type' => ClientInfo::get('device_type'),
            'user_id' => $user['user_id'],
            'os_name' => ClientInfo::get('os_name'),
            'os_version' => ClientInfo::get('os_version'),
            'v_code' => ClientInfo::get('v_code'),
            'longitude' => ClientInfo::get('longitude'),
            'latitude' => ClientInfo::get('latitude'),
            'login_way' => $login_way ? $login_way : '',
            'login_ip' => ClientInfo::getClientIp(),
            'network_status' => ClientInfo::get('network_status'),
            'brand_name' => ClientInfo::get('brand_name'),
            'device_model' => ClientInfo::get('device_model'),
            'channel' => ClientInfo::get('channel'),
            'login_time' => time()
        );
        Db::name('login_log')->insert($data);
        //清空一个月前的登录记录(10%的几率会发生)
        if (mt_rand(0, 100) <= 10) {
            $expire = strtotime("-1 months", time());
            Db::name('login_log')->where([['login_time', '<=', $expire], ['user_id', '=', $user['user_id']]])->delete();
        }
    }

    //验证密码
    protected function verifyPassword($user, $password)
    {
        $old = str_pad('', 40, '*');
        if ($user['salt'] == $old) {
            $auth = '###' . md5(md5('rCt52pF2cnnKNB3Hkp' . $password));
            return $auth == $user['password'];
        } else {
            $auth = sha1($password . $user['salt']);
            return $auth == $user['password'];
        }
    }

    //检查用户状态
    protected function checkUserStatus(&$user)
    {
        if ($user['status'] != '1') {
            $newUser = Db::name('user')->where('user_id', $user['user_id'])->field('user_id,status,disable_time,disable_length,disable_desc')->find();
            $disable_length = $newUser['disable_length'];
            $msg = self::getDisableMsg($newUser);
            if (empty($disable_length)) {
                return $this->setError($msg, 2, $newUser);
            } else {
                $enableTime = strtotime('+' . $disable_length, $newUser['disable_time']);
                if (time() <= $enableTime) return $this->setError($msg, 2, $newUser);
            }
            $update = ['status' => '1'];
            $num = Db::name('user')->where('user_id', $user['user_id'])->update($update);
            if ($num) {
                $user = array_merge($user, $update);
                self::updateRedis($user['user_id'], $update);
            }
        }
        return true;
    }

    //推送下线通知
    public function pushOfflineNotice($userId, $oldExclusive, $exclusive)
    {

    }

    public function bindPhone($inputData)
    {
        $userId = $inputData['user_id'];
        $phone = $inputData['phone'];
        $phoneCode = $inputData['phone_code'] ? $inputData['phone_code'] : '86';
        if (empty($userId)) return $this->setError('USER_ID不能为空');
        $chm = self::checkPhone($phone);
        if (is_error($chm)) return $this->setError($chm);
        $updateData = array('phone' => $phone, 'phone_code' => $phoneCode);
        $user = Db::name('user')->where(array('user_id' => $userId))->field('user_id,phone')->find();
        if ($user['phone'] == $phone) return $this->setError('已绑定，直接登录即可~');
        $has = !empty($user['phone']);
        $num = Db::name('user')->where(array('user_id' => $userId))->update($updateData);
        if (!$num) return $this->setError('绑定失败');
        if (!$has) {
            //对接rabbitMQ
            try {
                $rabbitChannel = new RabbitMqChannel(['user.credit']);
                $rabbitChannel->exchange('main')->sendOnce('user.credit.bind_phone', ['user_id' => $user['user_id'], 'phone_str' => str_hide($user['phone'], 3, 2)]);
            } catch (\Exception $e) {

            }
        }
        $this->updateRedis($userId, $updateData);
        return [
            'phone' => $phone,
            'user_id' => $userId
        ];
    }

    public function saveInfo($inputData)
    {
        if (isset($inputData['client_seri'])) unset($inputData['client_seri']);
        //fields IOS兼容处理
        $fields = $inputData['fields'];
        if (isset($fields)) {
            $fields = is_array($fields) ? $fields : explode(',', $fields);
            foreach ($inputData as $key => $value) {
                if ($key != 'user_id' && !in_array($key, $fields)) {
                    unset($inputData[$key]);
                }
            }
        }
        $tmp = copy_array($inputData, 'avatar,nickname,gender,birthday,city_id,sign,district_id,province_id,cover,album,weight,height,voice_sign');
        if (empty($tmp)) return $this->setError('用户信息不能为空');
        $data = $this->df->process('save@user', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());

        if (!empty($data['voice_sign']) && !is_url($data['voice_sign'])) {
            return $this->setError('语音标签不合法');
        }

        if (!empty($data['nickname'])) {
            if (strstr($data['nickname'], " ")) {
                return $this->setError('昵称不能有空格');
            }
            $lastData = $this->df->getLastData();
            $last_renick_time = $lastData['last_renick_time'];
            $renickLimitTime = config('app.app_setting.renick_limit_time');
            $diff = ($last_renick_time + $renickLimitTime) - time();
            $diff = $diff < 0 ? 0 : $diff;
            if ($diff > 0) {
                unset($data['nickname'], $tmp['nickname']);
                if (empty($tmp)) return $this->setError(time_str($diff, 'd') . '后才能修改昵称');
            }
            if (!self::checkNickname($data['nickname'])) {
                //对接rabbitMQ
                try {
                    $rabbitChannel = new RabbitMqChannel(['user.user_data_deal', 'user.credit']);
                    $rabbitChannel->exchange('main')->sendOnce('user.credit.illegal_nickname', ['user_id' => $data['user_id'], 'keyword' => $data['nickname']]);
                } catch (\Exception $e) {

                }

                return $this->setError('不能使用此昵称');
            }
            $data['last_renick_time'] = time();
            $DsIM = new DsIM();
            $DsIM->updateUserData($data['user_id']);
        }
        if (!empty($data['sign'])) {
            if (!KeywordCheck::isLegal($data['sign'])) {
                unset($data['sign']);
            }
        }

        $manualData = [];

        if (config('app.avatar_review')) {
            foreach ($data as $key => $v) {
                if (in_array($key, ['avatar', 'cover'])) {
                    $manualData[$key] = $v;
                    unset($data[$key]);
                }
            }
        }

        if (!empty($manualData)) {
            $find = Db::name('user_data_deal')->where(array('user_id' => $data['user_id'], 'audit_status' => '-1'))->find();
            if ($find) return $this->setError('操作繁忙，请稍后再试！');
            $id = $this->manual_handling($data['user_id'], $manualData);
            if ($id && isset($rabbitChannel)) $rabbitChannel->send('user.user_data_deal.audit', ['id' => $id]);
        }

        $is_dynamic_open = config('friend.is_open') ? config('friend.is_open') : 0;
        if ($inputData['is_synchro'] && !empty($is_dynamic_open)) {
            $usermsg = userMsg($data['user_id'], 'user_id,voice_sign,avatar');
            $voice_time = $inputData['voice_time']?$inputData['voice_time']:0;
            if (!empty($data['voice_sign']) && $usermsg['voice_sign'] != $data['voice_sign']) {
                //发新动态
                $rest = systemSend($data['user_id'], '我更新声音标签，请朋友们试听', '', '', $data['voice_sign']
                    , '', 2, 1, '', 3,
                    '', 0, '', '',
                    '', 6, '', '声音标签', '', 1, '', '',$voice_time);
            }

            if (!empty($data['avatar']) && $usermsg['avatar'] != $data['avatar'] && is_url($data['avatar'])) {
                //发新动态
                $voice_time = $inputData['voice_time']?$inputData['voice_time']:0;
                $rest = systemSend($data['user_id'], '我更新了个人头像，请朋友们围观', $data['avatar'], '', ''
                    , '', 2, 1, '', 1,
                    '', 0, '', '',
                    '', 5, '', '个人头像', '', 1, '', '',$voice_time);
            }
        }
        $num = Db::name('user')->where('user_id', $data['user_id'])->update($data);
        if (!$num) return $this->setError('保存用户信息失败');
        $this->updateRedis($data['user_id'], $data);
        $user = $this->getUser($data['user_id']);
        $user = array_merge($user, $manualData);

        isset($rabbitChannel) && $rabbitChannel->close();
        return $user;
    }

    //验证头像地址是否合法
    public function validateAvatar($value, $rule, $data = null, $more = null)
    {
        $avatar_res = validate_qiniu_url($value, 'avatar', array(
            'user_id' => $data['user_id']
        ));
        $album_res = validate_qiniu_url($value, 'album', array(
            'user_id' => $data['user_id']
        ));
        return $avatar_res || $album_res;
    }

    public function validateCover($value, $rule, $data = null, $more = null)
    {
        $cover_res = validate_qiniu_url($value, 'cover', array(
            'user_id' => $data['user_id']
        ));
        $album_res = validate_qiniu_url($value, 'album', array(
            'user_id' => $data['user_id']
        ));
        return $cover_res || $album_res;
    }

    public function changePwd($inputData)
    {
        if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
        if (isset($inputData['confirm_password']) && $inputData['confirm_password'] != $inputData['password']) {
            return $this->setError('两次密码输入不一致');
        }
        $chp = self::checkPassword($inputData['password']);
        if (is_error($chp)) return $this->setError($chp);
        $info = $this->getBasicInfo($inputData['user_id']);
        if (empty($info) || $info['status'] != '1') return $this->setError('用户不存在或已禁用');
        //已经设置密码则需要验证手机验证码
        if ($info['isset_pwd'] == '1') {
            /* if (empty($info['phone'])) return $this->setError('请绑定手机号');
             $code = $inputData['code'];
             if (empty($code)) return $this->setError('短信验证码不能为空');
             $smsCodeModel = new SmsCode();
             $result = $smsCodeModel->checkCode('change_pwd', $info['phone'], $code);
             if (!$result) return $this->setError($smsCodeModel->getError());*/
            if (empty($inputData['old_password'])) return $this->setError('请输入原密码');
            $vp = $this->verifyPassword($info, $inputData['old_password']);
            if (!$vp) return $this->setError('原密码不正确');
        }
        unset($inputData['code'], $inputData['old_password']);
        $data['isset_pwd'] = '1';
        $data['salt'] = sha1(uniqid() . get_ucode(8));
        $data['password'] = sha1($inputData['password'] . $data['salt']);
        $data['change_pwd_time'] = time();
        $num = Db::name('user')->where(array('user_id' => $inputData['user_id']))->update($data);
        if (!$num) return $this->setError('修改失败');
        $this->updateRedis($inputData['user_id'], $data);
        return array('isset_pwd' => '1', 'user_id' => $inputData['user_id']);
    }

    public function resetPwd($inputData)
    {
        $phoneCode = $inputData['phone_code'];
        $phone = $inputData['phone'];
        $code = $inputData['code'];
        if (empty($phone)) return $this->setError('手机号不能为空');
        if (empty($code)) return $this->setError('短信验证码不能为空');
        if (isset($inputData['confirm_password']) && $inputData['confirm_password'] != $inputData['password']) {
            return $this->setError('两次密码输入不一致');
        }
        $chp = self::checkPassword($inputData['password']);
        if (is_error($chp)) return $this->setError($chp);
        $smsCodeModel = new SmsCode();
        $result = $smsCodeModel->checkCode('reset_pwd', $phone, $code, $phoneCode);
        if (!$result) return $this->setError($smsCodeModel->getError());
        $user = Db::name('user')->alias('user')->field('user_id,isset_pwd,salt')->where('phone', $phone)->where(array(
            'user.delete_time' => null
        ))->find();
        if (empty($user)) return $this->setError('用户不存在');
        //if ($user['isset_pwd'] != '1') return $this->setError('该账号尚未设置密码');
        $data['isset_pwd'] = '1';
        $data['salt'] = sha1(uniqid() . get_ucode(8));
        $data['password'] = sha1($inputData['password'] . $data['salt']);
        $data['change_pwd_time'] = time();
        $num = Db::name('user')->where(array('user_id' => $user['user_id']))->update($data);
        if (!$num) return $this->setError('重置失败');
        $this->updateRedis($user['user_id'], $data);
        return array('isset_pwd' => '1', 'user_id' => $user['user_id']);
    }

    //绑定第三方账号
    public function bindThird($inputData)
    {
        if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
        $type = $inputData['type'];
        $openid = $inputData['openid'];
        $uuid = $inputData['uuid'];
        if (!enum_in($type, 'third_type')) return $this->setError('第三方平台不支持');
        $where['type'] = $type;
        $where['status'] = 'bind';
        $appid = !empty($inputData['appid']) ? $inputData['appid'] : enum_attr('third_type', $type, 'appid');
        //优先使用全局UUID
        if (!empty($uuid)) {
            $where['uuid'] = $uuid;
        } else {
            $where['openid'] = $openid;
            $where['appid'] = $appid;
        }
        $bind = Db::name('user_third')->where($where)->find();
        if ($bind && $bind['user_id'] == $inputData['user_id']) return $this->setError('请勿重复绑定');
        if ($bind) return $this->setError('已绑定其他账号');
        //自动解绑老的
        Db::name('user_third')->where(array('type' => $type, 'user_id' => $inputData['user_id'], 'status' => 'bind'))->update(array(
            'status' => 'unbind',
            'unbind_time' => time()
        ));
        $update['user_id'] = $inputData['user_id'];
        $update['type'] = $type;
        $update['openid'] = $openid;
        $update['appid'] = enum_attr('third_type', $type, 'appid');
        $update['uuid'] = $inputData['uuid'] ? $inputData['uuid'] : '';
        $update['status'] = 'bind';
        $update['bind_time'] = time();
        $update['invite_code'] = '';
        $id = Db::name('user_third')->insertGetId($update);
        if (!$id) return $this->setError('绑定失败');
        $update['id'] = $id;
        $updateData["bind_{$type}"] = '1';
        $this->updateRedis($inputData['user_id'], $updateData);
        return $update;
    }

    //切换设置状态
    public function switchStatus($inputData)
    {
        if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
        $name = $inputData['name'];
        $arr = array('comment_push', 'like_push', 'follow_push', 'follow_new_push', 'follow_live_push',
            'recommend_push', 'msg_push', 'rank_stealth', 'download_switch', 'autoplay_switch', 'at_push');
        $data = [];
        if (!isset($name)) {
            foreach ($inputData as $key => $value) {
                if (in_array($key, $arr)) {
                    $data[$key] = $value;
                }
            }
        } else {
            if (!in_array($name, $arr)) return $this->setError('设置项不存在');
            $data[$name] = $inputData['value'];
        }
        if (empty($data)) return $this->setError('请选择设置项');
        $userSetting = new UserSetting();
        $num = $userSetting->setting($inputData['user_id'], null, $data);
        return $data;
    }

    //延长VIP期限
    public function extendedVipExpire($user, $vip)
    {
        if (!is_array($user) && !empty($user)) {
            $user = $this->db()->field('user_id,vip_expire')->where(array('user_id' => $user, 'delete_time' => null))->find();
        }
        if (empty($user)) return $this->setError('用户不存在');
        if (!is_array($vip) && !empty($vip)) {
            $vip = Db::name('vip_order')->where(array('pay_status' => '1', 'order_no' => $vip))->find();
        }
        if (empty($vip)) return $this->setError('VIP不存在');
        $now = time();
        $unit = strtolower($vip['unit']);
        $length = $vip['length'];
        $map = array(
            'y' => 'years',
            'm' => 'months',
            'w' => 'week',
            'd' => 'days'
        );
        if (!isset($map[$unit])) return $this->setError('VIP单位不合法');
        if (!validate_regex($length, 'number') || $length <= 0) return $this->setError('VIP长度不合法');
        $start = $user['vip_expire'] < $now ? $now : $user['vip_expire'];
        $vip_expire_new = strtotime("+{$length} {$map[$unit]}", $start);
        $updateData['vip_expire'] = $vip_expire_new;
        $num = $this->db()->where(array('user_id' => $user['user_id']))->update($updateData);
        if (!$num) return $this->setError('更新VIP状态失败');
        self::parseVipExpire($updateData);
        $this->updateRedis($user['user_id'], $updateData);
        return $updateData;
    }


    public function savePwd($inputData)
    {
        if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
        $user = $this->db('user')->where(['user_id' => $inputData['user_id'], 'delete_time' => null])->find();
        if (!$user) return $this->setError('用户不存在');
        if ($user['isset_pwd'] != '0') return $this->setError('密码已设置');
        $username = $inputData['username'];
        $data = [];
        if (!empty($username)) {
            if ($username != $user['username']) {
                $chu = self::checkUsername($username);
                if (is_error($chu)) return $this->setError($chu);
                $data['username'] = $username;
            }
        } else {
            if (empty($user['username'])) return $this->setError('请设置用户名');
        }
        $chp = self::checkPassword($inputData['password']);
        if (is_error($chp)) return $this->setError($chp);
        $data['isset_pwd'] = '1';
        $data['salt'] = sha1(uniqid() . get_ucode(8));
        $data['password'] = sha1($inputData['password'] . $data['salt']);
        $data['change_pwd_time'] = time();
        $num = Db::name('user')->where(array('user_id' => $user['user_id']))->update($data);
        if (!$num) return $this->setError('设置失败');
        $this->updateRedis($user['user_id'], $data);
        return array('isset_pwd' => '1', 'user_id' => $user['user_id'], 'username' => $username ? $username : $user['username']);
    }

    //用户保存同步标签
    public function saveImpression($inputData)
    {
        if (empty($inputData['user_id'])) return $this->setError('参数错误');
        $user_my_impression = Db::name('user_my_impression')->where(['user_id' => $inputData['user_id']])->select();
        if (empty($inputData['ids']) && !empty($user_my_impression)) {
            $del = Db::name('user_my_impression')->where(['user_id' => $inputData['user_id']])->delete();
            return true;
        }
        $all_impression = Db::name('impression')->where(['status' => 1, 'type' => 1])->column('id');
        $select_id = explode(',', $inputData['ids']);
        if (count($select_id) > 6) return $this->setError('您最多只能添加6个印象');
        $exists = array_diff($select_id, $all_impression);
        if (!empty($exists)) return $this->setError('印象不存在');
        $now = time();

        if (!empty($user_my_impression)) {
            $user_select_ids = array_column($user_my_impression, 'impression_id');
            $save_ids = array_diff($select_id, $user_select_ids);
            if (empty($save_ids) && count($select_id) == count($user_select_ids)) return true;
            $del = Db::name('user_my_impression')->where(['user_id' => $inputData['user_id']])->delete();
            if (!$del) return $this->setError('保存出错');
        }

        $insert = [];
        foreach ($select_id as $p_id) {
            $tmp = [
                'user_id' => $inputData['user_id'],
                'impression_id' => $p_id,
                'create_time' => $now
            ];
            array_push($insert, $tmp);
        }

        $rs = Db::name('user_my_impression')->insertAll($insert);
        if (!$rs) return $this->setError('保存错误');
        return true;
    }

}