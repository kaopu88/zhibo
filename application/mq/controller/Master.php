<?php

namespace app\mq\controller;

use app\mq\service\RunLog;
use app\mq\service\Child;

class Master extends Cli
{
    protected $config;
    protected $log;
    protected $children = [];
    protected $isInit = false;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('mq.');
        $this->log = new RunLog();
        $this->log->pid(getmypid());
    }

    //Linux系统中启动
    public function start()
    {
        //必须加载pcntl扩展
        if (!function_exists("pcntl_fork")) {
            $this->log->fatal('pcntl extention is must');
            exit();
        }
        $processes = $this->config['processes'];
        if (empty($processes)) {
            $this->log->fatal('processes empty');
            exit();
        }
        $this->log->info('start on ' . $this->config['host'] . ':' . $this->config['port'] . ' by ' . $this->config['user']);
        $process_total = 0;
        foreach ($processes as $process_name => $process) {
            $pid = (int)$this->batchFork($process_name, $process);
            if ($pid == -1) {
                continue;
            } else if ($pid) {
                //继续执行
                $process_total++;
            } else {
                $this->log->info('exit');
                exit();
            }
        }
        $this->log->info('create process group ' . $process_total);
        $this->isInit = true;
        if ($process_total > 0) {
            $this->guard();
        }
        $this->log->info('exit');
        exit();
    }

    //Windows上直接生成子进程（不支持多个进程）
    public function wstart()
    {
        $this->log->pid(1);
        $process_name = input('process');
        if (empty($process_name)) {
            $this->log->fatal('process_name empty');
            exit();
        }
        $process = $this->config['processes'] ? $this->config['processes'][$process_name] : null;
        if (empty($process) || !is_array($process)) {
            $this->log->fatal('process empty');
            exit();
        }
        $this->log->info('wstart on ' . $this->config['host'] . ':' . $this->config['port'] . ' by ' . $this->config['user']);
        $process['threads'] = 1;
        $ppid = input('ppid', 1);
        $pid = getmypid();
        $this->log->pid($pid)->child($process_name, $ppid);
        (new Child($pid, $ppid, $process_name, $process, $this->log))->start();
    }

    //批量启动多个进程
    protected function batchFork($process_name, $process, $total = 0)
    {
        if (!is_array($this->children[$process_name])) {
            $this->children[$process_name] = [];
        }
        if (!isset($process['threads'])) {
            $process['threads'] = config("mq.{$process_name}_threads");
        }
        $threads = max(1, (int)$process['threads']);
        if (!$this->isInit) {
            $this->log->info("process {$process_name} need {$threads} threads");
        }
        while ($total < $threads) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                $this->log->fatal($process_name . ' ' . $total . 'st could not fork');
                return $pid;
            } else if ($pid) {
                //父进程
                $total++;
                $this->children[$process_name][] = $pid;
            } else {
                //子进程
                $pid = posix_getpid();
                $ppid = posix_getppid();//父进程号
                $this->log->child($process_name, $ppid)->pid($pid);
                (new Child($pid, $ppid, $process_name, $process, $this->log))->start();
                return 0;
            }
        }
        return 1;
    }

    //守护进程
    protected function guard()
    {
        $checkNum = 0;
        $checkNum2 = 60;
        while (true) {
            sleep(5);
            $checkNum++;
            if ($checkNum % $checkNum2 == 0) {
                $this->log->notice("pong...");
            }
            foreach ($this->children as $process_name => &$processChildren) {
                $process = $this->config['processes'][$process_name];
                foreach ($processChildren as $index => $processChild) {
                    $pid = $processChild;
                    $res = pcntl_waitpid($pid, $status, WNOHANG);
                    if ($res == -1 || $res > 0) {
                        $statusStr = "PID{$pid}";
                        if (!pcntl_wifexited($status)) {
                            $statusStr .= " exit unexpected";
                        } else {
                            //获取进程终端的退出状态码;
                            $code = pcntl_wexitstatus($status);
                            $statusStr .= " exit #{$code}";
                        }
                        if (pcntl_wifsignaled($status)) {
                            $statusStr .= " signal no";//不是通过接受信号中断
                        } else {
                            $signal = pcntl_wtermsig($status);
                            $statusStr .= " signal #$signal";
                        }
                        if (pcntl_wifstopped($status)) {
                            $statusStr .= " stop normal";
                        } else {
                            $signal = pcntl_wstopsig($status);
                            $statusStr .= " stop #$signal";
                        }
                        $this->log->info($statusStr);
                        unset($this->children[$process_name][$index]);
                    }
                }
                $num = count($this->children[$process_name]);
                if ($checkNum % $checkNum2 == 0) {
                    $this->log->notice("{$process_name} has {$num}");
                }
                $pid = $this->batchFork($process_name, $process, $num);
                if ($pid == -1) {
                    $this->log->info('create child error');
                } else if (empty($pid)) {
                    $this->log->info('exit');
                    exit();
                }
            }
        }
    }


}
