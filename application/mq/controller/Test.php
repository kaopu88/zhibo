<?php

namespace app\mq\controller;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\GiftLog;
use bxkj_module\service\Message;
use bxkj_module\service\TencentcloudVod;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use bxkj_recommend\Calc;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\AiAnalysisTaskInput;
use TencentCloud\Vod\V20180717\Models\AiContentReviewTaskInput;
use TencentCloud\Vod\V20180717\Models\MediaProcessTaskInput;
use TencentCloud\Vod\V20180717\Models\ProcessMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;

class Test extends Cli
{
    protected $config;
    protected $connection;

    public function publisher()
    {
        $type = input('type');
        $id = input('id', uniqid());
        $this->config = config('mq.');
        $this->connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost']);
        $channel = $this->connection->channel();
        if ($type == 'like') {

        } else if ($type == 'upload') {
            $myChannel = new RabbitMqChannel(['video.create_before']);
            $data = ['id' => 8, 'lng' => 17.25, 'msg_id' => $id];
            $myChannel->exchange('main')->sendOnce('video.create.upload', $data);
        }
        $channel->close();
        $this->connection->close();
        return 'send success';
    }

    public function process_media()
    {
        $qcloudConfig = [
            'secretId' => 'AKIDHTVQCPAN6dz97w55NXZcWEk5ED1dmHLF',
            'secretKey' => 'qTL7j9NPFsJYbWJG2d2EE2e4IE5lQwGR',
            'endpoint' => 'vod.ap-guangzhou.tencentcloudapi.com',
            'timeout' => 15,
            'signMethod' => 'HmacSHA256',
            'region' => 'ap-guangzhou',
            //转动图任务
            'AnimatedGraphicTaskSet' => [
                [
                    'Definition' => 20193,
                    'StartTimeOffset' => 1,
                    'EndTimeOffset' => 3
                ]
            ],
            //封面设置
            'CoverBySnapshotTaskSet' => [
            ],
            //内容审核
            'AiContentReviewTask' => [
                'Definition' => 10
            ],
            //内容分析
            'AiAnalysisTask' => [
                'Definition' => 10
            ]
        ];
        $cred = new Credential($qcloudConfig['secretId'], $qcloudConfig['secretKey']);
        $httpProfile = new HttpProfile();
        //$httpProfile->setReqMethod("POST");  // post请求(默认为post请求)
        $httpProfile->setReqTimeout($qcloudConfig['timeout']);// 请求超时时间，单位为秒(默认60秒)
        //$httpProfile->setEndpoint("vod.ap-guangzhou.tencentcloudapi.com");  // 指定接入地域域名(默认就近接入)
        // 实例化一个client选项，可选的，没有特殊需求可以跳过
        $clientProfile = new ClientProfile("HmacSHA256", $httpProfile);
        //$clientProfile->setUnsignedPayload(true);
        $client = new VodClient($cred, $qcloudConfig['region'], $clientProfile);
        $req = new ProcessMediaRequest();
        $req->FileId = 5285890787860127419;
        $processTaskInput = new MediaProcessTaskInput();
        $processTaskInput->AnimatedGraphicTaskSet = $qcloudConfig['AnimatedGraphicTaskSet'];
        $req->MediaProcessTask = $processTaskInput;
        $aiContentReviewTaskInput = new AiContentReviewTaskInput();
        $aiContentReviewTaskInput->Definition = $qcloudConfig['AiContentReviewTask']['Definition'];
        $req->AiContentReviewTask = $aiContentReviewTaskInput;
        $aiAnalysisTaskInput = new AiAnalysisTaskInput();
        $aiAnalysisTaskInput->Definition = $qcloudConfig['AiAnalysisTask']['Definition'];
        $req->AiAnalysisTask = $aiAnalysisTaskInput;
        $req->TasksPriority = 0;
        $req->SessionContext = 'bingxin';
        $req->SessionId = time();
        try {
            $resp = $client->ProcessMedia($req);
        } catch (TencentCloudSDKException $exception) {
            echo $exception->getErrorCode();
            echo 'ProcessMedia Error ' . $exception->getErrorCode() . ' ' . $exception->getMessage() . PHP_EOL;
            return '';
        }
        $taskId = $resp->TaskId;
        echo "TaskId:{$taskId}";
        if (empty($taskId)) return '';
        $tcv = new TencentcloudVod();
        $url = CORE_URL."/video_callback/create_before_complete";
        $tTes = $tcv->subscribeTask('ProcedureStateChanged', $taskId, $url, 'FINISH');
        if (!$tTes) return '';
        return 'success';
    }

    public function at_friend()
    {
        //喜欢视频
         $rabbitChannel = new RabbitMqChannel(['user.behavior']);
         $rabbitChannel->exchange('main')->sendOnce('user.behavior.like_video', [
             'behavior' => 'like_video',
             'data' => [
                 'user_id' => 10145,
                 'video_id' => 6
             ]
         ]);


        //取消喜欢视频
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.cancel_like_video', [
            'behavior' => 'cancel_like_video',
            'data' => [
                'user_id' => 10145,
                'video_id' => 6
            ]
        ]);


        //关注
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.follow', [
            'behavior' => 'follow',
            'data' => [
                'user_id' => 10183,
                'to_uid' => 10000297
            ]
        ]);

        //取消关注
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.cancel_follow', [
            'behavior' => 'cancel_follow',
            'data' => [
                'user_id' => 10183,
                'to_uid' => 10000297
            ]
        ]);

        //开播
         $rabbitChannel = new RabbitMqChannel(['user.behavior']);
         $rabbitChannel->exchange('main')->sendOnce('user.behavior.live', [
             'behavior' => 'live',
             'data' => [
                 'user_id' => 10183,
                 'room_id' => 10000297
             ]
         ]);

        //取消开播
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.cancel_live', [
            'behavior' => 'cancel_live',
            'data' => [
                'user_id' => 10183,
                'room_id' => 10000297
            ]
        ]);

        return 'success';

    }

    public function gift()
    {
        $data['id'] = 13913699;
        $data['user_id'] = 10152;
        $data['video_id'] = 40;
        $data['to_uid'] = 10163;
        $data['gift_id'] = 209;

        if (empty($data['id']) || empty($data['user_id']) || empty($data['video_id']) || empty($data['to_uid'])) return false;
        try {
            $msg = new Message();
            $result = $msg->setReceiver($data['to_uid'])->setSender($data['user_id'])->sendGift($data);
        } catch (\Exception $exception) {
            return false;
        }
    }



}
