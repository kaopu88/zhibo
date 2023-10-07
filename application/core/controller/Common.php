<?php

namespace app\core\controller;

use app\core\service\Region;
use app\core\service\SmsCode;
use DateTime;
use Qiniu\Auth;
use function Qiniu\base64_urlSafeEncode;
use Qiniu\Processing\Operation;
use think\Db;
use think\facade\Request;
use app\core\service\Qcloud;
use Wcs\Http\PutPolicy;
use Wcs\MgrAuth;
use Wcs\Config;


class Common extends Controller
{
    //发送短信验证码
    public function send_sms_code()
    {
        $phoneCode = Request::post('phone_code');
        $phone = Request::post('phone');//手机号
        $scene = Request::post('scene');//场景值
        $params = Request::post('params');//参数
        $smsCodeModel = new SmsCode();
        $result = $smsCodeModel->sendCode($scene, $phone, null, $phoneCode, $params);
        if (!$result) return json_error($smsCodeModel->getError());
        return json_success($result);
    }

    //校验短信验证码
    public function check_sms_code()
    {
        $phoneCode = Request::post('phone_code');
        $phone = Request::post('phone');//手机号
        $scene = Request::post('scene');//场景值
        $code = Request::post('code');
        $smsCodeModel = new SmsCode();
        $res = $smsCodeModel->checkCode($scene, $phone, $code, $phoneCode);
        if (!$res) return json_error($smsCodeModel->getError());
        return json_success('ok');
    }

    //获取七牛上传文件凭证
    public function get_qiniu_token()
    {
        $type = Request::post('type');
        $filename = Request::post('filename');
        $queryStr = Request::post('query');
        $userKey = Request::post('user_key');
        $storer = Request::post('storer');
        $userKey = $userKey ? $userKey : 'user_id';
        $storer = $storer ? $storer : config('upload.platform');
        $platform_config = config('upload.platform_config');
        $userId = Request::post($userKey);//这里的user不单单指的是user表也可能是admin agent_admin
        if (empty($type)) return json_error(make_error('上传类型不能为空'));
        if (empty($filename)) return json_error(make_error('上传文件名不能为空'));
        // if ($storer == 'qiniu') {
        //     $uploadConfig = config('upload.upload_config');
        // } else {
        //     $uploadConfig = config('upload.upload_config_' . $storer);
        // }
        $uploadConfig = config('upload.upload_config');
        if (empty($uploadConfig[$type])) return json_error(make_error('上传类型不存在'));
        $config = array_merge(($uploadConfig['_default'] ? $uploadConfig['_default'] : array()), $uploadConfig[$type]);
        if ($config['is_login'] == '1' && empty($userId)) return json_error(make_error('需要先登录'));
        $result = [];
        if ($storer == 'qiniu')
        {
            $key = make_qiniu_key($filename, $queryStr, $config, $userId ? $userId : 0, $userKey);
            

            $accessKey = $platform_config['access_key'];
            $secretKey = $platform_config['secret_key'];
            $bucket = $platform_config['bucket'];
            
            $auth = new Auth($accessKey, $secretKey);
            $expires = 3600;//有效期一个小时
            $policy = array(
                'callbackUrl' => CORE_URL."/open/qiniu_upload_callback",
                'callbackBody' => '{"key": "$(key)","hash": "$(etag)","fsize": "$(fsize)","bucket": "$(bucket)","name": "$(x:name)","w": "$(imageInfo.width)","h":"$(imageInfo.height)","fname": "$(fname)","mimeType": "$(mimeType)","uuid": "$(uuid)"}',
                'callbackBodyType' => 'application/json',
            );
            if (isset($config['fsizeMin'])) $policy['fsizeMin'] = (int)$config['fsizeMin'];
            if (isset($config['fsizeLimit'])) $policy['fsizeLimit'] = (int)$config['fsizeLimit'];
            if (isset($config['mimeLimit'])) $policy['mimeLimit'] = $config['mimeLimit'];
            $upToken = $auth->uploadToken($bucket, $key, $expires, $policy, true);
            if (!$upToken) return json_error(make_error('make token error'));
            if (strpos($platform_config['base_url'], ','))
            {
                @list($base_url, ) = explode(',', $platform_config['base_url']);
            }
            else{
                $base_url = $platform_config['base_url'];
            }
            $base = $base_url;
            $result['token'] = $upToken;
            $result['key'] = $key;
            $result['expires'] = $expires;
            $result['base'] = $base;
            $result['fsizeMin'] = $policy['fsizeMin'];
            $result['fsizeLimit'] = $policy['fsizeLimit'];
            $result['mimeLimit'] = $policy['mimeLimit'];
            $result['url'] = $base . '/' . ltrim($key, '/');
        }
        else if ($storer == 'tencent') {
            if (!empty($queryStr)) {
                $tmp = upload_query_decode($queryStr);
                $config = array_merge($config, is_array($tmp) ? $tmp : []);
            }
            $qcloud = new Qcloud();
            $source = $config['source'] ? $config['source'] : 'app';
            if (!in_array($source, ['erp', 'app'])) return json_error(make_error('来源不正确'));
            $signature = $qcloud->getQcloudVodSign($config['class_id'], $source, '');
            if (empty($signature)) return json_error(make_error('签名错误[qcloud]'));
            $result['signature'] = $signature;
        }
        else if ( $storer == 'aliyun'){
            $accesskeyid= $platform_config['access_key'];          // 请填写您的AccessKeyId。
            $accesskeysecret= $platform_config['secret_key'];     // 请填写您的AccessKeySecret。
            // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
            $host = $platform_config['bucket'];
            // $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
            $callbackUrl = 'http://88.88.88.88:8888/aliyun-oss-appserver-php/php/callback.php';
            $key = make_qiniu_key($filename, $queryStr, $config, $userId ? $userId : 0, $userKey);

            $aliyun_cdn = config('upload.resource_cdn');

            $callback_param = array('callbackUrl'=>$callbackUrl,
                'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
                'callbackBodyType'=>"application/x-www-form-urlencoded");
            $callback_string = json_encode($callback_param);

            $base64_callback_body = base64_encode($callback_string);
            $now = time();
            $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
            $end = $now + $expire;
            $expiration = $this->gmt_iso8601($end);


            //最大文件大小.用户可以自己设置
            $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
            $conditions[] = $condition;

            // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
            $start = array(0=>'starts-with', 1=>'$key', 2=>$key);
            $conditions[] = $start;


            $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
            $policy = json_encode($arr);
            $base64_policy = base64_encode($policy);
            $string_to_sign = $base64_policy;
            $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $accesskeysecret, true));

            $result = array();
            $result['accessid'] = $accesskeyid;
            $result['host'] = $host;
            $result['policy'] = $base64_policy;
            $result['signature'] = $signature;
            $result['expire'] = $end;
            $result['callback'] = $base64_callback_body;
            $result['key'] = $key;  // 这个参数是设置用户上传文件时指定的前缀。
            $result['url'] = $aliyun_cdn . '/' . $key;
        }else if ($storer == 'wsyun') {
            require_once ROOT_PATH . '/vendor/wcs-php-sdk-2.0.9/autoload.php';
            $key = $fileKey = make_qiniu_key($filename, $queryStr, $config, $userId ? $userId : 0, $userKey);
            
            //国外
            $accessKey = Config::WCS_ACCESS_KEY;
            $secretKey = Config::WCS_SECRET_KEY;
            $pullUrl = Config::PULLURL;
            $upUrl = Config::UPURL;
            $bucket = $bucketName    = Config::BUCKET;
            $expires   = 3600;//有效期一个小时
            $policy    = array(
                'callbackUrl'      => CORE_URL . "/open/qiniu_upload_callback",
                'callbackBody'     => '{"key": "$(key)","hash": "$(etag)","fsize": "$(fsize)","bucket": "$(bucket)","name": "$(x:name)","w": "$(imageInfo.width)","h":"$(imageInfo.height)","fname": "$(fname)","mimeType": "$(mimeType)","uuid": "$(uuid)"}',
                'callbackBodyType' => 'application/json',
            );
            if (isset($config['fsizeMin'])) $policy['fsizeMin'] = (int)$config['fsizeMin'];
            if (isset($config['fsizeLimit'])) $policy['fsizeLimit'] = (int)$config['fsizeLimit'];
            if (isset($config['mimeLimit'])) $policy['mimeLimit'] = $config['mimeLimit'];
            $pp = new PutPolicy();
            if ($fileKey == null || $fileKey === '') {
                $pp->scope = $bucketName;
            } else {
                $pp->scope = $bucketName . ':' . $fileKey;
            }
            $upToken = $token  = $pp->get_token();
            if (!$upToken) return json_error(make_error('make token error'));
            if (strpos($platform_config['base_url'], ',')) {
                @list($base_url,) = explode(',', $platform_config['base_url']);
            } else {
                $base_url = $platform_config['base_url'];
            }
            $base                 = $base_url = $upUrl;
            $result['token']      = $upToken;
            $result['key']        = $key;
            $result['expires']    = $expires;
            $result['base']       = $base;
            $result['host']         = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/h5/common/ws_upload';
            $result['fsizeMin']   = $policy['fsizeMin'];
            $result['fsizeLimit'] = $policy['fsizeLimit'];
            $result['mimeLimit']  = $policy['mimeLimit'];
            $result['url']        = $pullUrl . '/' . ltrim($key, '/');
        }
        return json_success($result);
    }

    //七牛图片裁切工具
    public function qiniu_img_crop()
    {
        $key = Request::post('key');
        $w = Request::post('w');
        $h = Request::post('h');
        $x = Request::post('x');
        $y = Request::post('y');
        $x = isset($x) ? $x : 0;
        $y = isset($y) ? $y : 0;
        if (empty($key)) return json_error(make_error('key不能为空'));
        $platform = config('upload.platform');
        if ($platform != 'qiniu') return json_error(make_error('非七牛存储平台'));
        $platform_config = config('upload.platform_config');
        $accessKey = $platform_config['access_key'];
        $secretKey = $platform_config['secret_key'];
        $bucket = $platform_config['bucket'];
        if (strpos($platform_config['base_url'], ','))
        {
            @list($base, ) = explode(',', $platform_config['base_url']);
        }
        else{
            $base = $platform_config['base_url'];
        }
        $domain = preg_replace('/^http(s)?\:\/\//', '', $base);
        $myOperation = new Operation($domain, null, 3600);
        $cropQuery = "imageMogr2/crop/!{$w}x{$h}a{$x}a{$y}";
        list($filename, $ext) = explode('.', $key);
        $newKey = dirname($filename) . '/' . basename($filename) . '-' . "crop_{$w}_{$h}_{$x}_{$y}.{$ext}";
        $saveasKey = base64_urlSafeEncode("{$bucket}:{$newKey}");
        $fops = [$cropQuery];
        $url = $myOperation->buildUrl($key, $fops);
        $url .= "|saveas/{$saveasKey}";
        $tmpUrl = preg_replace('/^http\:\/\//', '', $url);
        $sign = base64_urlSafeEncode(hash_hmac("sha1", $tmpUrl, $secretKey, true));
        $requestUrl = $url . "/sign/{$accessKey}:" . $sign;
        $result = curl_get($requestUrl, true);
        if (!$result) return json_error(make_error('裁切失败404'));
        $content = json_decode($result['content'], true);
        if (empty($content)) return json_error(make_error('裁切失败,返回数据错误'));
        if ($content['error']) return json_error(make_error($content['error']));
        $content['url'] = $base . '/' . $content['key'];
        return json_success($content);
    }

    //获取腾讯鉴权
    public function get_qcloud_token()
    {
        $params = Request::post();
        $carryMsg = $params['carry_msg'] ? $params['carry_msg'] : 'app';
        if (!in_array($carryMsg, ['erp', 'app'])) return json_error(make_error('来源不正确'));
        $taskId = '';
        $Qcloud = new Qcloud();
        $res = $Qcloud->getQcloudVodSign($taskId, $carryMsg);
        if (is_error($res)) return json_error($res);
        return json_success($res);//直接返回签名
    }

    //获取地址树
    public function get_region_tree()
    {
        $id = Request::post('id');
        $id = $id ? $id : 1;
        $region = new Region();
        $tree = $region->getRegionTree($id, 'ios');
        if ($tree === false) return json_error($region->getError());
        return json_success($tree);
    }

    //存储微信场景值
    public function save_wx_state()
    {
        $data = Request::post('data');
        $appId = Request::post('appid');
        if (empty($appId)) return json_error('appid不存在');
        $state = Db::name('wx_state')->insertGetId(array(
            'appid' => $appId,
            'data' => $data,
            'create_time' => time()
        ));
        if (!$state) return json_error('存储失败');
        return json_success($state, '存储成功');
    }

    //获取微信场景值
    public function get_wx_state()
    {
        $state = input('state');
        if (empty($state)) return json_error('state不存在');
        $result = Db::name('wx_state')->where('id', '=', $state)->find();
        if (empty($result)) return json_error('state不存在');
        return json_success($result['data'], '获取成功');
    }



    protected function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }

}
