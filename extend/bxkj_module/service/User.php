<?php

namespace bxkj_module\service;

use app\core\service\User as UserService;
use bxkj_common\RabbitMqChannel;
use bxkj_module\exception\Exception;
use bxkj_common\KeywordCheck;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class User extends Service
{

    protected $defaultAvatar;
    protected $defaultSign = '这个家伙太懒了，什么也没留下。';
    protected $usernamePrefix = 'bx_';
    protected $nicknamePrefix = 'BX';
    protected $fieldsMap = array(
        '_list' => 'user_id,pid,nickname,millet,avatar,gender,birthday,city_id,exp,level,verified,is_creation,sign,vip_expire,
        like_num,like_num_str,film_num,film_num_str,fans_num,fans_num_str,vip_expire_str,age,city_name,is_black,is_official,is_follow,rank_stealth,vip_status,credit_score,points,invite_code,phone,weight,height,voice_sign,voice_time,cash,goodnum',
    );

    public function __construct()
    {
        parent::__construct();
        $this->defaultAvatar = img_url('', '', 'avatar');
        $this->usernamePrefix = USER_NAME_PREFIX;
        $this->nicknamePrefix = NICK_NAME_PREFIX;
    }

    //检查手机号
    public static function checkPhone($phone, $uniqid = true)
    {
        if (empty($phone)) return make_error('手机号不能为空');
        if (!validate_regex($phone, 'phone')) return make_error('手机号不正确');
        if ($uniqid) {
            $num = Db::name('user')->where(array('phone' => $phone, 'delete_time' => null))->count();
            if ($num > 0) return make_error('手机号已存在');
        }
        return true;
    }

    //检查用户名
    public static function checkUsername($username)
    {
        if (empty($username)) return make_error('用户名不能为空');
        $length = mb_strlen($username);
        if ($length < 4 || $length > 25) return make_error('用户名4-25个字符');
        if (!validate_regex($username, '/^[0-9a-zA-Z_]{4,25}$/')) return make_error('用户名格式不正确');
        // if (validate_regex($username, 'number')) return make_error('用户名不能为纯数字');
        $num = Db::name('user')->where(['username' => $username, 'delete_time' => null])->count();
        if ($num > 0) return make_error('用户名已存在');
        return true;
    }

    //检查密码
    public static function checkPassword($password)
    {
        if (!validate_regex($password, 'require')) return make_error('密码不能为空');
        if (!validate_regex($password, 'no_blank')) return make_error('密码不能包含空格');
        if (validate_regex($password, 'number')) return make_error('密码不能为纯数字');
        if (strlen($password) < 6) return make_error('密码不能小于6位');
        if (strlen($password) > 16) return make_error('密码不能大于16位');
        return true;
    }

    //检查昵称
    public static function checkNickname($nickname)
    {
        if (!KeywordCheck::isLegal($nickname)) return false;
        return true;
    }

    //生成一个号
    public static function generateUserId()
    {
        $redis = RedisClient::getInstance();
        redis_lock('generate_user', 10);
        $autoNum = $redis->get('user_id:auto');
        if (!$autoNum) {
            $userId1 = Db::name('user')->max('user_id');
            $userId2 = Db::name('robot')->max('user_id');
            $autoNum = max($userId1, $userId2, USER_ID_START);
        }
        $exists = $redis->exists('user_id:pond');//检查号池是否已存在
        if (!$exists)
        {
            for ($i = 0; $i < 500; $i++)
            {
                $autoNum = (int)($autoNum);
                $autoNum++;
                $redis->sadd('user_id:pond', $autoNum);
            }
        }
        //随机抽取一个号并向池内补充一个
        $userId = $redis->sPop('user_id:pond');
        $autoNum++;
        $redis->sadd('user_id:pond', $autoNum);
        $redis->set('user_id:auto', $autoNum);
        redis_unlock('generate_user');
        return $userId;
    }

    //直接从数据库中取用户信息（基本信息）
    public function getBasicInfo($userId, $status = '1')
    {
        $where = array('user.user_id' => $userId, 'user.delete_time' => null);
        if (isset($status)) $where['status'] = $status;
        $userDb = Db::name('user');
        $user = $userDb->field('user.user_id,user.pid,user.nickname,user.type,user.isvirtual,user.status,user.isset_pwd,user.password,user.salt,user.phone_code,user.phone,user.exp,user.level,bean.bean,bean.pay_total,bean.total_bean,bean.fre_bean,bean.id bean_id,bean.recharge_total,bean.pay_status,bean.cash_status,bean.commission_price,bean.commission_pre_price,bean.commission_total_price,bean.loss_bean,bean.last_pay_time,user.millet,user.millet_status,user.total_millet,user.fre_millet,user.his_millet,user.millet_cash_total,setting.like_push,setting.comment_push,setting.at_push,setting.follow_push,user.is_promoter,user.is_anchor,user.credit_score,user.isvirtual_millet,user.his_isvirtual_millet,user.points,user.taoke_shop,user.teenager_model_open,user.shop_id,user.relation_id,user.relation_id,user.special_id,user.pdd_pid,user.jd_pid,user.taoke_level,user.taoke_money,user.taoke_money_status,user.instance_id,user.bond,user.member_level,user.balance,user.score,
                user.fenxiao_id,user.is_fenxiao,user.balance_money,user.order_num,user.balance_withdraw,user.balance_withdraw_apply,user.live_money,user.pay_password,user.live_withdraw_apply,user.goodnum')
            ->where($where)->alias('user')->join('bean bean', 'bean.user_id=user.user_id')->join('user_setting setting', 'setting.user_id=user.user_id')
            ->limit(1)->find();
        if ($user) {
            $user['user_id'] = (string)$user['user_id'];
        }
        return $user;
    }
    //直接从数据库中取用户信息（基本信息）
    public function getBasicInforeward($userId, $status = '1')
    {
        $where = array('user_id' => $userId, 'delete_time' => null);
        if (isset($status)) $where['status'] = $status;
        $userDb = Db::name('user');
        $user = $userDb
            ->where($where)
            ->limit(1)->find();
        if ($user) {
            $user['user_id'] = (string)$user['user_id'];
        }
        return $user;
    }

    //解析用户信息
    public static function parseUser(&$user)
    {
        if ($user['user_id'] == config('app.app_setting.helper_id'))
        {
            $user['nickname'] = APP_PREFIX_NAME.'小助手';
            $user['avatar'] = config('upload.image_defaults.helper_avatar');
        }

        $user['user_id'] = (string)$user['user_id'];
        $user['create_time'] = date('Y-m-d', $user['create_time']);
        $user['gender'] = $user['gender'] ? $user['gender'] : '0';
        self::parseVipExpire($user);
        if (!$user['birthday']) {
            $user['birthday'] = '';
            $user['age'] = 0;
        } else {
            $user['age'] = birthday_to_age($user['birthday']);
            $user['birthday'] = date('Y-m-d', $user['birthday']);
        }
        $user['province_id'] = isset($user['province_id']) ? (int)$user['province_id'] : 0;
        $user['city_id'] = isset($user['city_id']) ? (int)$user['city_id'] : 0;
        $user['district_id'] = isset($user['district_id']) ? (int)$user['district_id'] : 0;
        $user['total_millet_str'] = number_format2($user['total_millet']);
        $user['follow_num_str'] = number_format2($user['follow_num']);
        $user['fans_num_str'] = number_format2($user['fans_num']);
        $user['film_num_str'] = number_format2($user['film_num']);
        $user['like_num_str'] = number_format2($user['like_num']);
        $user['collection_num_str'] = number_format2($user['collection_num']);
        $user['download_num_str'] = number_format2($user['download_num']);
        $user['need_phone'] = '2';
        $user['is_official'] = in_array($user['user_id'], ['10000']) ? '1' : '0';
        if ($user['isvirtual'] == 1) {
            $user['phone'] = empty($user['phone']) ? '00' . $user['user_id'] : $user['phone'];
        } else {
            $user['need_phone'] = empty($user['phone']) ? '1' : '2';//不是虚拟号并且手机号为空
        }
        $strFields = 'bean,pay_total,cash_total,recharge_total,fre_bean,total_bean,his_millet,millet_cash_total,
        fre_millet,total_millet,millet';
        $strFieldArr = str_to_fields($strFields);
        foreach ($strFieldArr as $str) {
            if (isset($user[$str])) $user[$str] = (string)$user[$str];
        }
    }

    //解析VIP信息
    public static function parseVipExpire(&$user)
    {
        $now = time();
        $user['vip_expire'] = is_null($user['vip_expire']) ? 0 : $user['vip_expire'];
        if ($user['vip_expire'] == 0) {
            $user['vip_status'] = '0';
            $user['vip_expire_str'] = '未开通';
        } else if ($now < $user['vip_expire']) {
            $user['vip_status'] = '1';
            $user['vip_expire_str'] = date('Y-m-d', $user['vip_expire']);
        } else {
            $user['vip_status'] = '2';
            $user['vip_expire_str'] = '已过期';
        }
    }

    //为用户列表解析地址
    public static function parseRegionForUsers(&$users)
    {
        $regionIds = [];
        foreach ($users as &$user) {
            if ($user['province_id']) $regionIds[] = $user['province_id'];
            if ($user['city_id']) $regionIds[] = $user['city_id'];
            if ($user['district_id']) $regionIds[] = $user['district_id'];
        }
        $regionIds = array_unique($regionIds);  //地区ids
        if (!empty($regionIds)) {
            $regions = Db::name('region')->field('id,name')->whereIn('id', $regionIds)->select();
            foreach ($users as &$user) {
                $province = self::getItemByList($user['province_id'], $regions, 'id');
                $city = self::getItemByList($user['city_id'], $regions, 'id');
                $district = self::getItemByList($user['district_id'], $regions, 'id');
                $user['province_name'] = $province ? $province['name'] : '';
                $user['city_name'] = $city ? $city['name'] : '';
                $user['district_name'] = $district ? $district['name'] : '';
            }
        }
    }

    public static function updateRedis($userId, $update)
    {
        return UserRedis::updateData($userId, $update);
    }

    public function changeStatus($userIds, $status, $ownAgentId = null, $disable_length = '', $disable_desc = '', $aid = null)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $update = ['status' => $status];
        if ($status == '0') {
            $update['disable_desc'] = $disable_desc ? $disable_desc : '';
            if (!empty($disable_length)) {
                $enableTimeArr = explode(' ', $disable_length);
                if (!preg_match('/^\d+$/', $enableTimeArr[0])) return 0;
                if (!in_array($enableTimeArr[1], ['seconds', 'minutes', 'hours', 'days', 'months', 'years'])) return 0;
            }
            $update['disable_length'] = $disable_length ? $disable_length : '';
            $update['disable_time'] = time();
        }
        $mySocket = new Socket();
        $num = Db::name('user')->whereIn('user_id', $userIds)->update($update);
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        if ($num > 0) {
            $redis = RedisClient::getInstance();
            $total = 0;
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['status' => $status]);
                $key = "loginstate:{$userId}";
                if ($redis->exists($key)) {
                    if ($redis->hSet($key, 'status', $status)) {
                        $total++;
                    }
                }
                if ($status == '0') {
                    $msg = self::getDisableMsg($update);
                    $mySocket->stopUserAccountDiscontinued($userId, $msg);
                    if ($aid) {
                        $config = enum_array('freezing_account_rate');
                        $length = $disable_length ? $disable_length : '1 years';
                        $score = $config[$length];
                        $rabbitChannel->exchange('main')->send('user.credit.erp_freezing_account', ['user_id' => $userId, 'value' => $score, 'term' => $length, 'aid' => $aid]);
                    }
                }
            }
        }
        $rabbitChannel->close();
        return $num;
    }

    //切换直播状态
    public function changeLiveStatus($userIds, $liveStatus, $ownAgentId = null)
    {
        if (!in_array($liveStatus, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $num = Db::name('user')->whereIn('user_id', $userIds)->update(['live_status' => $liveStatus]);
        if ($num > 0) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['live_status' => $liveStatus]);
            }
        }
        return $num;
    }

    //切换上传短视频状态
    public function changeFilmStatus($userIds, $filmStatus, $ownAgentId = null)
    {
        if (!in_array($filmStatus, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $num = Db::name('user')->whereIn('user_id', $userIds)->update(['film_status' => $filmStatus]);
        if ($num > 0) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['film_status' => $filmStatus]);
            }
        }
        return $num;
    }

    //切换评论状态
    public function changeCommentStatus($userIds, $comment_status, $ownAgentId = null)
    {
        if (!in_array($comment_status, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $num = Db::name('user')->whereIn('user_id', $userIds)->update(['comment_status' => $comment_status]);
        if ($num > 0) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['comment_status' => $comment_status]);
            }
        }
        return $num;
    }

    //切换私聊状态
    public function changeContactStatus($userIds, $contact_status, $ownAgentId = null)
    {
        if (!in_array($contact_status, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $num = Db::name('user')->whereIn('user_id', $userIds)->update(['contact_status' => $contact_status]);
        if ($num > 0) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['contact_status' => $contact_status]);
            }
        }
        return $num;
    }

    //切换支付状态
    public function changePayStatus($userIds, $payStatus, $ownAgentId = null)
    {
        if (!in_array($payStatus, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $num = Db::name('bean')->whereIn('user_id', $userIds)->update(['pay_status' => $payStatus]);
        if ($num > 0) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['pay_status' => $payStatus]);
            }
        }
        return $num;
    }

    public function changeMilletStatus($userIds, $milletStatus, $ownAgentId = null)
    {
        if (!in_array($milletStatus, ['0', '1'])) return false;
        $userIds = $this->getUsersAgentFilter($userIds, $ownAgentId);
        if (empty($userIds)) return 0;
        $num = Db::name('user')->whereIn('user_id', $userIds)->update(['millet_status' => $milletStatus]);
        if ($num > 0) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['millet_status' => $milletStatus]);
            }
        }
        return $num;
    }

    public function clearSign($userIds, $ownAgentId = null)
    {
        $where = [];
        Agent::agentWhere($where, ['agent_id' => $ownAgentId], 'rel.');
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        if (empty($userIds)) return 0;
        $num = Db::name('user')->alias('user')
            ->join('__PROMOTION_RELATION__ rel', 'rel.user_id=user.user_id')
            ->whereIn('user.user_id', $userIds)->where($where)->update(['user.sign' => '']);
        if ($num) {
            foreach ($userIds as $userId) {
                self::updateRedis($userId, ['sign' => '']);
            }
        }
        return $num;
    }

    //将user_id进行权限过滤
    public function getUsersAgentFilter($userIds, $ownAgentId, $mode = 1, $fields = null)
    {
        $where = [];
        $fields = isset($fields) ? $fields : 'user.user_id,user.delete_time';
        Agent::agentWhere($where, ['agent_id' => $ownAgentId], 'rel.');
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        $users = Db::name('user')->alias('user')
            //->join('__PROMOTION_RELATION__ rel', 'rel.user_id=user.user_id')
            ->field($fields)->whereIn('user.user_id', $userIds)->where($where)->select();
        $userIds2 = [];
        $users2 = [];
        foreach ($users as $user) {
            if (empty($user['delete_time'])) {
                $userIds2[] = $user['user_id'];
                $users2[] = $user;
            }

        }
        return $mode == 1 ? $userIds2 : $users2;
    }

    protected function vipStatusWhere(&$where, $get)
    {
        if ($get['vip_status'] == '0') {
            $where[] = ['vip_expire', '=', '0'];
        } else if ($get['vip_status'] == '1') {
            $where[] = ['vip_expire', '>', time()];
        } else if ($get['vip_status'] == '2') {
            $where[] = ['vip_expire', '<=', time()];
            $where[] = ['vip_expire', '<>', '0'];
        }
    }

    protected function userTypeWhere(&$where, $get)
    {
        if ($get['user_type'] == 'user') {
            $where[] = ['is_anchor', '<>', '1'];
            $where[] = ['is_promoter', '<>', '1'];
        } else if ($get['user_type'] == 'anchor') {
            $where[] = ['is_anchor', '=', '1'];
        } else if ($get['user_type'] == 'promoter') {
            $where[] = ['is_promoter', '=', '1'];
        } else if ($get['user_type'] == 'not_promoter') {
            $where[] = ['is_promoter', '<>', '1'];
        } else if ($get['user_type'] == 'not_anchor') {
            $where[] = ['is_anchor', '<>', '1'];
        } else if ($get['user_type'] == 'isvirtual') {
            $where[] = ['isvirtual', '=', '1'];
        }
    }

    public function getUsersByIds($userIds, $ownAgentId = null, $needAgentInfo = true)
    {
        if (empty($userIds)) return [];
        $where = [];
        if (isset($ownAgentId)) {
            Agent::agentWhere($where, ['agent_id' => $ownAgentId], 'rel.');
            $users = Db::name('user')->alias('user')
                ->join('__PROMOTION_RELATION__ rel', 'rel.user_id=user.user_id')
                ->whereIn('user.user_id', $userIds)
                ->where($where)
                ->field('user.user_id,user.is_anchor,user.isvirtual,user.nickname,user.remark_name,user.level,
                user.avatar,user.phone,user.is_promoter,rel.promoter_uid,rel.agent_id')
                ->limit(count($userIds))->select();
        } else {
            $users = Db::name('user')->alias('user')
                ->whereIn('user.user_id', $userIds)
                ->where($where)->field('user.user_id,user.is_anchor,user.isvirtual,user.nickname,user.remark_name,user.level,user.avatar,user.phone,user.is_promoter')
                ->limit(count($userIds))->select();
        }
        $users = $users ? $users : [];
        if ($ownAgentId && $needAgentInfo) {
            list($agentIds, $promoterUids) = self::getIdsByList($users, 'agent_id,promoter_uid');
            $agentService = new Agent();
            $agents = $agentService->getAgentsByIds($agentIds);
            $promoterUsers = $this->getUsersByIds($promoterUids, null, false);
        }
        foreach ($users as &$user) {
            if ($user['is_anchor']=='1')
            {
                $anchor_lv = Db::name('anchor')->where('user_id',$user['user_id'])->value('anchor_lv');
                $user = array_merge($user, AnchorExpLevel::getAnchorLevelInfo($anchor_lv));
            }else{
                $user = array_merge($user, ExpLevel::getLevelInfo($user['level']));
            }
            if ($ownAgentId && $needAgentInfo) {
                if ($user['agent_id']) {
                    $user['agent_info'] = self::getItemByList($user['agent_id'], $agents, 'id');
                }
                if ($user['promoter_uid']) {
                    $user['promoter_info'] = self::getItemByList($user['promoter_uid'], $promoterUsers, 'user_id');
                }
            }
        }
        return $users;
    }

    public function getUserById($userId, $ownAgentId = null)
    {
        if (empty($userId)) return [];
        $users = $this->getUsersByIds([$userId], $ownAgentId);
        return $users ? $users[0] : [];
    }

    //获取多条用户信息
    public function getInfoArr($userIds, $multi = true, $isRobot = false)
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $regionIds = [];
        $regions = [];
        $table = $isRobot ? 'robot' : 'user';
        $userDb = Db::name($table)->alias('user');
        $userDb->field('user.user_id,user.pid,user.username,user.nickname,user.type,user.isvirtual,user.avatar,user.status,user.gender,user.birthday,user.province_id,
                user.city_id,user.district_id,user.phone,user.exp,user.level,user.verified,user.is_creation,user.is_promoter,
                user.is_anchor,user.sign,user.cover,user.vip_expire,user.millet,user.total_millet,user.fre_millet,user.millet_status,user.his_millet,user.like_num,
                user.fans_num,user.film_num,user.follow_num,user.collection_num,user.download_num,user.create_time,user.isset_pwd,user.live_status,user.film_status,
                user.comment_status,user.contact_status,user.credit_score,user.isvirtual_millet,user.his_isvirtual_millet,user.phone_code,user.points,user.taoke_shop,user.teenager_model_open,
                user.shop_id,user.taoke_level,user.relation_id,user.relation_id,user.special_id,user.pdd_pid,user.jd_pid,user.taoke_money,
                user.instance_id,user.bond,user.member_level,user.balance,user.score,user.invite_code, user.weight, user.height, user.voice_sign,user.voice_time, user.cash,user.goodnum,
                user.fenxiao_id,user.is_fenxiao,user.balance_money,user.order_num,user.balance_withdraw,user.balance_withdraw_apply,user.live_money,user.pay_password,user.live_withdraw_apply');
        $userDb->field('bean.id bean_id,bean.bean,bean.pay_total,bean.total_bean,bean.fre_bean,bean.pay_status,bean.cash_status,bean.loss_bean,bean.commission_price,bean.commission_pre_price,bean.commission_total_price');
        $userDb->field('setting.comment_push,setting.like_push,setting.follow_push,setting.follow_new_push,setting.recommend_push,setting.follow_live_push,
                setting.msg_push,setting.at_push,setting.rank_stealth,setting.download_switch,setting.autoplay_switch');
        $where = array('user.delete_time' => null);
        $userDb->whereIn('user.user_id', $userIds);
        $users = $userDb->where($where)
            ->join('bean', 'bean.user_id=user.user_id', 'left')
            ->join('user_setting setting', 'setting.user_id=user.user_id', 'left')
            ->limit(count($userIds))->select();
        if (empty($users)) return [];
        foreach ($users as &$user) {
            $user['bind_weixin'] = $user['bind_qq'] = $user['bind_weibo'] = '0';
            if ($user['province_id']) $regionIds[] = $user['province_id'];
            if ($user['city_id']) $regionIds[] = $user['city_id'];
            if ($user['district_id']) $regionIds[] = $user['district_id'];
            //机器人没有设置表、绑定关系和豆子表
            if ($isRobot) {
                $user['bean_id'] = 0;
                $user['bean'] = 0;
                $user['pay_total'] = 0;
                $user['total_bean'] = 0;
                $user['fre_bean'] = 0;
                $user['pay_status'] = '1';
                $user['cash_status'] = '1';
                $user['comment_push'] = '1';
                $user['at_push'] = '1';
                $user['like_push'] = '1';
                $user['follow_push'] = '1';
                $user['follow_new_push'] = '1';
                $user['recommend_push'] = '1';
                $user['follow_live_push'] = '1';
                $user['msg_push'] = '1';
                $user['rank_stealth'] = '0';
                $user['download_switch'] = '1';
                $user['autoplay_switch'] = '1';
            }
        }
        if (!$isRobot) {
            //绑定关系
            $binds = Db::name('user_third')->field('user_id,type,openid,bind_time')->where(array('status' => 'bind'))->whereIn('user_id', $userIds)->select();
            foreach ($binds as $bind) {
                foreach ($users as &$user) {
                    if ($user['user_id'] == $bind['user_id']) {
                        $user["bind_{$bind['type']}"] = '1';
                    }
                }
            }
        }
        //地区
        $regionIds = array_unique($regionIds);
        if (!empty($regionIds)) {
            $regions = Db::name('region')->field('id,name')->whereIn('id', $regionIds)->select();
        }
        foreach ($users as &$user) {
            $province = $this->getItemByList($user['province_id'], $regions, 'id');
            $city = $this->getItemByList($user['city_id'], $regions, 'id');
            $district = $this->getItemByList($user['district_id'], $regions, 'id');
            $user['province_name'] = $province ? $province['name'] : '';
            $user['city_name'] = $city ? $city['name'] : '';
            $user['district_name'] = $district ? $district['name'] : '';
            $user['is_visitor'] = $user['bind_device'] == '1' ? '1' : '0';
        }
        return $multi ? $users : $users[0];
    }

    //获取用户信息(单个)
    public function getUser($userId, $selfUserId = null, $fields = null)
    {
        if (isset($fields) && empty($fields)) return false;
        $users = $this->getUsers([$userId], $selfUserId, $fields);
        return $this->getItemByList($userId, $users, 'user_id');
    }

    //获取用户信息（批量）
    public function getUsers($userIds, $selfUserId = null, $fields = null)
    {
        $userIds = array_unique((is_array($userIds) ? $userIds : explode(',', $userIds)));
        $users = [];
        $redis = RedisClient::getInstance();
        $userBlack = new UserBlacklist();
        $follow = new Follow();
        foreach ($userIds as $id) {
            $isRobot = $redis->sIsMember('robot_sets', $id);//检查是否是机器人
            $key = $isRobot ? "robot:{$id}" : "user:{$id}";
            $userJson = $redis->get($key);
            // $userJson = null;
            $user = $userJson ? json_decode($userJson, true) : null;
            if (empty($user) || empty($user['user_id']) || $user['cache_expired_time'] <= time()) {
                $user = $this->getInfoArr($id, false, $isRobot);
                if ($user) {
                    $user['cache_expired_time'] = time() + (3600 * 24 * 7);
                    $redis->set($key, json_encode($user));
                }
            }
            if ($user) {
                self::parseUser($user);
                $user['purview'] = '';
                if (!empty($selfUserId)) {
                    $isBlack = $userBlack->isBlack($selfUserId, $user['user_id']);
                    $user['is_black'] = $isBlack ? '1' : '0';
                    $isFollow = $follow->isFollow($selfUserId, $user['user_id']);
                    $user['is_follow'] = $isFollow ? '1' : '0';
                }
                $tmpFields = isset($this->fieldsMap[$fields]) ? $this->fieldsMap[$fields] : $fields;
                $users[] = isset($fields) && $fields != '_all' ? copy_array($user, $tmpFields) : $user;
            }
        }
        return $users;
    }

    protected function handlerAvatar($url)
    {
        if (empty($url)) return $this->defaultAvatar;
        //将微信头像替换成高清的 https://thirdwx.qlogo.cn
        if (preg_match('/^(http|https)\:\/\/thirdwx\.qlogo\.(cn|com)/i', $url)) {
            return preg_replace('/\/[0-9_a-zA-Z]+$/', '/0', $url);
        }
        return $url;
    }

    public function setIsvirtual($userId)
    {
        $update = ['isvirtual' => '1'];
        $num = Db::name('user')->where([
            ['user_id', 'eq', $userId],
            ['delete_time', 'null']
        ])->update($update);
        if ($num) {
            User::updateRedis($userId, $update);
        }
        if (!$num) return $this->setError('设置失败');
        return $num;
    }

    //获取满足的等级
    public static function getFillLv($exp, $type = 'user')
    {
        $typeName = $type == 'user' ? 'exp_level' : "{$type}_exp_level";
        $redis = RedisClient::getInstance();
        $db = Db::name($typeName);
        if (!$redis->exists('config:' . $typeName)) {
            $levelArr = $db->order('level_up ASC')->field('levelid,level_up')->select();
            if (empty($levelArr)) return false;
            foreach ($levelArr as $item) {
                $redis->zAdd('config:' . $typeName, $item['level_up'], $item['levelid']);
            }
        }
        $lv = $redis->zRevRangeByScore('config:' . $typeName, $exp, 0, array('withscores' => TRUE, 'limit' => array(0, 1)));
        if ($lv === false) return false;
        foreach ($lv as $k => $v) {
            return $k;
        }
        return false;
    }

    public static function getDisableMsg($user)
    {
        $disable_length = $user['disable_length'];
        $desc = (empty($user['disable_desc']) ? '' : ('被封原因：' . $user['disable_desc'] . '。'));
        if (empty($disable_length)) {
            return '您的账号已被永久封禁！' . $desc;
        } else {
            $enableTime = strtotime('+' . $disable_length, $user['disable_time']);
            $arr1 = ['seconds', 'minutes', 'hours', 'days', 'months', 'years'];
            $arr2 = ['秒', '分钟', '小时', '天', '个月', '年'];
            $str = preg_replace('/\s+/', '', str_replace($arr1, $arr2, $disable_length));
            if (time() <= $enableTime) return "您的账号已被封禁{$str}！" . $desc;
        }
        return '';
    }

    public function updateData($userId, $data)
    {
        $arr = UserRedis::$arrowArr;
        $isArrow = false;
        foreach ($data as $fk => $value) {
            if (in_array($fk, $arr) && is_string($value) && preg_match('/^(\-|\+)\d+$/', $value)) {
                $isArrow = true;
                break;
            }
        }
        if ($isArrow) {
            Service::startTrans();
            $user = Db::name('user')->where(['user_id' => $userId])->find();
            foreach ($data as $fk => $value) {
                if (in_array($fk, $arr) && is_string($value)) {
                    if (preg_match('/^\+\d+/', $value)) {
                        $data[$fk] = $user[$fk] + (ltrim($value, '+'));
                    } elseif (preg_match('/^\-\d+/', $value)) {
                        $data[$fk] = $user[$fk] - (ltrim($value, '-'));
                    }
                }
            }
        }
        $num = Db::name('user')->where(['user_id' => $userId])->update($data);
        if (!$num) {
            if ($isArrow) Service::rollback();
            return $num;
        }
        if ($isArrow) Service::commit();
        self::updateRedis($userId, $data);
        return $num;
    }

    public function followSearch($user_id, $key_word)
    {
        $where = [['f.user_id', 'eq', $user_id]];

        if (empty($key_word)) return [];
        if (is_numeric($key_word)) {
            array_push($where, ['u.user_id', 'like', "%{$key_word}%"]);
        } else {
            array_push($where, ['u.nickname', 'like', "%{$key_word}%"]);
        }

        $res = Db::name('user')->alias('u')
            ->join('__FOLLOW__ f', 'u.user_id=f.follow_id')
            ->field('u.user_id')
            ->where($where)
            ->select();

        return $res;
    }

    public function searchByname($key_word,$selfUserId){

        $db      = Db::name('user');
        $list    = $db
            ->where([ 'user_id' => (int)$key_word, 'status' => '1', 'delete_time' => null])
            ->field('user_id')
            ->union('select user_id from '. config('database.prefix') .'user where nickname like"' . trim($key_word) . '%"')
            ->union('select user_id from '. config('database.prefix') .'user where phone="' . trim($key_word) . '"')
            ->select();
        if (empty($list)) return [];
        $userIds = $users = [];
        foreach ($list as $item) {
            $userIds[] = $item['user_id'];
        }
        if (!empty($userIds)) {
            $userModel = new UserService();
            $users     = $userModel->getUsers($userIds, $selfUserId, '_list');
        }
      return $users;
    }
}