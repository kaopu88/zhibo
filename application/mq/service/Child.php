<?php

namespace app\mq\service;

use app\mq\service\RunLog;
use bxkj_module\service\MyQuery;
use bxkj_module\service\Service;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use bxkj_recommend\exception\Exception;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use think\Db;

class Child
{
    protected $pid;//当前进程ID
    protected $ppid;//父进程ID
    protected $connection;
    protected $config;
    protected $log;
    protected $process_name;
    protected $process;
    protected $channel;
    protected $consumerId;
    protected $callbackObjs = [];

    public function __construct($pid, $ppid, $process_name, $process, $log)
    {
        $this->pid = $pid;
        $this->ppid = $ppid;
        $this->config = config('mq.');
        $this->process_name = $process_name;
        $this->process = $process;
        $this->log = $log;
        $this->consumerId = "{$this->process_name}:{$this->pid}:" . uniqid();
        //方便IDE提示
        if (false) {
            $this->connection = new AMQPStreamConnection();
            $this->channel = new AMQPChannel($this->connection);
            $this->log = new RunLog();
        }
    }

    public function start()
    {
        $this->log->info("start");
        try {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['user'],
                $this->config['password'],
                $this->config['vhost']);
        } catch (\Exception $e) {
            $this->log->fatal('connection exception: ' . $e->getMessage());
            exit();
        }
        $this->channel = $this->connection->channel();
        $exchanges = is_string($this->process['exchanges']) ? str_to_fields($this->process['exchanges']) : $this->process['exchanges'];
        $exchangeConfigs = config('mq.exchanges');
        $exchangeConfigs = is_array($exchangeConfigs) ? $exchangeConfigs : [];
        foreach ($exchanges as $exchange_name) {
            $exchange = $exchangeConfigs[$exchange_name];
            if (empty($exchange)) {
                $this->log->fatal("exchange {$exchange_name} config empty");
                $this->channel->close();
                $this->connection->close();
                exit();
            }
            $this->startExchange($exchange_name, $exchange);
        }
        $orphanTotal = 0;
        $runtime = 0;
        $autoRestartTime = mt_rand(180, 540) * 10000000;//30-90分钟重启一次
        $block_time = config('mq.block_time');
        $sleep = ($block_time <= 1000) ? $block_time : 50000;//50ms
        $this->log->info("block_time {$block_time}");
        $is_posix_getppid = function_exists('posix_getppid');
        while (count($this->channel->callbacks)) {
            //每一个小时自动重启一遍（单位：微秒）
            if ($runtime >= $autoRestartTime) {
                $runtime = 0;
                $this->log->info("auto restart");
                $this->channel->close();
                $this->connection->close();
                exit();
            }

            try {
                $this->channel->wait(null, true);//不阻塞
            } catch (\Exception $e) {
                $this->log->fatal('wait exception: ' . $e->getMessage() . ' #### code:' . $e->getCode() . ' ' . $e->getFile() . ' line ' . $e->getLine());
                break;
            }

            if ($orphanTotal >= 6000000) {
                $orphanTotal = 0;
                //孤儿进程检测
                if ($is_posix_getppid) {
                    $ppid = posix_getppid();
                    if ($ppid == 1 || $ppid == 0) {
                        $this->log->info("orphan process");
                        $this->channel->close();
                        $this->connection->close();
                        exit();
                    }
                }
                if (!$this->connection->isConnected()) {
                    $this->log->info('isConnected false');
                    exit();
                }
                if (!$this->channel->is_open()) {
                    $this->log->info('is_open false');
                    $this->connection->close();
                    exit();
                }
            }
            usleep($sleep);
            $orphanTotal += $sleep;
            $runtime += $sleep;
        }
        $this->channel->close();
        $this->connection->close();
        $this->log->info("connection close");
    }

    //启动一个交换机
    protected function startExchange($exchange_name, $exchange)
    {
        $exchange_name = "{$this->config['prefix']}.{$exchange_name}";
        $type = $exchange['type'] ? $exchange['type'] : 'topic';
        $durable = isset($exchange['durable']) ? $exchange['durable'] : false;//持久化
        $auto_delete = isset($exchange['auto_delete']) ? $exchange['auto_delete'] : true;//没有队列时自动删除
        $this->channel->exchange_declare($exchange_name, $type, false, $durable, $auto_delete);
        $this->log->info("exchange {$exchange_name} create");
        //流量控制 prefetch_count=1 如果消费者尚未处理完（还没有确认）则不分配新的消息给它
        if (isset($exchange['prefetch_count'])) {
            $this->channel->basic_qos(null, (int)$exchange['prefetch_count'], null);
        }
        $queues = $exchange['queues'];
        if (empty($queues)) {
            $this->log->fatal('queues empty');
            return false;
        }
        //绑定队列
        foreach ($queues as $queue_name => $queue) {
            $queue_name = "{$this->config['prefix']}.{$queue_name}";
            $queueDurable = isset($queue['durable']) ? $queue['durable'] : $durable;//队列持久化
            $routing_keys = is_array($queue['routing_keys']) ? $queue['routing_keys'] : explode(',', $queue['routing_keys']);
            $callback = $queue['callback'] ? $queue['callback'] : '';
            $no_ack = isset($queue['no_ack']) ? $queue['no_ack'] : false;//收到消息后,是否不需要回复确认即被认为被消费
            $headers = isset($queue['headers']) ? $queue['headers'] : [];
            $table = [];
            if (!empty($headers)) {
                foreach ($headers as $k => $v) {
                    $headers[$k] = str_replace('__PREFIX__', $this->config['prefix'], $v);
                }
                $table = new AMQPTable($headers);
            }
            $this->channel->queue_declare($queue_name, false, $queueDurable, false, false, false, $table);
            foreach ($routing_keys as $routing_key) {
                $this->channel->queue_bind($queue_name, $exchange_name, $routing_key);//绑定路由
                $this->log->info("queue {$queue_name} bind {$routing_key}");
            }
            if (!empty($callback)) {
                if (is_string($callback)) {
                    $callback = [$this->getClassObj($callback), 'index'];
                } else if (is_array($callback)) {
                    if (is_string($callback[0])) {
                        $callback[0] = $this->getClassObj($callback[0]);
                    }
                }
                $this->channel->basic_consume($queue_name, '', false, $no_ack, false, false, function ($message) use ($callback) {
                    $args = func_get_args();
                    $msg = $args[0];
                    $routing_key = $msg->delivery_info['routing_key'];
                    $exchange = $msg->delivery_info['exchange'];
                    $this->log->notice("exchange = {$exchange} routing_key = {$routing_key} body = {$msg->body}");
                    try {
                        call_user_func_array($callback, $args);
                    } catch (\Exception $e) {
                        while (Service::$transNum > 0) {
                            $this->log->info('transNum auto rollback ' . Service::$transNum);
                            Service::rollback();
                        }
                        if ($e instanceof Exception || $e instanceof TencentCloudSDKException) {
                            $this->log->info('consume callback error ' . $e->getMessage() . ' ' . $e->getFile() . ' line ' . $e->getLine());
                        } else {
                            $this->log->fatal('consume callback throw error ' . $e->getMessage() . ' ' . $e->getFile() . ' line ' . $e->getLine());
                            throw $e;
                        }
                    }
                });
                $this->log->info("queue {$queue_name} consume");
            } else {
                $this->log->info("queue {$queue_name} not consume");
            }
        }
        return true;
    }

    protected function getClassObj($className)
    {
        if (is_object($className)) return $className;
        if (!isset($this->callbackObjs[$className])) {
            $this->callbackObjs[$className] = new $className($this->log, [
                'pid' => $this->pid,
                'ppid' => $this->ppid,
                'process_name' => $this->process_name
            ]);
        }
        return $this->callbackObjs[$className];
    }

}
