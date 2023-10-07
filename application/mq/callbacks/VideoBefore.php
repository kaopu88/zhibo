<?php

namespace app\mq\callbacks;

use bxkj_common\RedisClient;
use bxkj_module\service\TencentcloudVod;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\AiAnalysisTaskInput;
use TencentCloud\Vod\V20180717\Models\AiContentReviewTaskInput;
use TencentCloud\Vod\V20180717\Models\CoverBySnapshotTaskInput;
use TencentCloud\Vod\V20180717\Models\MediaProcessTaskInput;
use TencentCloud\Vod\V20180717\Models\ProcessMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;
use bxkj_common\RabbitMqChannel;

/*
 * 对于需要应答的消息，不管消息业务有没有处理成功一定要应答，不能中断，防止出现死信
 * $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
 * 如果业务处理失败了可以
 *return $this->failed($msg,true);
 *
 * $msg->delivery_info;
 * 'routing_key'
 */

class VideoBefore extends ConsumerCallback
{
    /**
     * 视频处理
     *
     * @param AMQPMessage $msg
     * @return bool
     */
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $video = Db::name('video_unpublished')->where(['id' => $data['id']])->find();
            if ($video) {
                if ($video['process_status'] == '0')  {
                    $res = $this->initiateMediaProcess($video);
                    if (!$res) return $this->failed($msg, true);
                }
            }
        }
        $rabbitMq = new RabbitMqChannel(['video.create_after']);
        $rabbitMq->exchange('main')->sendOnce('video.create.process', ['id' => $data['id']]);
        $this->ack($msg);
    }
    
    public function process1()
    {
        $data = ['id'=>35];
        if (!empty($data)) {
            $video = Db::name('video_unpublished')->where(['id' => $data['id']])->find();
            if ($video) {
                if ($video['process_status'] == '0')  {
                    $res = $this->initiateMediaProcess($video);
                    if (!$res) return $this->failed($msg, true);
                }
            }
        }
        $rabbitMq = new RabbitMqChannel(['video.create_after']);
        $rabbitMq->exchange('main')->sendOnce('video.create.process', ['id' => $data['id']]);
        echo(1231);die;
        $this->ack($msg);
    }

    //发起视频处理
    protected function initiateMediaProcess($video)
    {   

        return true;
        $qcloudConfig = config('app.vod');
        if ($qcloudConfig['platform'] != 'tencent') return false;
        $qcloud = $qcloudConfig['platform_config'];
        if (empty($qcloud)) return false;
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout(isset($qcloud['time_out'])?$qcloud['time_out']:60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod($qcloud['sign_method']);  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req = new ProcessMediaRequest();
        $req->FileId = $video['video_id'];
        $processTaskInput = new MediaProcessTaskInput();
        //$this->log->notice('qcloud config ' . json_encode($qcloudConfig));
        //转动图
        if (!empty($qcloudConfig['AnimatedGraphicTaskSet'])) $processTaskInput->AnimatedGraphicTaskSet = $qcloudConfig['AnimatedGraphicTaskSet'];

        //任务流预设
        //$TranscodeTaskSetArr = is_array($qcloudConfig['TranscodeTaskSet']) ? $qcloudConfig['TranscodeTaskSet'] : [];
        $redis = RedisClient::getInstance();
        $processMedia = $redis->get('video_processMedia');
        $TranscodeTaskSetArr = !empty($processMedia) ? [["Definition" => $processMedia]] : [];

        //转音乐
        if ($qcloudConfig['TranscodeTaskSet_Music'] && empty($video['music_id'])) $TranscodeTaskSetArr[] = $qcloudConfig['TranscodeTaskSet_Music'];


        //转码处理
        if (!empty($TranscodeTaskSetArr)) $processTaskInput->TranscodeTaskSet = $TranscodeTaskSetArr;

        //转封面
        if ($qcloudConfig['CoverBySnapshotTaskSet']) $processTaskInput->CoverBySnapshotTaskSet = $qcloudConfig['CoverBySnapshotTaskSet'];

        $req->MediaProcessTask = $processTaskInput;

        //内容审核
        if (!empty($qcloudConfig['AiContentReviewTask']))
        {
            $aiContentReviewTaskInput = new AiContentReviewTaskInput();
            $aiContentReviewTaskInput->Definition = $qcloudConfig['AiContentReviewTask']['Definition'];
            $req->AiContentReviewTask = $aiContentReviewTaskInput;
        }

        //内容分析
        if (!empty($qcloudConfig['AiAnalysisTask']))
        {
            $aiAnalysisTaskInput = new AiAnalysisTaskInput();
            $aiAnalysisTaskInput->Definition = $qcloudConfig['AiAnalysisTask']['Definition'];
            $req->AiAnalysisTask = $aiAnalysisTaskInput;
        }
        $req->TasksPriority = 0;
        $req->SessionContext = 'CK_VOD_APP';
        $req->SessionId = 'CK:' . RUNTIME_ENVIROMENT . ':' . $video['id'];
        try {
            $resp = $client->ProcessMedia($req);
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            $this->log->info('ProcessMedia Error ' . $errCode . ' ' . $exception->getMessage());
            return false;
        }

        if (empty($resp->TaskId))
        {
            $this->log->info('task_id empty ' . $video['video_id']);
            return false;
        }

        return true;
    }

    //发起视频处理
    protected function initiateMediaProcessChangeCode($video,$processMedia)
    {
        $qcloudConfig = config('app.vod');
        if ($qcloudConfig['platform'] != 'tencent') return false;
        $qcloud = $qcloudConfig['platform_config'];
        if (empty($qcloud)) return false;
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['time_out']?:60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod($qcloud['sign_method']);  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req = new ProcessMediaRequest();
        $req->FileId = $video['video_id'];
        $processTaskInput = new MediaProcessTaskInput();
        //转码处理
        if (!empty($processMedia)) $processTaskInput->TranscodeTaskSet = [["Definition" => $processMedia]];

        $req->MediaProcessTask = $processTaskInput;
        $req->TasksPriority = 0;
        $req->SessionContext = 'CK_VOD_APP';
        $req->SessionId = 'CK:' . RUNTIME_ENVIROMENT . ':' . $video['id'].rand(111111,999999);
        try {
            $resp = $client->ProcessMedia($req);
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            $this->log->info('ProcessMedia Error ' . $errCode . ' ' . $exception->getMessage());
            return false;
        }

        if (empty($resp->TaskId))
        {
            $this->log->info('task_id empty ' . $video['video_id']);
            return false;
        }

        return true;
    }
}
