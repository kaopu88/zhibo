<?php

namespace app\core\controller;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\TencentcloudVod;
use think\Db;
use think\facade\Env;

class VideoCallback extends Controller
{
    //腾讯回调
    public function general()
    {
        $params = file_get_contents("php://input");
        
        $aa = 'eyJkZXRlY3RJZCI6IjIwMTkyMTQxNjcyMjgxMDQyMDIzMDkwNjE0MDkwNUl5Y2dXZlJqMSIsImNtZCI6ImltYWdlUG9ybixpbWFnZVRlcnJvcixpbWFnZVBvbGl0aWNhbCIsImJ1Y2tldCI6InpoYW5nc2FuIiwiaXNaaXAiOjAsInBvcm5EZXRlY3QiOnsiY29kZSI6MjAwLCJtZXNzYWdlIjoiU3VjY2VzcyIsImZpbGVMaXN0IjpbeyJrZXkiOiJ0ZXN0LzE2OTM3OTY4NzVfMDAwMDIuanBnIiwidXJsIjoiaHR0cDovL3docy51a2trODguY29tL3Rlc3QvMTY5Mzc5Njg3NV8wMDAwMi5qcGciLCJyYXRlIjowLjk2NDY4NzQ4NzU4MjM2NzMsImxhYmVsIjoyLCJyZXZpZXciOmZhbHNlLCJlcnJvciI6IiJ9XX0sInRlcnJvckRldGVjdCI6eyJjb2RlIjoyMDAsIm1lc3NhZ2UiOiJTdWNjZXNzIiwiZmlsZUxpc3QiOlt7ImtleSI6InRlc3QvMTY5Mzc5Njg3NV8wMDAwMi5qcGciLCJ1cmwiOiJodHRwOi8vd2hzLnVra2s4OC5jb20vdGVzdC8xNjkzNzk2ODc1XzAwMDAyLmpwZyIsInJhdGUiOjAuOTM3ODkxMTA5MDUzOTM3NiwibGFiZWwiOjAsInJldmlldyI6ZmFsc2UsImVycm9yIjoiIn1dfSwicG9saXRpY2FsRGV0ZWN0Ijp7ImNvZGUiOjIwMCwibWVzc2FnZSI6IlN1Y2Nlc3MiLCJmaWxlTGlzdCI6W3sia2V5IjoidGVzdC8xNjkzNzk2ODc1XzAwMDAyLmpwZyIsInVybCI6Imh0dHA6Ly93aHMudWtrazg4LmNvbS90ZXN0LzE2OTM3OTY4NzVfMDAwMDIuanBnIiwibGFiZWwiOjAsInBlcnNvbnMiOlt7InJhdGUiOjAuMCwibmFtZSI6IumdnuaUv-ayu-S6uueJqSIsInJldmlldyI6ZmFsc2UsImZhY2VVcmwiOiIifV0sImVycm9yIjoiIn1dfX0=';
        
        $myfile = fopen("VideoCallback.txt", "a");
        $date = date('Y-m-d H:i:s');
        fwrite($myfile, "\r\n");
        fwrite($myfile, $date);
        fwrite($myfile, "\r\n");
        fwrite($myfile, var_export($params,true));
        fclose($myfile);
        
        
        $params  =strtr($params, '-_', '+/');
        $params = base64_decode($params);
        $params = json_decode($params,true);
        if (empty($params)) return $this->error();
        header('Content-Type:text/plain;charset=utf-8');
        header('status:200');
        echo 'SUCCESS';
        exit();
        
        
        //下边为腾讯回调用
        $params = $this->parseTencentTaskData($params);
        $tcv = new TencentcloudVod();
        $res = $tcv->trigger($params['eventType'], $params);

        if ($res !== true) return $this->error();
        header('Content-Type:text/plain;charset=utf-8');
        header('status:200');
        echo 'SUCCESS';
        exit();
    }


    //内容处理回调
    public function create_before_complete()
    {
        $json = file_get_contents("php://input");
        $task = json_decode($json, true);

        if (empty($task) || empty($task['data']) || empty($task['data']['fileId'])) return $this->error();
        $data = $task['data'];
        $video = Db::name('video_unpublished')->field('id,video_id,process_status')->where(['video_id' => $data['fileId']])->find();
        if ($video && $video['process_status'] == '0') {
            $update = [
                'process_status' => '1',
                //回调数据转交预发布表basic字段
                'basic_info' => $json
            ];
            $num = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);
            if (!$num) return $this->error();
            $rabbitChannel = new RabbitMqChannel(['video.create_after']);
            $rabbitChannel->exchange('main')->sendOnce('video.create.process', ['id' => $video['id']]);
        }
        echo 'OK';
        exit();
    }

    public function test()
    {
        $rabbitChannel = new RabbitMqChannel(['video.create_before']);

        $a = $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => 2604]);
        var_dump($a);
    }


    private function error()
    {
        header('Content-Type:text/plain;charset=utf-8');
        header('status:204');
        echo 'FAILED';
        exit();
    }


    protected function parseTencentTaskData($params)
    {
        $data['version'] = '3.0';
        $data['eventType'] = $params['EventType'];


        switch ($params['EventType']) {
            case 'NewFileUpload':
                $data['data'] = is_array($params['FileUploadEvent']) ? $params['FileUploadEvent'] : [];
                break;

            case 'ProcedureStateChanged':
                $data['data'] = is_array($params['ProcedureStateChangeEvent']) ? $params['ProcedureStateChangeEvent'] : [];
                break;

            case 'FileDeleted':
                $data = [];
                break;

            case 'TranscodeComplete':
                $data = [];
                break;

            case 'CreateSnapshotByTimeOffsetComplete':
                $data = [];
                break;
        }

        if (isset($data['data'])) {
            $data['data'] = bxkj_lcfirst($data['data']);
        }

        return $data;
    }


    protected function changeFileCode($params)
    {

        $json = json_encode($params);
        $update = [
            'video_url' => $params['data']['mediaProcessResultSet'][0]['TranscodeTask']['Output']['Url'],
            'basic_info' => $json
        ];
        $num = Db::name('video_unpublished')->where(['video_id' => $params['data']['fileId']])->update($update);
        $update = [
            'video_url' => $params['data']['mediaProcessResultSet'][0]['TranscodeTask']['Output']['Url']
        ];
        $update = Db::name('video')->where(['video_id' => $params['data']['fileId']])->update($update);

        if (!$num) return $this->error();
        return true;
    }
}