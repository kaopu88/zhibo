<?php

namespace bxkj_module\push\producer;

use bxkj_push\AomyPush;
use bxkj_common\Console;
use bxkj_common\RedisClient;
use bxkj_module\push\PushProducer;
use think\Db;

class UserBehaviorProducer extends PushProducer
{
    public function getMergeId($msg)
    {
        return $msg['user_id'] . ':' . $msg['type'];
    }

    //创建任务数据
    public function createTask($msg)
    {
        $key = self::getTaskKey($msg['msg_task_id']);
        $data['msg_write_type'] = $msg['msg_write_type'];
        $data['msg_producer'] = $msg['msg_producer'];
        $data['msg_task_id'] = $msg['msg_task_id'];
        $data['msg_merge_id'] = $msg['msg_merge_id'];
        $data['cat_type'] = $msg['cat_type'];
        if ($msg['cat_type'] == 'notice' || $msg['cat_type'] == 'push') {
            $data['title'] = $msg['title'];
            $data['summary'] = $msg['summary'];
        }
        $data['type'] = $msg['type'];
        $data['total'] = 1;
        $data['receiver_uid'] = $msg['user_id'];
        $data['list'] = json_encode([
            [
                'msg_id' => $msg['id'],
                'user_id' => $msg['send_uid'],
                'nickname' => $msg['send_nickname'],
                'avatar' => $msg['send_avatar']
            ]
        ]);
        $this->redis->hMset($key, $data);
        $this->redis->expire($key, 86400 * 7);
        return true;
    }

    public function mergeTask($taskId, $msg)
    {
        $taskKey = self::getTaskKey($taskId);
        $task = $this->redis->hGetAll($taskKey);
        if (!empty($task)) {
            $this->redis->hIncrBy($taskKey, 'total', 1);
            $list = json_decode($task['list'], true);
            if (count($list) < 3) {
                $has = false;
                foreach ($list as $item) {
                    if ($item['user_id'] == $msg['send_uid']) {
                        $has = true;
                        break;
                    }
                }
                if (!$has) {
                    $list[] = [
                        'msg_id' => $msg['id'],
                        'user_id' => $msg['send_uid'],
                        'nickname' => $msg['send_nickname'],
                        'avatar' => $msg['send_avatar']
                    ];
                    $this->redis->hSet($taskKey, 'list', json_encode($list));
                }
            }
        }
        return true;
    }

    public function removeMsg($task, $msgId)
    {
        if (empty($task)) return false;
        $taskKey = self::getTaskKey($task['msg_task_id']);
        $list = json_decode($task['list'], true);
        $list = is_array($list) ? $list : [];
        $newList = [];
        foreach ($list as $item) {
            if ($item['msg_id'] != $msgId) {
                $newList[] = $item;
            }
        }
        $this->redis->hSet($taskKey, 'list', json_encode($newList));
        if (empty($newList)) {
            $this->redis->hSet($taskKey, 'msg_cancel', 1);
        }
        $this->redis->hIncrBy($taskKey, 'total', -1);
        return true;
    }

    public function getMsgData($task)
    {
        $list = json_decode($task['list'], true);
        $nicknames = [APP_PREFIX_NAME.'用户'];
        $msgId = 0;
        $userId = 0;
        $avatar = '';
        if (!empty($list)) {
            $nicknames = [];
            $msgId = $list[0]['msg_id'];
            $userId = $list[0]['user_id'];
            $avatar = $list[0]['avatar'];
            foreach ($list as $item) {
                $nicknames[] = short($item['nickname'], 10);
            }
        }
        $nicknameStr = implode('、', $nicknames);
        $total = (int)$task['total'];
        $catType = $task['cat_type'];
        $type = $task['type'];
        $nicknameStr = $nicknameStr . ($total > count($nicknames) ? "等{$total}人" : '');
        $title = APP_PREFIX_NAME.'新消息';
        $summary = '';
        if ($catType == 'like') {
            $suffix = ($type == 'like_film') ? '的作品' : '的评论';
            $title = $nicknameStr . "赞了你{$suffix}";
            $summary = '赶快去看看赞了你的人吧';
        } else if ($catType == 'comment') {
            $suffix = '';
            $act = '评论或回复';
            if ($type == 'comment') {
                $suffix = '的作品';
                $act = '评论';
            } else if ($type == 'reply') {
                $suffix = '的评论';
                $act = '回复';
            }
            $title = $nicknameStr . "{$act}了你{$suffix}";
        } else if ($catType == 'follow') {
            $title = $nicknameStr . '关注了你';
            $summary = '赶快去看看你的粉丝们吧';
        } else if ($catType == 'at') {
            $scene = ($type == 'at_publish_film') ? '发布新作品时' : ($type == 'at_gift_reply' ? '回复时' : '评论中');
            $title = $nicknameStr . "在{$scene}@了你";
            $summary = '快去响应召唤吧~';
        } else if ($catType == 'reward') {
            $title = $nicknameStr . '给你赠送了礼物';
            $summary = '快去查看礼物吧~';
        } else {
            return false;
        }
        $queryData = [
            'cat_type' => $catType,
            'type' => $type,
            'msg_id' => $msgId,
            'user_id' => $userId
        ];
        $data = array(
            'title' => $title,
            'text' => short($summary, 18),
            'img' => $avatar,
            'custom' => array(
                'header' => 'url',
                'url' => getJump('message_list', $queryData)
            )
        );
        bxkj_console(json_encode($data));
        return $data;
    }

}