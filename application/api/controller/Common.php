<?php

namespace app\api\controller;

use app\common\controller\Controller;
use app\common\service\App;
use app\common\service\DsSession;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;
use app\core\service\Wsyun;
use Wcs\Upload\Uploader;
use Wcs\Http\PutPolicy;

class Common extends Controller
{
    public function appinit()
    {
        $appService = new App();
        $params = input();
        ClientInfo::refresh($params);
        $result = $appService->init($params);
        if (!$result) return $this->jsonError($appService->getError());
        //一些初始化信息
        $configApp = config('app.');
        $h5Urls = $configApp['h5_urls'];

        foreach ($h5Urls as &$h5Url) {
            $h5Url = parse_tpl($h5Url, array(
                'h5_service_url' => H5_URL,
                'api_service_url' => API_URL,
                'core_service_url' => CORE_URL,
                'v' => ClientInfo::get('v'),
                'user_id' => isset($result['user']) ? $result['user']['user_id'] : '',
            ));
        }
        $result['h5_urls'] = $h5Urls;
        $testArr = isset($configApp['test_user']) ? $configApp['test_user'] : [];
        $result['show_type'] = in_array(USERID, $testArr) ? '0' : '1';//0不显示 1显示
        $redis = RedisClient::getInstance();
        $maxLevel = $redis->zrevrange('config:exp_level', 0, 0);
        $result['max_level'] = (int)$maxLevel[0];
        $result['app_base_info'] = [
            'app_name' => APP_NAME,
            'app_prefix_name' => APP_PREFIX_NAME,
            'app_recharge_unit' => APP_BEAN_NAME,
            'app_millet_unit' => APP_MILLET_NAME,
            'app_balance_unit' => APP_BALANCE_NAME,
            'app_settlement_unit' => APP_SETTLEMENT_NAME,
            'app_account_name' => APP_ACCOUNT_NAME,
            'contact_tel' => APP_SERVICE_TEL,
            'live_room_name' => isset($configApp['live_setting']['live_room_name']) ? $configApp['live_setting']['live_room_name'] : '秉信',
            'agent_name' => isset($configApp['agent_setting']['agent_name']) ? $configApp['agent_setting']['agent_name'] : '公会',
        ];
        $result['phone_code'] = ['area' => '中国', 'code' => '86'];
        $result['open_anchor_type'] = isset($configApp['live_setting']['user_live']['open_anchor_type']) ? $configApp['live_setting']['user_live']['open_anchor_type'] : 0;
        $result['refresh_text'] = isset($configApp['product_setting']['refresh_text']) ?  $configApp['product_setting']['refresh_text'] : ['加载中'];

        $result['alert_detail'] = [
            'alert_title' => isset($configApp['product_setting']['alert_title']) ?  $configApp['product_setting']['alert_title'] : '服务协议和隐私政策',
            'alert_content' => isset($configApp['product_setting']['alert_content']) ?  $configApp['product_setting']['alert_content'] : '内容',
            'login_private_title' => isset($configApp['product_setting']['login_private_title']) ?  $configApp['product_setting']['login_private_title'] : '《隐私政策》',
            'login_private_url' =>  isset($configApp['product_setting']['login_private_url']) ? H5_URL . $configApp['product_setting']['login_private_url'] : '',
            'login_service_title' => isset($configApp['product_setting']['login_service_title']) ?  $configApp['product_setting']['login_service_title'] : '《服务协议》',
            'login_service_url' => isset($configApp['product_setting']['login_service_url']) ? H5_URL . $configApp['product_setting']['login_service_url'] : '',
        ];

        $result['charge_rec_duration'] = isset($configApp['app_setting']['charge_video_duration']) ? $configApp['app_setting']['charge_video_duration'] : '';
        $nav = [
            //['id' => 0, 'type' => 'hot', 'name' => '热门', 'icon' => ''],
            //['id' => 0, 'type' => 'newest', 'name' => '最新', 'icon' => ''],
            //['id' => 0, 'type' => 'pk', 'name' => 'PK', 'icon' => ''],
        ];
        $channels = $redis->get('cache:live_channel');
        if (!$channels) {
            $channels = Db::name('live_channel')->where(['status' => '1', 'parent_id' => 0])->order('sort_order desc,id asc')->field('id,name,icon')->select();
            $channels = json_encode($channels);
            $redis->set("cache:live_channel", $channels, 3600);
        }
        $channels = json_decode($channels, true) ?: [];

        foreach ($channels as $channel) {
            //$channel['type'] = 'list';
            $nav[] = $channel;
        }
        $result['live_nav'] = $nav;
        $result['water_marker'] = '';
        $configSite = config('site.');
        $result['ios_app_hidden'] = $configSite['ios_app_hidden'] ? $configSite['ios_app_hidden'] : '0';
        $result['ios_app_hidden_version'] = $configSite['ios_app_hidden_version'] ? $configSite['ios_app_hidden_version'] : '0';
        $result['android_app_hidden'] = (int)$configSite['android_app_hidden'] ? (int)$configSite['android_app_hidden'] : 0;
        $result['register'] = [
            'one_key_login' => $configSite['one_key_login'] ? $configSite['one_key_login'] : '0',
            'invite_code' => $configSite['invite_code'] ? $configSite['invite_code'] : '0',
            'register_type' => $configSite['register_type'] ? $configSite['register_type'] : '0',
        ];

        $result['customer_service'] = [
            'type' => config('message.bxkj_customer_service.type') ? config('message.bxkj_customer_service.type') : '0',
            'link' => config('message.bxkj_customer_service.link') ? config('message.bxkj_customer_service.link') : ''
        ];
        $result['teenager'] = [
            'teenager_model_switch' => $configSite['teenager_model_switch'] ? $configSite['teenager_model_switch'] : '0',
        ];
        $result['distribute'] = [
           'distribute_status' => config('giftdistribute.is_open') ? config('giftdistribute.is_open') : '0',
           'distribute_name' => config('giftdistribute.name') ? config('giftdistribute.name') : '佣金',
        ];
        $result['firstinvest'] = [
            'firstinvest_status' => '0',
        ];

        $activeConfig = config('activity.');

        $messageConfig = config('message.aomy_private_letter');
        $result['activity'] = [
            'lottery_status' => $activeConfig['lottery_is_open'] ? $activeConfig['lottery_is_open'] : '0',
            'red_packet_status' => $activeConfig['red_packet_is_open'] ? $activeConfig['red_packet_is_open'] : '0',
            'voice_setting' => isset($configApp['live_setting']['voice_setting']['status']) ? $configApp['live_setting']['voice_setting']['status'] : '0',
            'is_dynamic_open' => config('friend.is_open') ? config('friend.is_open') : '0',
            'is_user_task_open' =>  isset($configApp['new_task']['is_open']) ? $configApp['new_task']['is_open'] : '0',
            'agent_front_status' => isset($configApp['agent_setting']['agent_front_status']) ? $configApp['agent_setting']['agent_front_status'] : '0',
            'team_status' => $configSite['team_status'] ? $configSite['team_status'] : '0',
            'private_letter_status' => isset($messageConfig['private_letter_status']) ? $messageConfig['private_letter_status'] : '0',
            'private_ios_letter_status' => isset($messageConfig['private_ios_letter_status']) ? $messageConfig['private_ios_letter_status'] : '0',
        ];
        $result['shop'] = [
            'user_shop' => config('taoke.user_shop') ? config('taoke.user_shop') : '0',
            'is_shop_open' => isset($configApp['live_setting']['is_shop_open']) ? $configApp['live_setting']['is_shop_open'] : '0',
            'is_goods_open' => isset($configApp['live_setting']['is_goods_open']) ? $configApp['live_setting']['is_goods_open'] : '0'
        ];
        $result['private_chat_level'] = 0;
        return $this->success($result, '初始化成功');
    }

    //APP刷新TOKEN
    public function refreshToken()
    {
        $appService = new App();
        $result = $appService->refreshToken(input());
        if (!$result) return $this->jsonError($appService->getError());
        return $this->success($result, '刷新成功');
    }

    //发送短信接口
    public function sendSmsCode()
    {
        $scene = input('scene');
        $phone = input('phone');
        $phoneCode = input('phone_code');
        if (empty($scene)) return $this->jsonError('请输入场景值');
        $sceneConfig = enum_array('sms_code_scenes', $scene);
        if (empty($sceneConfig)) return $this->jsonError('场景值不合法');
        if ($sceneConfig['bind'] == 1) {
            if (empty(USERID)) return $this->jsonError('请先登录', 1003);
            $phone2 = DsSession::get('user.phone');
            $phoneCode2 = DsSession::get('user.phone_code');
            if (empty($phone2)) return $this->jsonError('您还没有绑定手机号');
            if (isset($phone) && ($phone != $phone2 || $phoneCode != $phoneCode2)) return $this->jsonError('输入的手机号和绑定的手机号不一致');
            $phone = $phone2;
            $phoneCode = $phoneCode2;
        }
        if (empty($phone) || !validate_regex($phone, 'phone')) return $this->jsonError('手机号不合法');
        $sdk = new CoreSdk();
        $result = $sdk->post('common/send_sms_code', array(
            'phone' => $phone,
            'scene' => $scene,
            'phone_code' => $phoneCode ? $phoneCode : '86',
            'client_seri' => ClientInfo::encode()
        ));
        if (!$result) return $this->jsonError($sdk->getError());
        return $this->success($result, '发送成功');
    }

    //获取七牛上传文件凭证
    public function getQiniuToken()
    {
        $type = input('type');
        $filename = input('filename');
        $storer = input('storer');
        if (empty($type)) return $this->jsonError('上传类型不能为空');
        if (empty($filename)) return $this->jsonError('文件名不能为空');
        $sdk = new CoreSdk();
        $result = $sdk->post('common/get_qiniu_token', array(
            'type' => $type,
            'filename' => $filename,
            'user_id' => defined('USERID') ? USERID : '',
            'user_key' => 'user_id',
            'storer'=>$storer
        ));
        if (!$result) return $this->jsonError($sdk->getError()->message, 1);
        return $this->success($result, '获取成功');
    }

    /**
     * 获取腾讯云上传凭证
     */
    public function getQcludSign()
    {   
        
       
        require_once ROOT_PATH . '/vendor/wcs-php-sdk-2.0.9/autoload.php';
        // $accessKey = 'Ef6VI6yQfP1DBtN0XCAxKkXK5ohJODTBVeqM';
        // $secretKey = 'KekpeWY1BUuik2MYZbieEubcgYT3nmVdVR2lYJhocLhr81ajD8XrpMEss5XrrZuB';
        // $bucket = $bucketName    = 'bingxinzhibo1';
        // $fileKey = md5(time()).'.png';
        
        
        // $dea = round(1000 * (microtime(true) + 3600), 0);
        // $putPolicy['scope'] = $bucketName.':'.$fileKey;
        // $putPolicy['deadline'] = $dea;
        // $putPolicy['returnBody'] = 'url=$(url)&fsize=$(fsize)&bucket=$(bucket)&key=$(key)';
        // $putPolicy = json_encode($putPolicy);
        // $encodePutPolicy  = urlsafe_base64_encode($putPolicy);
        // $Sign =  hash_hmac('sha1', $encodePutPolicy, $secretKey, false);
        // $encodeSign = urlsafe_base64_encode($Sign);
        // $uploadToken = $accessKey.':'.$encodeSign.':'.$encodePutPolicy;
        // var_dump($putPolicy);
        // var_dump($uploadToken);
        
        // $pp = new PutPolicy();
 
        // if ($fileKey == null || $fileKey === '') {
        //     $pp->scope = $bucketName;
        // } else {
        //     $pp->scope = $bucketName . ':' . $fileKey;
        // }
        // $pp->returnBody = 'url=$(url)&fsize=$(fsize)&bucket=$(bucket)&key=$(key)';
        // // $pp->deadline = '<timestamp>';
        //  $upToken = $token  = $pp->get_token();

        // $localFile = $_FILES['file'];
        // $file = request()->file('file');
        // $info = $file->validate(['size'=>2000*1024,'ext'=>'jpg,gif,png,jpeg']);
       
        // //保存到站点目录下
        // $rootPath='uploads/diary';
        // $info = $info->move($rootPath);
        // $saveFileName=$info->getSaveName();
        // $savePath=$rootPath.'/'.str_replace('\\','/',$saveFileName);
        // // $url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
        // // $savePath = $url.'/'.$savePath;
     
        // if(!file_exists($savePath)) {
        //     die("ERROR: {$savePath}文件不存在！");
        // }
        // $client = new Uploader($token);
        // $resp = $client->upload_return($savePath);
        // var_dump($resp);
        // $str = base64_decode($resp->respBody,true);
        // var_dump($str);die;
        //         测试
        // $filename = '唯美视频素材冷暖对比的沙滩与海和独木船-国外素材网.mp4';
        
        $requestPayload = '{"videoName":"a","pageSize":"5","pageIndex":"2"}';

        // 计算 SHA256 哈希
        // $hash = hash('sha256', $requestPayload, false);
        
        // // 十六进制编码
        // // $hexEncoded = bin2hex($hash);
        
        // // 转换为小写字母
        // $lowercase = strtolower($hash);
        
        // echo $lowercase;
        // die;
        
        // $json = '{"videoName":"a","pageSize":"5","pageIndex":"2"}';
        // $json = 'videoName=a&pageIndex=2&pageSize=5';
        
        // $hash = hash('sha256',$json,false);
        // var_dump($hash);
        // $bin2 = bin2hex($hash);
        // var_dump($bin2);
        // $aa =  strtolower($bin2);
        // var_dump($aa);die;
        // $filename = 'aaa.mp4';
        // $Qcloud = new Wsyun($filename);
        // $res = $Qcloud->getQcloudVodSign();
        // var_dump($res);die;
        if (empty(USERID)) return $this->jsonError('需要先登录', 1003);
        $carry_msg = input('carry_msg');
        $task_id = input('task_id');
        $sdk = new CoreSdk();
        $user_info = $sdk->getUser(USERID);
        if ($user_info['film_status'] != '1') return $this->jsonError('视频上传功能受限');
        $res = $sdk->post('common/get_qcloud_token', [
            'carry_msg' => $carry_msg,
            'task_id' => $task_id,
        ]);
        if (!$res) return $this->jsonError($sdk->getError());
        return $this->success(['sign' => $res], '获取成功');
    }

    //获取中国省市区
    public function getRegionTree()
    {
        $sdk = new CoreSdk();
        $res = $sdk->post('common/get_region_tree', ['id' => 1]);
        if (!$res) return $this->jsonError($sdk->getError()->message, 1);
        return $this->success($res, '获取成功');
    }

    public function getPhoneCodes()
    {
        $configPath = ROOT_PATH . 'data/phone_area_code.json';
        $json = file_get_contents($configPath);
        $arr = json_decode($json, true);
        return $this->success($arr, '获取成功');
    }

    public function getNotices()
    {
        $mark = input('mark');
        if (empty($mark)) return $this->jsonError('设备标记不能为空');
        $coreSdk = new CoreSdk();
        $ad = $coreSdk->post('ad/get_contents', [
            'space' => 'app_index_notice',
            'client_seri' => ClientInfo::encode()
        ]);
        $arr = [];
        if (!$ad) return $this->success($arr, '获取成功');
        $redis = RedisClient::getInstance();
        $contents = $ad['contents'] ? $ad['contents'] : [];
        $key = "notice_view:index:{$mark}";//浏览记录
        $members = $redis->sMembers($key);
        $ids = [];//现在的公告ID集合
        foreach ($contents as $content) {
            $id = $content['id'];
            $content['image_info'] = [];
            if (!empty($content['image'])) {
                foreach ($content['image'] as $key => $item) {
                    $itemSha1 = sha1($item);
                    $key2 = "notice_imagesize:{$itemSha1}";
                    $imageInfo = cache($key2);
                    if (empty($imageInfo)) {
                        $imageInfo = getimagesize($item);
                        cache($key2, $imageInfo);
                    }
                    $content['image_info'][$key] = [
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1],
                        'url' => $item
                    ];
                }
            }
            if (!in_array($id, $members)) {
                $arr[] = $content;
            }
            $ids[] = $id;
        }
        //移除已废弃的公告ID
        foreach ($members as $member) {
            if (!in_array($member, $ids)) {
                $redis->sRem($key, $member);
            }
        }
        return $this->success($arr, '获取成功');
    }

    public function closeNotice()
    {
        $id = input('id');
        $mark = input('mark');
        if (empty($mark)) return $this->jsonError('设备标记不能为空');
        if (empty($id)) return $this->jsonError('公告ID不能为空');
        $adContent = Db::name('ad_content')->where(['id' => $id, 'delete_time' => null])->find();
        if (!$adContent) return $this->jsonError('公告不存在');
        if ($adContent['allow_close'] != '1') return $this->jsonError('不允许关闭');
        $redis = RedisClient::getInstance();
        $key = "notice_view:index:{$mark}";//浏览记录
        $addRes = $redis->sAdd($key, $id);
        $res = 0;
        if ($addRes) {
            $res = Db::name('ad_content')->where(['id' => $id])->setInc('pv', 1);
        }
        return $this->success($res, '记录成功');
    }

    public function networkStatusLog()
    {
        $params = input();
        if (!isset($params['upload_rate']) || !isset($params['download_rate'])) return $this->jsonError('速率参数不完整');
        if (!preg_match('/^[\.\d]+$/', $params['upload_rate']) || !preg_match('/^[\.\d]+$/', $params['download_rate'])) {
            return $this->jsonError('速率参数不正确');
        }
        if (!in_array($params['scene'], ['live'])) return $this->jsonError('场景参数不正确');
        $data = [
            'scene' => $params['scene'] ? $params['scene'] : '',
            'v_code' => defined('APP_CODE') ? APP_CODE : 0,
            'user_id' => defined('USERID') ? USERID : 0,
            'access_token' => ACCESS_TOKEN,
            'meid' => defined('APP_MEID') ? APP_MEID : '',
            'upload_rate' => $params['upload_rate'] ? $params['upload_rate'] : 0,
            'download_rate' => $params['download_rate'] ? $params['download_rate'] : 0,
            'network_status' => isset($params['network_status']) ? (string)$params['network_status'] : (ClientInfo::get('network_status')),
            'os_name' => ClientInfo::get('os_name'),
            'os_version' => ClientInfo::get('os_version'),
            'brand_name' => ClientInfo::get('brand_name'),
            'device_model' => ClientInfo::get('device_model'),
            'client_ip' => Request::ip(),
            'num' => (int)$params['num'],
            'create_time' => time()
        ];
        $id = Db::name('network_status')->insertGetId($data);
        if (!$id) return $this->jsonError('记录失败');
        return $this->success($id, '记录成功');
    }
    
    
    public function getPushAndPullUrl()
    {
        $config = get_live_config(); //直播配置

        $stream = '56233_10003876_1571916799';

        list(, ,$time) = explode('_', $stream);

        $secret_key = $config['secret_key'];

        $pull = $config['pull'];

        $stream_prefix = $config['stream_prefix'];

        $ext_time = $config['ext'];

        $ext = strtoupper(dechex($time+$ext_time));

        $secret = md5($secret_key . $stream . $ext);

        $s = sprintf('rtmp://%s/live/%s?bizid=%s&txSecret=%s&txTime=%s', $pull, $stream, $stream_prefix, $secret, $ext);

        return $this->success(['s' => $s]);
    }

    public function getprivate()
    {
        $configApp = config('app.');
        $result = [
            'alert_title' => isset($configApp['product_setting']['alert_title']) ?  $configApp['product_setting']['alert_title'] : '服务协议和隐私政策',
            'alert_content' => isset($configApp['product_setting']['alert_content']) ?  $configApp['product_setting']['alert_content'] : '内容',
            'login_private_title' => isset($configApp['product_setting']['login_private_title']) ?  $configApp['product_setting']['login_private_title'] : '《隐私政策》',
            'login_private_url' =>  isset($configApp['product_setting']['login_private_url']) ? H5_URL . $configApp['product_setting']['login_private_url'] : '',
            'login_service_title' => isset($configApp['product_setting']['login_service_title']) ?  $configApp['product_setting']['login_service_title'] : '《服务协议》',
            'login_service_url' => isset($configApp['product_setting']['login_service_url']) ? H5_URL . $configApp['product_setting']['login_service_url'] : '',
        ];
        return $this->success($result, '获取成功');
    }

    public function getCancelProtocol()
    {
        $configApp = config('app.');
        $string = isset($configApp['product_setting']['cancel_protocol']) ? $configApp['product_setting']['cancel_protocol'] : '';
        return $this->success($string, '获取成功');
    }
}
