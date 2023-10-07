<?php

namespace app\common\service;

use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use bxkj_common\HttpClient;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_module\service\Work;
use think\Db;
use think\Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Faceid\V20180301\FaceidClient;
use TencentCloud\Faceid\V20180301\Models\IdCardOCRVerificationRequest;

class User extends \bxkj_module\service\User
{
    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
    }

    //返回前端时过滤敏感字段
    public static function safeFiltering(&$user, $append = '')
    {
        $fields = 'id,salt,password,exclusive_type,exclusive,login_ip,login_time,reg_way,type,isvirtual';
        $fieldsArr = explode(',', $fields);
        $fieldsArr = array_merge($fieldsArr, ($append ? explode(',', $append) : array()));
        foreach ($fieldsArr as $f) {
            if (isset($user[$f])) unset($user[$f]);
        }
    }

    //检查登录状态
    public function checkLogin($user, $os)
    {
        if (empty($user) || empty($user['user_id'])) return $this->setError('用户不存在');
        $user_id = $user['user_id'];
        $deviceType = in_array(strtolower($os), array('ios', 'android')) ? 'mobile' : 'web';
        $key = "loginstate:{$user_id}";
        $state = $this->redis->hMGet($key, ["exclusive_{$deviceType}", 'status', 'update_v']);

        if (empty($state) || $state['status'] === '0') return $this->setError('登录状态失效');
        if ($state['status'] != '1') {
            $tmpUser = Db::name('user')->where(['user_id' => $user['user_id']])->field('user_id,status,disable_time,disable_length,disable_desc')->find();
            if ($tmpUser['status'] != '1') {
                $tmpData = [
                    'disable_desc' => $tmpUser['disable_desc'],
                    'enable_time' => '',
                    'disable_length' => '永久'
                ];
                $disable_length = $tmpUser['disable_length'];
                $desc = (empty($tmpUser['disable_desc']) ? '' : ('被封原因：' . $tmpUser['disable_desc'] . '。'));
                if (empty($disable_length)) {
                    return $this->setError('您的账号已被永久封禁！' . $desc, 2, $tmpData);
                } else {
                    $enableTime = strtotime('+' . $disable_length, $tmpUser['disable_time']);
                    $tmpData['enable_time'] = date('Y-m-d H:i:s', $enableTime);
                    $arr1 = ['seconds', 'minutes', 'hours', 'days', 'months', 'years'];
                    $arr2 = ['秒', '分钟', '小时', '天', '个月', '年'];
                    $str = preg_replace('/\s+/', '', str_replace($arr1, $arr2, $disable_length));
                    $tmpData['disable_length'] = $str;
                    if (time() <= $enableTime) {
                        return $this->setError("您的账号已被封禁{$str}！" . $desc, 2, $tmpData);
                    }
                }
            }
        }
        if (!$state["exclusive_{$deviceType}"] || $state["exclusive_{$deviceType}"] != $user['exclusive']) {
            $coreSdk = new CoreSdk();
            $coreSdk->post('user/forced_offline', ['user_id' => $user_id, 'device_type' => $deviceType, 'active_meid' => APP_MEID, 'active_access_token' => ACCESS_TOKEN, 'os' => $os]);
            return $this->setError('强制下线,账号已在其他客户端登录');
        }
        return $state;
    }

    //获取用户设置项
    public function getUserSetting($user_id, $name = null)
    {
        $sdk = new CoreSdk();
        $user = $sdk->post('user/get_user', array('user_id' => $user_id));
        if (!$user) return null;
        return $user[$name];
    }

    public function verification($inputData)
    {
        if (!$inputData['idcard_type']) return $this->setError('证件类型必须选择');
        if (!$inputData['front_idcard']) return $this->setError('证件正面必须上传');
        if (!$inputData['back_idcard']) return $this->setError('证件反面必须上传');
        if (!$inputData['hand_idcard']) return $this->setError('手持证件必须上传');
        if (empty($inputData['card_num'])) return $this->setError('证件号不能为空');
        if (empty($inputData['name'])) return $this->setError('真实姓名不能为空');
        //if (!validate_idcard($inputData['card_num'])) return $this->setError('证件号不正确');
        $where['user_id'] = $inputData['user_id'];
        $user_verified = Db::name('user_verified')->where($where)->order('id desc')->find();
        if (count($user_verified) > 0 && $user_verified['status'] != '2') {
            $verified = Db::name('user')->where($where)->value('verified');
            return ['verified' => (string)$verified];
        }
        //查询是否有该身份证号的已通过的实名
        $has_verifed_sfz = Db::name('user_verified')->where(['card_num' => $inputData['card_num'], 'status' => '1'])->find();
        if (!empty($has_verifed_sfz)) return $this->setError('该身份证号已实名');

        $is_anchor = 0;
        if (isset($inputData['is_anchor'])) $is_anchor = $inputData['is_anchor'];

        $insert = [
            'user_id' => $inputData['user_id'],
            'name' => $inputData['name'],
            'card_num' => $inputData['card_num'],
            'status' => '0',
            'create_time' => time(),
            'front_idcard' => $inputData['front_idcard'],
            'back_idcard' => $inputData['back_idcard'],
            'hand_idcard' => $inputData['hand_idcard'],
            'idcard_type' => $inputData['idcard_type'],
            'addr_code' => '',
            'birth' => '0000-00-00',
            'sex' => '',
            'length' => '',
            'check_bit' => '',
            'addr' => '',
            'province' => '',
            'city' => '',
            'area' => '',
            'is_anchor' => $is_anchor,
        ];

        if (config('app.certification_setting.cert_on')) {
            //$http = new HttpClient();

            if (strcasecmp($inputData['idcard_type'], 'SFZ') == 0) {
                $isauto = 1;
                try {
                    $secretId = config('app.certification_setting.app_key');
                    $secretKey = config('app.certification_setting.app_secret');
                    $point = config('app.certification_setting.service_gateway');
                    $cred = new Credential($secretId, $secretKey);
                    $httpProfile = new HttpProfile();
                    $httpProfile->setEndpoint($point);

                    $clientProfile = new ClientProfile();
                    $clientProfile->setHttpProfile($httpProfile);
                    $client = new FaceidClient($cred, "", $clientProfile);

                    $req = new IdCardOCRVerificationRequest();
                    $params = array(
                        "ImageUrl" => $inputData['front_idcard']
                    );
                    $req->fromJsonString(json_encode($params));

                    //IdCardOCRVerification
                    $resp = $client->IdCardOCRVerification($req);
                    $result = json_decode($resp->toJsonString(), true);
                    if ($result['Result'] != "0") return $this->setError($result['Description']);
                    if (empty($result['Name']) || $result['Name']!=$inputData['name'])  throw new Exception("姓名不一致");
                    if (empty($result['IdCard']) || $result['IdCard']!=$inputData['card_num'])  throw new Exception("身份证号不一致");
                } catch (TencentCloudSDKException $e) {
                    $isauto = 0;
                    $insert['handle_desc'] = $e->getMessage();
                } catch (Exception $e) {
                    $isauto = 0;
                    $insert['handle_desc'] = $e->getMessage();
                }

                if (!empty($isauto)) {
                    $insert['status'] = '1';
                    $insert['handle_desc'] = '系统自动通过';
                    $insert['handle_time'] = time();
                    $res = Db::name('user_verified')->insertGetId($insert);
                    if (!$res) return $this->setError('认证失败3');

                    //直接认证通过
                    $update['verified'] = '1';
                    $verified = '1';
                    $res2 = Db::name('user')->where(array('user_id' => $inputData['user_id']))->update($update);
                    if (!$res2) return $this->setError('认证失败4');
                    $core = new CoreSdk();
                    $core->post('user/update_redis', array(
                        'user_id' => $inputData['user_id'],
                        'verified' => $verified
                    ));

                    return ['verified' => $verified];
                }
            }

            /*if (isset($details)) {
                $insert['addr_code'] = $details['addrCode'];
                $insert['birth'] = $details['birth'];
                $insert['sex'] = $details['sex'];
                $insert['length'] = $details['length'];
                $insert['check_bit'] = $details['checkBit'];
                $insert['addr'] = $details['addr'];
                $insert['province'] = $details['province'];
                $insert['city'] = $details['city'];
                $insert['area'] = $details['area'];
            }*/
        }

        $res = Db::name('user_verified')->insertGetId($insert);
        if (!$res) return $this->setError('认证失败3');

        $workService = new Work();
        $aid = $workService->allocation('user_verified', USERID, $res);
        $upd['aid'] = $aid;
        Db::name('user_verified')->where(array('id' => $res))->update($upd);
        $update['verified'] = '2';
        $verified = '2';

        $res2 = Db::name('user')->where(array('user_id' => $inputData['user_id']))->update($update);
        if (!$res2) return $this->setError('认证失败4');
        $core = new CoreSdk();
        $core->post('user/update_redis', array(
            'user_id' => $inputData['user_id'],
            'verified' => $verified
        ));
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.verified', ['user_id' => $inputData['user_id'], 'real_name' => $inputData['name']]);
        return ['verified' => $verified];
    }

    public function getWeekMillet($userId, $time = null)
    {
        $nodes = DateTools::getWeekNodes('d', $time, 'Ymd');
        $redis = RedisClient::getInstance();
        $total = 0;
        foreach ($nodes as $node) {
            $key = "kpi:anchor:all:millet:d:{$node}";
            $millet = $redis->zScore($key, $userId);
            if ($millet) $total += $millet;
        }
        return $total;
    }

    /**
     * 获取用户等级进度
     * @param $user
     * @return float
     * @throws Exception
     */
    public function getUserLevelProcess($user)
    {
        if (!is_array($user) && is_numeric($user)) {
            $user = $this->redis->get('user:' . $user);

            $user = empty($user) ? Db::name('user')->where('user_id', $user)->find() : json_decode($user, true);
        }

        if (!isset($user['level']) || !isset($user['exp'])) {
            throw new Exception('not level filed or exp filed');
        }

        if ($this->redis->sismember('robot_sets', $user['user_id'])) return 0;

        $level = $this->getUserLevel();

        $nextLevelScore = 0;

        $currentLevelScore = 0;

        foreach ($level as $level_name => $level_up) {
            if ($user['level'] == $level_name) $currentLevelScore = $level_up;

            if ($user['level'] < $level_name) {
                $nextLevelScore = $level_up;
                break;
            }
        }

        $rate = round((($user['exp'] - $currentLevelScore) / ($nextLevelScore - $currentLevelScore)), 2);

        return $rate < 0 ? 0 : $rate;
    }

    /**
     * 获取主播等级进度
     * @param $user
     * @return float
     * @throws Exception
     */
    public function getAnchorLevelProcess($user)
    {
        if (!is_array($user) && is_numeric($user)) {
            $user = Db::name('anchor')->where('user_id', $user)->find();
        } else if (!isset($user['anchor_lv']) || !isset($user['anchor_exp'])) {
            $user = Db::name('anchor')->where('user_id', $user['user_id'])->find();
        }

        if (empty($user['anchor_lv']) || empty($user['anchor_exp'])) return 0;

        $level = $this->getAnchorLevel();
        $nextLevelScore = 0;
        $currentLevelScore = 0;

        foreach ($level as $level_name => $level_up) {
            if ($user['anchor_lv'] == $level_name) $currentLevelScore = $level_up;

            if ($user['anchor_lv'] < $level_name) {
                $nextLevelScore = $level_up;
                break;
            }
        }

        $rate = round((($user['anchor_exp'] - $currentLevelScore) / ($nextLevelScore - $currentLevelScore)), 2);
        return $rate < 0 ? 0 : $rate;
    }


    /**
     * 主播等级
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getUserLevel()
    {
        $level = $this->redis->zrange('config:exp_level', 0, -1, true);

        if (empty($level)) {
            $level = Db::name('exp_level')->field('name, level_up')->select();
            foreach ($level as $key => $val) {
                $this->redis->zadd('config:exp_level', $val['level_up'], $val['name']);
            }
            $level = $this->redis->zrange('config:exp_level', 0, -1, true);
        }

        return $level;
    }


    /**
     * 用户等级
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAnchorLevel()
    {
        $level = $this->redis->zrange('config:anchor_level', 0, -1, true);

        if (empty($level)) {
            $level = Db::name('anchor_exp_level')->field('name, level_up')->select();

            foreach ($level as $key => $val) {
                $this->redis->zadd('config:anchor_level', $val['level_up'], $val['name']);
            }

            $level = $this->redis->zrange('config:anchor_level', 0, -1, true);
        }

        return $level;
    }

    public function getImpression($inputData)
    {
        if (empty($inputData)) return '';
        $all_impression = Db::name('impression')->field('id, name, color')->where(['status' => 1, 'type' => 1])->select();
        if (empty($all_impression)) return '';
        $presssion_name = '';
        foreach ($all_impression as $key => $value) {
            if (in_array($value['id'], $inputData)) {
                $presssion_name = $value['name'] . ',' . $presssion_name;
            }
        }
        return rtrim($presssion_name, ',');
    }

    public function getFans($inputData)
    {
        if (empty($inputData)) return [];
        $where['parent_id'] = $inputData['user_id'];
        $whereOr = [];
        if ($inputData['level'] != 'all') {
            $where['level'] = $inputData['level'];
        }

        if (!empty($inputData['keyword'])) {
            $userModel = new \bxkj_module\service\User();
            $userfind  = $userModel->searchByname($inputData['keyword'], $inputData['user_id']);
            if (!empty($userfind)) {
                $findUserId = [];
                foreach ($userfind as $key => $value) {
                    $findUserId[] = $value['user_id'];
                }
                $where['uid'] = $findUserId;
            } else {
                return [];
            }
        }

        $res = Db::name('user_rank')->where($where)->where($whereOr)->page($inputData['page'], $inputData['length'])->order('id desc')->select();

        if (empty($res)) return [];
        $userIds = array_unique(array_column($res, 'uid'));
        $userService = new User();
        $userList = $userService->getUsers($userIds);
        $userList = $userList ? $userList : [];
        return $userList;
    }
}