<?php

namespace bxkj_common;

class SectionMarkExecuter extends SectionExecuter
{
    protected $lockName;

    public function getOptions()
    {
        return [];
    }

    //首次执行
    public function first()
    {
        return [];
    }

    public function handler($length = 10)
    {
    }

    public function success($complete = '', $total = 0)
    {
        return ['complete' => $complete, 'processed' => $total];
    }

    protected function wait()
    {
        $lockName = "section:{$this->lockName}";
        redis_lock($lockName, 300);
    }

    protected function lock()
    {
    }

    protected function unlock()
    {
        $lockName = "section:{$this->lockName}";
        redis_unlock($lockName);
    }

    protected function pointer($value = null)
    {
        $redis = RedisClient::getInstance();
        if (isset($value)) {
            $redis->set("traversing:pointer:{$this->lockName}", $value);
        } else {
            $pointer = $redis->get("traversing:pointer:{$this->lockName}");
            return (int)$pointer;
        }
    }

}