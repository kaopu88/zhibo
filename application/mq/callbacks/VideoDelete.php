<?php

namespace app\mq\callbacks;

use bxkj_module\service\UserRedis;
use bxkj_recommend\PoolManager;
use PhpAmqpLib\Message\AMQPMessage;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DeleteMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;

class VideoDelete extends ConsumerCallback
{

    //视频上传后的处理
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $where = ['id' => $data['id']];
            $video = Db::name('video_unpublished')->where($where)->find();
            if ($video) {
                $res = $this->delVideo($video);
                if (!$res) return $this->failed($msg, true);
            }
        }
        $this->ack($msg);
    }

    protected function delVideo($video)
    {
        try {
            $pool = new PoolManager();
            $pool->remove($video['id']);
            $pool->removeNew($video['id']);
        } catch (\Exception $exception) {
            $this->log->info('delVideo remove error');
        }
        $num = Db::name('video_unpublished')->where(['id' => $video['id']])->delete();
        $num2 = Db::name('video')->where(['id' => $video['id']])->delete();
        if ($num || $num2) {
            //更新用户的发布数量
            if ($num2) {
                $filmNum = Db::name('video')->where(['user_id' => $video['user_id']])->count();
                $userUpdate = ['film_num' => $filmNum];
                Db::name('user')->where(['user_id' => $video['user_id']])->update($userUpdate);
                UserRedis::updateData($video['user_id'], $userUpdate);
            }
            Db::name('video_tags_relation')->where(['video_id' => $video['id']])->delete();
            Db::name('topic_relation')->where(['video_id' => $video['id']])->delete();
            Db::name('video_comment')->where(['video_id' => $video['id']])->delete();
            if (!empty($video['video_id'])) {
                $this->initiateDeleteMedia($video['video_id']);
            }
        }
        return true;
    }

    protected function initiateDeleteMedia($FileId)
    {$vod_config = config('app.vod');
        if ($vod_config['platform'] != 'tencent') return false;
        $qcloud = $vod_config['platform_config'];
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['timeout']?:60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("HmacSHA256");  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req = new DeleteMediaRequest();
        $req->FileId = $FileId;
        try {
            $resp = $client->DeleteMedia($req);
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            $this->log->info('DeleteMedia Error ' . $errCode . ' ' . $exception->getMessage());
            return false;
        }
        $RequestId = $resp->RequestId;
        return $RequestId;
    }

}
