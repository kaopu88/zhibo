<?php

namespace app\mq\callbacks;
use bxkj_module\service\DsIM;
use bxkj_module\service\UserRedis;
use bxkj_module\service\Work;
use PhpAmqpLib\Message\AMQPMessage;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Ocr\V20181119\OcrClient;
use TencentCloud\Ocr\V20181119\Models\GeneralFastOCRRequest;
use bxkj_common\RedisClient;
use think\Db;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use bxkj_common\RabbitMqChannel;

class UserDataDeal extends ConsumerCallback
{
    public function audit(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        //查询 user_data_deal id
        if(!empty($data) && !empty($data['id'])){
            $res=$this->process($data['id']);
            if(!$res) return $this->failed($msg,true);
        }
        $this->ack($msg);
    }

    protected function process($id)
    {
        $item = Db::name('user_data_deal')->where(['id' => $id])->find();
        if ($item['audit_status'] != '-1')
        {
            return true;
        }
        $img = json_decode($item['data'],true);
        $user_id = $item['user_id'];
        //是否人工审核
        $person = false;
        //图片检测
        foreach ($img as $k => $v)
        {
            if (in_array($k,['avatar','cover'])){
                $res = $this->imgHandler($v);
                if ($res['status']==0){
                    Db::name('user_data_deal')->where('id', $id)->update(['audit_status' => '2', 'handle_time' => time(), 'handle_desc' => $res['msg']]);
                    //对接rabbitMQ
                    $rabbitChannel = new RabbitMqChannel(['user.credit']);
                    $rabbitChannel->exchange('main')->sendOnce('user.credit.user_data_turndown_sev', ['user_id' => $user_id, 'reason'=>$res['msg']]);
                    return true;
                }else if($res['status']==2){
                    $person = true;
                }
            }
        }
        //查询是否含待审核记录
        $find = Db::name('user_data_deal')->where(array('user_id'=> $user_id,'audit_status'=>'0'))->find();
        if ($person){
            //人审
            $workService = new Work();
            if($find){
                //如果之前存在此用户待人工审核的记录
                $itemdata = json_decode($find['data'],true);
                $newdata = array_merge($itemdata,$img);
                $aid = $workService->allocation('user_data_deal', $user_id, $find['id']);
                $num = Db::name('user_data_deal')->where('id', $find['id'])->update(['data' => json_encode($newdata), 'aid' => $aid]);
                if ($num){
                    Db::name('user_data_deal')->where('id', $id)->delete();
                }
            }else{
                $aid = $workService->allocation('user_data_deal', $user_id, $find['id']);
                Db::name('user_data_deal')->where('id', $id)->update(['audit_status' => '0', 'aid' => $aid]);
            }
        }else{
            if ($find){
                //如果之前存在此用户待人工审核的记录
                $itemdata = json_decode($find['data'],true);
                foreach ($img as $k => $v){
                    unset($itemdata[$k]);
                }
                if ($itemdata){
                    Db::name('user_data_deal')->where('id', $find['id'])->update(['data' => json_encode($itemdata)]);
                }else{
                    Db::name('user_data_deal')->where('id', $find['id'])->delete();
                }
            }
            Db::name('user_data_deal')->where('id', $id)->update(['audit_status' => '1', 'handle_time' => time(), 'handle_desc' => '系统自动审核通过']);
            //更新用户表 更新用户Redis
            Db::name('user')->where('user_id', $user_id)->update($img);
            UserRedis::updateData($user_id, $img);
            if ($img['avatar']){
                $DsIM = new DsIM();
                $DsIM->updateUserData($user_id);
            }
        }
        return true;
    }

    public function imgHandler($img)
    {
        //1 检测图片是否含有文字 如果有 移交客服处理
        $check_words = $this->tencent_image_check($img);
        if (count($check_words['TextDetections']) > 0) {
            //人工审核
            return ['status'=>2,'msg'=>'系统自动审核通过'];
        }else{
            //2 检测图片 图片鉴黄 图片鉴暴恐 图片敏感人物识别
            $check_contents = $this->qiniu_image_check($img);
            $content = json_decode($check_contents,true);
            if ($content['code']==200 && $content['message']=='OK' && $content['result']['suggestion']=='pass') {
                //通过
                return ['status'=>1,'msg'=>'机审通过'];
            }else{
                //驳回
                return ['status'=>0,'msg'=>'识别图片含有文字 图片鉴黄 图片鉴暴恐 图片敏感人物'];
            }
        }
    }

    function url_post($url, $data, $authorization, $getinfo = false)
    {
        $headers = array('Content-Type: application/json', 'Authorization: ' . $authorization);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        //post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //header请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        if ($getinfo) {
            $info = curl_getinfo($ch);
            $output = array('content' => $output, 'info' => $info);
        }
        curl_close($ch);
        return $output;
    }

    public function tencent_image_check($url)
    {
        $vod_config = config('app.vod');
        if ($vod_config['platform'] != 'tencent') return false;
        $qcloud = $vod_config['platform_config'];
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['timeout']?:60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("HmacSHA256");  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new OcrClient($cred, $qcloud['region'], $clientProfile);
        $req = new GeneralFastOCRRequest();
        $req->ImageUrl = $url;
        try {
            $resp = $client->GeneralFastOCR($req);
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            $this->log->notice('GeneralFastOCR Error ' . $errCode . ' ' . $exception->getMessage());
            return false;
        }
        $resp = json_encode($resp);
        return json_decode($resp,true);
    }

    public function qiniu_image_check($url)
    {
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');

        $data = array(
            'data' => array('uri' => $url),
            'params' => array(
                'scenes' => array("pulp", "terror", "politician")
            )
        );

        $dataStr = "POST /v3/image/censor\nHost: ai.qiniuapi.com\nContent-Type: application/json\n\n" . json_encode($data);
        $hmac = hash_hmac('sha1', $dataStr, $secretKey, true);
        $sign = $accessKey . ':' . \Qiniu\base64_urlSafeEncode($hmac);
        $sign = 'Qiniu ' . $sign;

        $res = $this->url_post('http://ai.qiniuapi.com/v3/image/censor', json_encode($data), $sign);

        return $res;
    }
}
