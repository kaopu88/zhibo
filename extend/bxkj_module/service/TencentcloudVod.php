<?php

namespace bxkj_module\service;

use bxkj_common\RabbitMqChannel;
use think\Db;

class TencentcloudVod extends Service
{
    protected $dbConfig;
    protected $mqConfig;

    public function __construct()
    {
        parent::__construct();
        //同一数据源 正式数据库 这里不区分正式、测试
        $this->dbConfig = config('database.');
        //正式库mq
        $this->mqConfig = config('mq.');
    }

    //订阅任务结果
    public function subscribeTask($eventType, $vodTaskId, $callback, $status = null)
    {
        if (empty($callback)) return $this->setError('callback不能为空');
        $where = ['event_type' => $eventType, 'task_id' => $vodTaskId];
        if (isset($status)) $where['status'] = $status;
        return $this->subscribe($where, $callback);
    }

    //订阅上传文件结果
    public function subscribeNewFile($fileId, $callback)
    {
        if (empty($callback)) return $this->setError('callback不能为空');
        $where = ['event_type' => 'NewFileUpload', 'video_id' => $fileId];
        return $this->subscribe($where, $callback);
    }

    //订阅结果处理
    protected function subscribe($where, $callback)
    {
        $row = Db::connect($this->dbConfig)->name('tencentcloud_vod')->where($where)->find();
        if (empty($row)) {
            $where['notice_status'] = '0';
            $where['callback'] = $callback;
            $where['create_time'] = time();
            $id = Db::connect($this->dbConfig)->name('tencentcloud_vod')->insertGetId($where);
            if (!$id) return $this->setError('创建任务失败');
        } else {
            if ($row['notice_status'] != '0') return true;
            Db::connect($this->dbConfig)->name('tencentcloud_vod')->where(['id' => $row['id']])->update(['callback' => $callback]);
            //已触发
            if ($row['trigger_time'] && empty($row['callback'])) {
                $channel = new RabbitMqChannel(['common.callbacks'], null, $this->mqConfig);
                $channel->exchange('main')->sendOnce('callback.tencentcloud_vod', ['id' => $row['id'], 'type' => 'tencentcloud_vod']);
            }
        }
        return true;
    }

   
    //触发结果处理
    public function trigger($event, $params)
    {
        switch ($event)
        {
            case 'NewFileUpload':
                $res = $this->triggerVodTask($params);
                break;
            case 'ProcedureStateChanged':
                bxkj_console($params);
                $res = $this->triggerVodPublish($params);
                break;
            default:
                return true;
        }

        return $res;
    }


    /**
     * 触发视频任务流
     *
     * @param array $params
     * @return bool
     */
    protected function triggerVodTask(array $params)
    {
        /*$video_info = Db::name('video_unpublished')->where(['video_id' => $params['data']['fileId'], 'process_status' => '0'])->find();

        if (empty($video_info)) return false;

        $rabbitChannel = new RabbitMqChannel(['video.create_before']);

        $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => $video_info['id']]);*/

        return true;
    }



    /**
     * 触发视频发布
     *
     * @param array $params
     * @return bool
     */
    protected function triggerVodPublish(array $params)
    {
        $data = $params['data'];

        $insert = [
            'version' => $params['version'],
            'event_type' => $params['eventType'],
            'video_id' => $data['fileId'],
            'task_id' => $data['taskId'],
            'status' => $data['status'],
            'events' => json_encode($params),
            'trigger_time' => time(),
            'create_time' => time(),
            'notice_status' => '0',
        ];

        $res = Db::name('tencentcloud_vod')->insertGetId($insert);

        if (!$res) return false;

        $video = Db::name('video_unpublished')->field('id,video_id,process_status')->where(['video_id' => $data['fileId']])->find();

        if (!$video || $video['process_status'] != '0') return false;

        $update = [
            'process_status' => '1',
            //回调数据转交预发布表basic字段
            'basic_info' => $insert['events'],
        ];

        $num = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);

        if (!$num) return false;

        $rabbitChannel = new RabbitMqChannel(['video.create_after']);

        $rabbitChannel->exchange('main')->sendOnce('video.create.process', ['id' => $video['id']]);

        return true;
    }

}