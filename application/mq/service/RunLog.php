<?php

namespace app\mq\service;


use think\facade\Env;

class RunLog
{
    protected $debug;
    protected $config;
    protected $process_name;
    protected $ppid = 1;
    protected $pid = 0;

    public function __construct()
    {
        $this->debug = true;
    }

    public function fatal($data)
    {
        $this->printStr('fatal', $data);
    }

    public function info($data)
    {
        $this->printStr('info', $data);
    }

    public function notice($data)
    {
        $this->printStr('notice', $data);
    }

    protected function printStr($type, $data)
    {
        if (is_error($data)) {
            $message = (string)$data;
        } else {
            $message = is_array($data) ? json_encode($data) : $data;
        }
        $timeStr = date('m-d H:i:s');
        $str = "[";
        $str .= $this->strPad("{$type}", 6) . " - {$timeStr}] ";
        if (!empty($this->process_name)) {
            $str .= ($this->strPad("{$this->ppid}") . "> " . $this->strPad("{$this->process_name}_{$this->pid}", 12));
        } else {
            $str .= $this->strPad("{$this->pid}") . "> " . $this->strPad('0', 12);
        }
        $location = '';
        $statcks = debug_backtrace();
        if ($statcks[2]) {
            $file = $statcks[2]['file'];
            $file = str_replace(Env::get('root_path'), DIRECTORY_SEPARATOR, $file);
            $location = $file . ($file ? ' ' : '') . 'line ' . $statcks[1]['line'];
        }
        $debug = config('app.app_debug');
        if (($type == 'notice' && $debug) || $type != 'notice') {
            echo $str . "{$location} : {$message}" . PHP_EOL;
        }
    }

    protected function strPad($name, $length = 9)
    {
        return str_pad($name, $length, ' ', STR_PAD_RIGHT);
    }

    public function pid($pid)
    {
        $this->pid = $pid;
        return $this;
    }

    public function child($process_name, $ppid)
    {
        $this->process_name = $process_name;
        $this->ppid = $ppid;
        return $this;
    }
}