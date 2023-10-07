<?php

namespace app\mq\callbacks;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\Task;
use bxkj_module\service\UserRedis;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\PoolManager;
use bxkj_recommend\model\Video;
use PhpAmqpLib\Message\AMQPMessage;
use think\Db;

class VideoPublish extends ConsumerCallback
{

    //视频上传后的处理
    public function process(AMQPMessage $msg)
    {   
        // $myfile = fopen("VideoPublish.txt", "a");
        // fwrite($myfile, "\r\n");
        // fwrite($myfile, $msg->body);
        // fclose($myfile);
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $video = Db::name('video_unpublished')->where(['id' => $data['id']])->find();
            if($video['default_audit_status'])$video['audit_status']='2';
            if ($video && $video['audit_status'] == '2' ) {//&& (int)$video['process_status'] >= 2
                $data['new_upload'] = isset($data['new_upload']) ? $data['new_upload'] : '1';
                $res = $this->publish($video, $data['new_upload']);
                if (!$res) return $this->failed($msg, true);
            }
        }
        $this->ack($msg);
    }
    
    //视频上传后的处理
    public function process1()
    {
        $data = json_decode($msg->body, true);
        $data = ['user_id' => 100352, 'id' => 25, 'aid' => 1];
        if (!empty($data)) {
            $video = Db::name('video_unpublished')->where(['id' => $data['id']])->find();
            if($video['default_audit_status'])$video['audit_status']= '2';
            if ($video && $video['audit_status'] == '2' ) {//&& (int)$video['process_status'] >= 2
                $data['new_upload'] = isset($data['new_upload']) ? $data['new_upload'] : '1';
                $res = $this->publish($video, $data['new_upload']);
                var_dump($res);
                if (!$res) return $this->failed($msg, true);
            }
        }
         echo(12321);die;
        $this->ack($msg);
       
    }
    protected function publish($video, $isNewUpload)
    {
        if ($video['visible'] == '2') return true;//私密的不发布
        $tableStructure = $this->getTableStructure('video');
        $data = [];
        if ($tableStructure && $tableStructure['fields']) {
            foreach ($tableStructure['fields'] as $field) {
                if (isset($video[$field])) $data[$field] = $video[$field];
            }
        } else {
            $data = $video;
        }
        //发布表不需要的字段
        unset($data['process_status'], $data['audit_time'],
            $data['app_time'], $data['audit_status'], $data['basic_info'],
            $data['reason'], $data['status'], $data['poi_id'],
            $data['delete_time'], $data['ai_review'], $data['default_audit_status']);
        $has = Db::name('video')->where(['id' => $data['id']])->count();
        if ($has) return true;
        if (empty($data['video_url']) ||
            empty($data['user_id']) ||
            // empty($data['width']) ||
            // empty($data['duration']) ||
            // empty($data['height']) ||
            empty($data['cover_url'])) {
            return false;
        }
        $res = Db::name('video')->insert($data);
        if (!$res) return false;
        $filmNum = Db::name('video')->where(['user_id' => $data['user_id']])->count();
        $userUpdate = ['film_num' => $filmNum];
        Db::name('user')->where(['user_id' => $data['user_id']])->update($userUpdate);
        UserRedis::updateData($data['user_id'], $userUpdate);
        Db::name('video_unpublished')->where(['id' => $data['id']])->update(['status' => '1']);
        try {
            $pool = new PoolManager();
            $res2 = $pool->push(new Video($data['id']));
            $pool->newpush($data['id']); //新排序方式
            if (!$res2) return false;
        } catch (Exception $exception) {
            $this->log->info('PoolManager push:' . $exception->getMessage());
            return false;
        }
        if ($isNewUpload == '1') {
            //@好友
            if (!empty($data['friends'])) {
                $friends = json_decode($data['friends'], true);
                $friend_uids = array_column($friends ? $friends : [], 'user_id');
                $atData = [
                    'scene' => 'publish_film',
                    'friend_uids' => implode(',', $friend_uids),
                    'user_id' => $data['user_id'],
                    'film_id' => $data['id'],
                    'film_title' => $data['describe'],
                    'cover_url' => $data['cover_url'] ? $data['cover_url'] : ''
                ];
                $rabbitChannel = new RabbitMqChannel(['user.behavior']);
                $rabbitChannel->exchange('main')->sendOnce('user.behavior.at_friend', ['behavior' => 'at_friend', 'data' => $atData]);
            }
        }

        $taskMod = new Task();
        $data = [
            'user_id' => $data['user_id'],
            'task_type' => 'postVideo',
            'task_value' => 1
        ];
        $taskMod->subTask($data);
        $taskMod->uploadVideo(['uid' => $data['user_id']]);

        return true;
    }

    protected function getTableStructure($tableName)
    {
        $key = "table:{$tableName}";
        $arr = cache($key);
        if (empty($arr)) {
            $prefix = config('database.prefix');
            $result = Db::query("DESC `{$prefix}{$tableName}`");
            if (!$result) return false;
            $pks = array();
            $fields = array();
            foreach ($result as $item) {
                if ($item['Key'] == 'PRI') {
                    $pks[] = $item['Field'];
                }
                $fields[] = $item['Field'];
            }
            $arr = array(
                'name' => $tableName,
                'pks' => $pks,
                'fields' => $fields
            );
            cache($key, $arr);
        }
        return $arr;
    }

}
