<?php

namespace bxkj_module\push\producer;

use bxkj_push\AomyPush;
use bxkj_common\Console;
use bxkj_common\RedisClient;
use bxkj_module\push\PushProducer;
use bxkj_module\service\UserSetting;
use think\Db;

class SystemProducer extends PushProducer
{
    //创建任务数据
    public function createTask($msg)
    {
        $key = self::getTaskKey($msg['msg_task_id']);
        $data['msg_write_type'] = $msg['msg_write_type'];
        $data['msg_producer'] = $msg['msg_producer'];
        $data['msg_task_id'] = $msg['msg_task_id'];
        $data['msg_merge_id'] = $msg['msg_merge_id'];
        $data['msg_id'] = $msg['id'] ? $msg['id'] : '';
        $data['cat_type'] = $msg['cat_type'];
        $data['type'] = $msg['type'];
        if ($msg['type'] == 'push') {
            $data['msg_offset'] = isset($msg['msg_offset']) ? $msg['msg_offset'] : 0;
            $data['msg_length'] = $msg['msg_length'] ? $msg['msg_length'] : 200;
            $data['group_id'] = $msg['group_id'];
            $data['url'] = $msg['url'];
        } else {
            $data['receiver_uid'] = $msg['user_id'];
        }
        $data['send_uid'] = $msg['send_uid'];
        $data['title'] = $msg['title'];
        $data['text'] = $msg['summary'] ? $msg['summary'] : '';
        $data['img'] = $msg['send_avatar'] ? $msg['send_avatar'] : '';
        $this->redis->hMset($key, $data);
        $this->redis->expire($key, 86400 * 7);
        return true;
    }

    public function getUsers($task)
    {
        $offset = (int)$task['msg_offset'];
        $length = (int)$task['msg_length'];
        $length = $length <= 0 ? 100 : ($length > 1000 ? 1000 : $length);
        $users = Db::name('user')->where(['delete_time' => null])->limit($offset, $length)->field('user_id,nickname,avatar')->select();
        $users = $users ? $users : [];
        $sendUsers = [];
        sleep(1);
        if (!empty($users)) {
            foreach ($users as $user) {
                $sendUsers[] = $user['user_id'];
            }
            if (count($users) >= $length) {
                return ['offset' => $offset + count($users), 'user_ids' => $sendUsers];
            }
        }
        return ['user_ids' => $sendUsers];
    }

    public function getMsgData($task)
    {
        $catType = $task['cat_type'];
        $type = $task['type'];
        $msgId = $task['msg_id'];
        $userId = $task['send_uid'];
        if (empty($task['url'])) {
            $url = getJump('message_list', [
                    'cat_type' => $catType,
                    'type' => $type,
                    'msg_id' => $msgId,
                    'user_id' => $userId
                ]);
        } else {
            $url = $task['url'];
        }
        $data = array(
            'title' => $task['title'],
            'text' => $task['text'],
            'img' => $task['img'],
            'custom' => array(
                'header' => 'url',
                'url' => $url
            )
        );
        return $data;
    }

}