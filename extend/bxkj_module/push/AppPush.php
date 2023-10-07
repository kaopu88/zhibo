<?php

namespace bxkj_module\push;

use bxkj_common\RedisClient;
use bxkj_push\AomyPush;

class AppPush
{
    protected $delayRange;
    protected $delayRate;
    protected $maxDelay;
    protected $sectionLength;
    protected $receiptPeriod;
    protected $redis;
    const TIMER_KEY = 'messages:push:timer';

    public function __construct()
    {
        $push = config('message.bxkj_push');

        $this->delayRange = $push['push_delay_range'];//延迟最大范围，900
        $this->delayRate = $push['push_delay_rate'];//每秒延迟率 1
        $this->maxDelay = $push['push_max_delay'];//最长延迟时间 3600
        $this->sectionLength = $push['push_section_length'];//200
        $receiptPeriod = $push['push_receipt_period'];//3600
        $this->receiptPeriod = $receiptPeriod <= 0 ? 7200 : $receiptPeriod;
        $this->redis = RedisClient::getInstance();
    }

    //写入一次性发送任务
    public function write($producerName, $msg, $isMerge = false)
    {
        $now = time();
        $producer = $this->getProducer($producerName);
        if ($isMerge) {
            $mergeId = $producer->getMergeId($msg);
            $mergeTaskKey = "messages:merge:{$mergeId}";
            $mergeTask = $this->redis->hGetAll($mergeTaskKey);
            $oldMergeTask = $mergeTask ? $mergeTask : [];
            if (empty($mergeTask)) {
                $mergeTask = [
                    'last_send_time' => 0,
                    'task_id' => ''
                ];
            }
            $lastSendTime = (int)$mergeTask['last_send_time'];
            $interval = $now - $lastSendTime;
            $delay = max($this->delayRange - ($interval * $this->delayRate), 0);
            $sendTime = $now + $delay;
            $maxDelay = $this->maxDelay;
            if (($sendTime - $lastSendTime) > $maxDelay) {
                $sendTime = $lastSendTime + $maxDelay;
            }
            $sendTime = $now;
            if ($sendTime < $now) $sendTime = $now;
            $taskId = $mergeTask['task_id'];
            if (empty($taskId)) {
                $taskId = sha1(uniqid() . get_ucode());
                $msg['msg_write_type'] = 'once';
                $msg['msg_producer'] = $producerName;
                $msg['msg_task_id'] = $taskId;
                $msg['msg_merge_id'] = $mergeId;
                $res = $producer->createTask($msg);
                $mergeTask['task_id'] = $taskId;
            } else {
                $res = $producer->mergeTask($taskId, $msg);
            }
            if ($oldMergeTask['task_id'] != $mergeTask['task_id'] || $oldMergeTask['last_send_time'] != $mergeTask['last_send_time']) {
                $this->redis->hMset($mergeTaskKey, $mergeTask);
            }
            if (!$res) return false;
            if (!$this->redis->sIsMember('messages:push:dup', $taskId)) {
                $this->redis->zAdd(self::TIMER_KEY, $sendTime, $taskId);
            }
        } else {
            $taskId = sha1(uniqid() . get_ucode());
            $msg['msg_write_type'] = 'once';
            $msg['msg_producer'] = $producerName;
            $msg['msg_task_id'] = $taskId;
            $msg['msg_merge_id'] = '';
            $res = $producer->createTask($msg);
            if (!$res) return false;
            $this->redis->lPush('queue:push:wait', $taskId);
        }
        return $taskId;
    }

    //写入分片发送任务
    public function writeSection($producerName, $msg)
    {
        $now = time();
        $producer = $this->getProducer($producerName);
        $taskId = sha1(uniqid() . get_ucode());
        $msg['msg_write_type'] = 'section';
        $msg['msg_producer'] = $producerName;
        $msg['msg_task_id'] = $taskId;
        $msg['msg_merge_id'] = '';
        $msg['msg_offset'] = 0;
        $msg['msg_length'] = $this->sectionLength;
        $res = $producer->createTask($msg);
        if (!$res) return false;
        $this->redis->lPush('queue:push:wait', $taskId);
        return $taskId;
    }

    public function send($taskId)
    {
        //3是取消
        $taskKey = "task:push:{$taskId}";
        $task = $this->redis->hGetAll($taskKey);
        if (empty($task)) {
            $this->sendComplete($taskId);
            return 2;
        }
        if (!empty($task['msg_cancel'])) {
            $this->sendComplete($task);
            return 3;
        }
        $msgWriteType = parse_name($task['msg_write_type'], 1, true);
        $funName = 'send' . $msgWriteType;
        if (!method_exists($this, $funName)) {
            $this->sendComplete($task);
            return 2;
        }
        $result = call_user_func_array([$this, $funName], [$task]);
        return $result;
    }

    protected function sendOnce($task)
    {
        $producer = $this->getProducer($task['msg_producer']);
        $msgData = $producer->getMsgData($task);
        if (!$msgData) {
            $this->sendComplete($task);
            return 3;
        }
        $AomyPush = new AomyPush();
        $pushRes = $AomyPush->setUser(array('user_id' => $task['receiver_uid']))->allTo($msgData);
        if ($pushRes) {
            $this->redis->set("messages:receipt:{$task['msg_task_id']}", json_encode(['result' => $pushRes, 'type' => 'once']), $this->receiptPeriod);
        }
        $this->sendComplete($task, time());
        return 0;
    }

    protected function sendSection($task)
    {
        $producer = $this->getProducer($task['msg_producer']);
        $msgData = $producer->getMsgData($task);
        if (!$msgData) {
            $this->sendComplete($task);
            return 3;
        }
        $userRes = $producer->getUsers($task);
        if (!empty($userRes['user_ids'])) {
            $AomyPush = new AomyPush();
            $pushRes = $AomyPush->setUsers($userRes['user_ids'])->allTo($msgData);
            if ($pushRes) {
                $k1 = "messages:receipt:{$task['msg_task_id']}";
                $kJson = $this->redis->get($k1);
                $arr = $kJson ? json_decode($kJson, true) : false;
                $arr = is_array($arr) ? $arr : ['type' => 'section', 'result' => []];
                $arr['result'][] = $pushRes;
                $this->redis->set($k1, json_encode($arr), $this->receiptPeriod);
            }
        }
        $taskId = $task['msg_task_id'];
        $taskKey = "task:push:{$taskId}";

        if (isset($userRes['offset'])) {
            $hset = $this->redis->hMset($taskKey, array('msg_offset' => $userRes['offset']));
            if ($hset) {
                $goRes = $this->goOn($taskId);
                if ($goRes) return 9;
            }
        }
        $this->sendComplete($task);
        return 0;
    }

    //发送完成
    public function sendComplete($taskId, $updateTime = null)
    {
        if (!is_array($taskId)) {
            $taskKey = "task:push:{$taskId}";
            $task = $this->redis->hGetAll($taskKey);
        } else {
            $task = $taskId;
            $taskId = $task['msg_task_id'];
            $taskKey = "task:push:{$taskId}";
        }
        if ($task) {
            $mergeId = $task['msg_merge_id'];
            if (!empty($mergeId)) {
                $mergeTaskKey = "messages:merge:{$mergeId}";
                $updateData = ['task_id' => ''];
                if (isset($updateTime)) $updateData['last_send_time'] = $updateTime;
                $this->redis->hMset($mergeTaskKey, $updateData);
            }
        }
        $this->redis->lRem('queue:push:working', $taskId, 0);
        $this->redis->lRem('queue:push:wait', $taskId, 0);
        $this->redis->sRem('messages:push:dup', $taskId);
        $this->redis->del($taskKey);
    }

    //继续分片任务
    public function goOn($taskId)
    {
        if ($this->redis->lRem('queue:push:working', $taskId, 0)) {
            $res = $this->redis->lPush('queue:push:wait', $taskId);
            return $res;
        }
        return false;
    }

    public function cancel($taskId, $msgId = '')
    {
        $taskKey = "task:push:{$taskId}";
        $task = $this->redis->hGetAll($taskKey);
        if (!empty($task)) {
            if (empty($task['msg_merge_id'])) {
                $this->redis->hMset($taskKey, ['msg_cancel' => 1]);
            } else {
                $producer = $this->getProducer($task['msg_producer']);
                $producer->removeMsg($task, $msgId);
            }
        }
        return true;
    }

    protected function getProducer($producerName)
    {
        $className = "\bxkj_module\push\producer\\" . parse_name($producerName, 1) . "Producer";
        $producer = new $className($this->redis);
        return $producer;
    }
}