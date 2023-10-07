<?php

namespace bxkj_common;

class SectionExecuter
{
    protected $manager;
    protected $info;
    protected $error;

    public function __construct()
    {
    }

    //初始化
    public function init(&$manager, $info)
    {
        $this->manager = &$manager;
        $this->info = $info;
        if (false) $this->manager = new SectionManager();
    }

    public function getOptions()
    {
        return [];
    }

    public function complete($data)
    {
    }

    //首次执行
    public function first()
    {
        return [];
    }

    protected function safetyExit()
    {
        $this->manager->unlock();
        exit();
    }

    public function getError()
    {
        if (empty($this->error)) return ['status' => 3, 'message' => 'unknow'];
        return $this->error;
    }

    public function setError($message = '', $status = 1)
    {
        $this->error = [
            'status' => $status,
            'message' => $message
        ];
        return false;
    }

    public static function getItemByList($value, $list, $key = 'id', $field = null)
    {
        if (is_object($list) && method_exists($list, 'toArray')) {
            $list = $list->toArray();
        }
        if (!is_array($list)) return null;
        foreach ($list as $index => $item) {
            if ($item[$key] == $value) {
                return isset($field) ? $item[$field] : $item;
            }
        }
        return null;
    }

}