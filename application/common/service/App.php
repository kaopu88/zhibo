<?php

namespace app\common\service;

use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;
use think\facade\Request;

class App extends Service
{
    const INIT_TOKEN = APP_SECRET_KEY;
    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
    }

    public function checkInitToken($sign, $inputData)
    {
        $meid = ClientInfo::get('meid');//设备ID
        $time = $inputData['time'];//时间戳
        $v = ClientInfo::get('v');//内部版本号
        $channel = ClientInfo::get('channel');//渠道标识符
        if (empty($sign) || empty($meid)) return false;
        $tmp = sha1(self::INIT_TOKEN . $meid . $time . $v . $channel);
        return ($sign === $tmp);
    }

    public function refreshToken($inputData)
    {
        if (!ClientInfo::has('v') || !ClientInfo::has('meid')) {
            return $this->setError('缺少APP版本信息或者设备码');
        }
//        if (!$this->checkInitToken($inputData['sign'], $inputData)) {
//            return $this->setError('初始化签名错误');
//        }
        $session = ClientInfo::getInfo();
        $access_token = DsSession::init($session);
        $session['access_token'] = $access_token;
        if (!empty($session['meid'])) {
            //删除设备上老的access_token
            $old = $this->redis->get('rel_meid:' . $session['meid']);
            if ($old) {
                $this->redis->del("access_token:{$old}");
            }
            $this->redis->set('rel_meid:' . $session['meid'], $access_token);
        }
        return $session;
    }

    public function init($inputData)
    {
        if (!ClientInfo::has('v') || !ClientInfo::has('meid')) {
            return $this->setError('缺少APP版本信息或者设备码');
        }
        $session = DsSession::get();

        if (empty($session) || empty($session['access_token']) || empty(ACCESS_TOKEN)) {
            $session = $this->refreshToken($inputData);
            $i = 0;
            while (!$session) {
                //防止生成失败 一般来说是不会执行的
                $session = $this->refreshToken($inputData);
                $i = $i + 1;
                if ($i >= 5) break;
            }
            if (!$session) return $this->setError('token 生成失败');
        }
        $session = array_merge((is_array($session) ? $session : []), ClientInfo::getInfo());
        DsSession::set(null, $session);
        $tmp = array(
            'login_status' => '0',
            'access_token' => $session['access_token'],
            'meid' => $session['meid'],
            'channel' => $session['channel'],
            'v' => ClientInfo::get('v'),
        );
        //加载安装包信息
        $packagesModel = new Packages();
        $lastVersion = $packagesModel->getLastVersion(array(
            'channel' => ClientInfo::get('channel'),
            'os' => strtolower(ClientInfo::get('os_name'))
        ));
        if ($lastVersion) {
            $tmp['last_code'] = $lastVersion['code'];
            $tmp['last_package'] = $lastVersion;
        }
        //用户信息
        $user = null;
        $tmp['unread_total'] = 0;
        if (!empty($session['user'])) {
            $user = $session['user'];
            $user['user_id'] = (string)$user['user_id'];
            User::safeFiltering($user);
            $tmp['user'] = $user;
            $tmp['login_status'] = '1';
            $messageService = new Message();
            $createTime = preg_match('/\D/', trim($user['create_time'])) ? strtotime($user['create_time']) : $user['create_time'];
            $tmp['unread_total'] = $messageService->getUnreadTotalCache('', $user['user_id'], 'all', $createTime);
        }
        //获取启动页广告
        $coreSdk = new CoreSdk();
        $purview = $user ? $user['purview'] : '*,not_login';
        $ad = $coreSdk->post('ad/get_contents', array(
            'space' => 'app_start',
            'purview' => $purview,
            'city_id' => '',
            'os' => strtolower(ClientInfo::get('os_name')),
            'code' => ClientInfo::get('v_code'),
            'multi' => '1',
            'client_seri' => ClientInfo::encode()
        ));
        $tmp['app_start'] = (is_array($ad) && $ad['app_start']) ? $ad['app_start'] : (object)[];
        Db::name('start_log')->insertGetId([
            'access_token' => empty(ACCESS_TOKEN) ? '' : ACCESS_TOKEN,
            'user_id' => $user ? $user['user_id'] : 0,
            'v_code' => ClientInfo::get('v_code'),
            'network_status' => ClientInfo::get('network_status'),
            'os_name' => ClientInfo::get('os_name'),
            'os_version' => ClientInfo::get('os_version'),
            'channel' => ClientInfo::get('channel'),
            'longitude' => ClientInfo::get('longitude'),
            'latitude' => ClientInfo::get('latitude'),
            'client_ip' => Request::ip(),
            'brand_name' => ClientInfo::get('brand_name'),
            'device_model' => ClientInfo::get('device_model'),
            'meid' => ClientInfo::get('meid'),
            'device_type' => ClientInfo::get('device_type'),
            'start_time' => time()
        ]);

        //有几率的清空一个月前的启动记录
        if (mt_rand(0, 100) <= 10) {
            $expire = strtotime("-1 months", time());
            Db::name('start_log')->where([['start_time', 'elt', $expire]])->delete();
        }

        return $tmp;
    }


}