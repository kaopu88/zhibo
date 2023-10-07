<?php

namespace bxkj_module\push\producer;

use bxkj_push\AomyPush;
use bxkj_common\Console;
use bxkj_common\RedisClient;
use bxkj_module\push\PushProducer;
use bxkj_module\service\UserSetting;
use think\Db;

class LiveProducer extends PushProducer
{
    //创建任务数据
    public function createTask($msg)
    {
        $key = self::getTaskKey($msg['msg_task_id']);
        $data['msg_write_type'] = $msg['msg_write_type'];
        $data['msg_producer'] = $msg['msg_producer'];
        $data['msg_task_id'] = $msg['msg_task_id'];
        $data['msg_merge_id'] = $msg['msg_merge_id'];
        $data['msg_offset'] = $msg['msg_offset'];
        $data['msg_length'] = $msg['msg_length'];
        $data['user_id'] = $msg['user_id'];
        $data['room_id'] = $msg['room_id'];
        $data['title'] = $msg['title'];
        $data['text'] = $msg['text'] ? $msg['text'] : '';
        $data['img'] = $msg['avatar'] ? $msg['avatar'] : '';
        $this->redis->hMset($key, $data);
        $this->redis->expire($key, 86400 * 7);
        return true;
    }

    public function getUsers($task)
    {
        $offset = (int)$task['msg_offset'];
        $length = (int)$task['msg_length'];
        $length = $length <= 0 ? 100 : ($length > 1000 ? 1000 : $length);
        $members = $this->redis->zRange("fans:{$task['user_id']}", $offset, $offset + $length - 1);
        bxkj_console([$offset, $length, $members]);
        $roomKey = "roomAudience_{$task['room_id']}";
        $sendMembers = [];
        if (!empty($members)) {
            $userSetting = new UserSetting();
            foreach ($members as $member) {
                $switch = $userSetting->setting($member, 'follow_live_push');
                if ($switch == '1') {
                    if (!$this->redis->sIsMember($roomKey, $member)) {
                        $sendMembers[] = $member;
                    }
                }
            }
            if (count($members) >= $length) {
                return ['offset' => $offset + count($members), 'user_ids' => $sendMembers];
            }
        }
        return ['user_ids' => $sendMembers];
    }

    public function getMsgData($task)
    {
        $liveNum = Db::name('live')->where('id', $task['room_id'])->limit(1)->count();
        if (!$liveNum) return false;
        $queryData = ['room_id' => $task['room_id']];
        $data = array(
            'title' => $task['title'],
            'text' => $task['text'],
            'img' => $task['img'],
            'custom' => array(
                'header' => 'url',
                'url' => getJump('live_detail', $queryData)
            )
        );
        return $data;
    }

}