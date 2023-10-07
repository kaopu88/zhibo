<?php

namespace app\h5\controller;

use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;
use Wcs\Upload\Uploader;
use Wcs\Http\PutPolicy;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use app\mq\callbacks\VideoPublish;
use app\mq\callbacks\VideoBefore;
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

class Common extends Controller
{
    public function send_code()
    {
        $params = input();
        $scene = $params['scene'];
        $phone = $params['phone'];
        $phoneCode = isset($params['phone_code']) ? $params['phone_code'] : '86';
        if (empty($scene)) return json_error(make_error('请输入场景值'));
        $sceneConfig = enum_array('sms_code_scenes', $scene);
        if (empty($sceneConfig)) return json_error(make_error('场景值不合法'));
        $bind = isset($sceneConfig['bind']) ? $sceneConfig['bind'] : '';
        if ($bind == 1) {
            if (!defined('USERID') || empty(USERID)) return make_error('请先登录', 1003);
            $phone2 = '';
            if (empty($phone2)) return make_error('您还没有绑定手机号');
            if (isset($phone) && $phone != $phone2) return make_error('输入的手机号和绑定的手机号不一致');
            $phone = $phone2;
        }
        if (empty($phone) || !validate_regex($phone, 'phone')) return json_error(make_error('手机号格式不正确'));
        $sdk = new CoreSdk();
        $result = $sdk->post('common/send_sms_code', array(
            'phone' => $phone,
            'scene' => $scene,
            'phone_code' => $phoneCode
        ));
        if (!$result) return json_error($sdk->getError());
        session('last_send_time', $result['send_time']);
        return json_success($result, '发送成功');
    }


    public function virtualOperationalData()
    {
        $now = time();
        $today = strtotime(date("Y-m-d"));

        if ($now < $today + 64800) {
            $yesterday = strtotime(date("Y-m-d", strtotime("-1 day")));
            $threeday = strtotime(date("Y-m-d", strtotime("-2 day")));

            $redis = RedisClient::getInstance();

            $p = $redis->get('cache:virtualOperational');

            $films = Db::name('film')
                ->where(['audit_status' => '1', 'status' => '1'])
                ->where('play_sum', 'lt', 20000)
                ->whereOr('zan_sum', 'lt', 10000)
                ->whereOr('collection_sum', 'lt', 500)
                ->whereOr('share_sum', 'lt', '100')
                ->field('zan_sum, play_sum, collection_sum, share_sum, id, audit_time')
                ->order('audit_time desc')
                ->paginate(['list_rows' => 200, 'page' => !empty($p) ? $p : 1])
                ->toArray();

            if (!empty($films['data'])) {
                foreach ($films['data'] as $key => $film) {
                    if (!empty($film)) {
                        switch (true) {
                            case $film['audit_time'] > $today :
                                $data = $this->getOperationalScore('today', $film);
                                break;

                            case $film['audit_time'] > $yesterday && $film['audit_time'] < ($yesterday + 86399) :
                                $data = $this->getOperationalScore('yesterday', $film);
                                break;

                            case $film['audit_time'] > $threeday && $film['audit_time'] < ($threeday + 86399) :
                                $data = $this->getOperationalScore('threeday', $film);
                                break;

                            default:
                                $data = $this->getOperationalScore('default', $film);
                                break;
                        }

                        if (!empty($data)) Db::name('film')->where('id', $film['id'])->update($data);

                    }
                }

                $redis->set('cache:virtualOperational', $p + 1);
            } else {
                $redis->del('cache:virtualOperational');
            }
        }
    }

    /**
     *
     * 1、虚拟运营数据的增加的前提是基于已经审核通过的视频；
     * 2、脚本每小时执行一次；
     * 3、当视频浏览量低于20000时，每次随机增加50-200的浏览量；
     * 4、当视频点赞量低于10000时，每次随机增加50-200的点赞量；
     * 5、当视频收藏量低于500时，每次随机增加5-30的收藏量；
     * 6、当视频分享量低于100时，每次随机增加1-10的分享量；
     *
     * 当天审核过的视频随机加
     * 浏览量：10~80，点赞：1~50，收藏：1~12，分享：1~5
     * 昨天审核过的视频随机加
     * 浏览量：60~150，点赞：40~120，收藏：8~22，分享：+2
     * 前天审核过的视频随机加
     * 浏览量：150~200，点赞：120~200，收藏：22~30，分享：+1
     * 三天前审核过的视频随机加
     * 浏览量：200~300，点赞：180~230，收藏：25~50，分享：+3
     */
    protected function getOperationalScore($date_type, array $films)
    {
        $data = [];

        $scores = [
            'today' => [
                'play_sum' => ['min' => 10, 'max' => 80],
                'zan_sum' => ['min' => 1, 'max' => 50],
                'collection_sum' => ['min' => 1, 'max' => 12],
                'share_sum' => ['min' => 1, 'max' => 5]
            ],
            'yesterday' => [
                'play_sum' => ['min' => 60, 'max' => 150],
                'zan_sum' => ['min' => 40, 'max' => 120],
                'collection_sum' => ['min' => 8, 'max' => 22],
                'share_sum' => ['min' => 3, 'max' => 7]
            ],
            'threeday' => [
                'play_sum' => ['min' => 150, 'max' => 200],
                'zan_sum' => ['min' => 120, 'max' => 200],
                'collection_sum' => ['min' => 22, 'max' => 30],
                'share_sum' => ['min' => 4, 'max' => 8]
            ],
            'default' => [
                'play_sum' => ['min' => 200, 'max' => 300],
                'zan_sum' => ['min' => 180, 'max' => 230],
                'collection_sum' => ['min' => 25, 'max' => 50],
                'share_sum' => ['min' => 7, 'max' => 12]
            ]
        ];

        $date_type_score = $scores[$date_type];

        $films['play_sum'] < 20000 && $data['play_sum'] = $films['play_sum'] + mt_rand($date_type_score['play_sum']['min'], $date_type_score['play_sum']['max']);

        $films['zan_sum'] < 10000 && $data['zan_sum'] = $films['zan_sum'] + mt_rand($date_type_score['zan_sum']['min'], $date_type_score['zan_sum']['max']);

        $films['collection_sum'] < 500 && $data['collection_sum'] = $films['collection_sum'] + mt_rand($date_type_score['collection_sum']['min'], $date_type_score['collection_sum']['max']);

        $films['share_sum'] < 100 && $data['share_sum'] = $films['share_sum'] + mt_rand($date_type_score['share_sum']['min'], $date_type_score['share_sum']['max']);

        return $data;
    }

    public function get_qiniu_token()
    {
        $type = input('type');
        $filename = input('filename');
        if (empty($type)) $this->error('上传类型不能为空');
        if (empty($filename)) $this->error('文件名不能为空');
        $sdk = new CoreSdk();
        $result = $sdk->post('common/get_qiniu_token', array(
            'type' => $type,
            'filename' => $filename,
            'user_id' => defined('USERID') ? USERID : '',
            'user_key' => 'user_id'
        ));
        if (!$result) return $this->error($sdk->getError()->message);
        return $this->success('获取成功', $result);
    }
    
    public function ws_upload()
    {   
        require_once ROOT_PATH . '/vendor/wcs-php-sdk-2.0.9/autoload.php';
        $localFile = $_FILES['file'];
        $file = request()->file('file');
        $info = $file->validate(['size'=>200*1024*1024,'ext'=>'jpg,gif,png,jpeg,mp4']);
        //保存到站点目录下
        $rootPath='uploads/diary';
        $info = $info->move($rootPath);
        if($info){
            $filename =$saveFileName=$info->getSaveName();
            $savePath=$rootPath.'/'.str_replace('\\','/',$saveFileName);
            // $url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
            // $savePath = $url.'/'.$savePath;
            
            if(!file_exists($savePath)) {
                die("ERROR: {$savePath}文件不存在！");
            }
           
        }else{
            die("ERROR:上传失败！");
        }
        
        $accessKey = Config::WCS_ACCESS_KEY;
        $secretKey = Config::WCS_SECRET_KEY;
        $bucket = $bucketName    = Config::BUCKET;
        
        $fileKey = input('key');
        if(empty($fileKey)){
            $ext                 = substr($filename, strripos($filename, '.') + 1);
            $fileKey = 'test/'.time().'.'.$ext;
        }
        $pp = new PutPolicy();
        if ($fileKey == null || $fileKey === '') {
            $pp->scope = $bucketName;
        } else {
            $pp->scope = $bucketName . ':' . $fileKey;
        }
        $pp->returnBody = 'url=$(url)&fsize=$(fsize)&bucket=$(bucket)&key=$(key)';
        $upToken = $token  = $pp->get_token();
        $client = new Uploader($token);
        $resp = $client->upload_return($savePath);
        // var_dump($resp);
        $str = base64_decode($resp->respBody,true);
        parse_str($str, $params);
        $url = $params['url'];

        return json_success($params,'上传成功');
    }
    public function ws_fops()
    {   
        require_once ROOT_PATH . '/vendor/wcs-php-sdk-2.0.9/autoload.php';
        $ak = Config::WCS_ACCESS_KEY;
        $sk = Config::WCS_SECRET_KEY;
        $auth = new MgrAuth($ak, $sk);
        // $bucketName = 'zhangsan';
        $bucketName = Config::BUCKET;
        //国外
        $url = 'test/1693983633.mp4';
        //国内
        // $url = 'test/1693985634.mp4';
        $client = new FileManager($auth);
        $res =  $client->fops($bucketName, $url);
        var_dump($res);
    }
    public function ws_fmgr()
    {   
        $aa = REDIS_AUTH;
        var_dump($aa);die;
        require_once ROOT_PATH . '/vendor/wcs-php-sdk-2.0.9/autoload.php';
        $ak = Config::WCS_ACCESS_KEY;
        $sk = Config::WCS_SECRET_KEY;
        $auth = new MgrAuth($ak, $sk);
        //{"persistentId":"20194f2de377588f467a9299dc4b4b64661f"}
        //{"persistentId":"20194a3c059fbe4b449182c5484a13950c12"}
        //{"persistentId":"2019e22f15d623a04a7bbd6b76592c0e4281"}
        $persistentId = '201900a786755a5e4e508db2260b75c0c78a';
        $client = new FileManager($auth);
        $res =  $client->fmgr($url);
        var_dump($res);
    }
    
    public function test()
    {   
        // $rabbitChannel = new RabbitMqChannel(['video.update', 'video.create_publish', 'user.credit']);
        // $aa = $rabbitChannel->exchange('main')->send('user.credit.lower_shelf_video', ['user_id' => 100352, 'video_id' => 22, 'aid' => 1]);
        $rabbitChannel = new RabbitMqChannel(['video.create_before']);
        $aa =  $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => 35]);
        var_dump($aa);die;
        $log ='aa';
        $obj = new VideoBefore($log,['process_name'=>'VideoPublish']);
        $ss = $obj->process1();
        var_dump($aa);
    }
}
