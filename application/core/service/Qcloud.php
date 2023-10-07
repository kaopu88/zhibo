<?php

namespace app\core\service;

use bxkj_common\Console;


class Qcloud
{

    /**
     * 腾讯云点播上传凭证
     * @param $classId int 云端目录id
     * @param string $source 客户端标识，默认移动端为app
     * @param int $procedure 视频后续任务操作，这里为服务端预设标识
     * @return string
     */
    public function getQcloudVodSign($taskId='', $carryMsg='', $classId='')
    {
        $vod_config = config('app.vod');

        if ($vod_config['platform'] != 'tencent') return make_error('未配置腾讯云点播服务');

        $qcloudConfig = $vod_config['platform_config'];

        empty($carryMsg) || $qcloudConfig['sourceContext'] = $carryMsg;

        $current = time();

        $expired = $current + mt_rand(600, 1800);

        $argList = [
            'secretId' => $qcloudConfig['secret_id'],
            'currentTimeStamp' => $current,
            'expireTime' => $expired,
            'random' => mt_rand(1000,5000),
//            'classId' => $classId, //分类目录id
//            'procedure' =>
            'sourceContext' => $qcloudConfig['sourceContext'],
            'oneTimeValid' => $qcloudConfig['one_time_valid'] //是否单次有效
        ];

        !empty($taskId) && $argList['procedure'] = $this->procedure($taskId);

        $orignal = http_build_query($argList);

        $signature = base64_encode(hash_hmac('SHA1', $orignal, $qcloudConfig['secret_key'], true).$orignal);

        return $signature;
    }


    //生成任务处理模版
    protected function procedure($taskNum)
    {
        $vod_config = config('app.vod');

        if ($vod_config['platform'] != 'tencent') return '';

        $qcloudConfig = $vod_config['platform_config'];

        if (empty($qcloudConfig['uploadTemplate'])) return '';

        $procedureConfig = $qcloudConfig['uploadTemplate'];

        switch ($taskNum)
        {
            case 2 ://鉴黄
                $procedureConfig = array_merge($procedureConfig['default'], $procedureConfig['aiReview']);

                $transcode = json_encode($procedureConfig['transcode']);

                $process = "QCVB_ProcessUGCFile({$transcode}, {$procedureConfig['watermark']}, {$procedureConfig['coverBySnapshot']}, {$procedureConfig['aiReview']})";
                break;

            case 1 ://不鉴黄
                $config = $procedureConfig['default'];

                $transcode = json_encode($config['transcode']);

                $process = "QCVB_SimpleProcessFile({$transcode}, {$config['watermark']}, {$config['coverBySnapshot']})";
                break;

            default :
                $process = '';
                break;
        }

        return $process;
    }




}