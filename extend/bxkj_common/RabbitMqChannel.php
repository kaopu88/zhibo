<?php

namespace bxkj_common;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class RabbitMqChannel
 * @method queue_declare($queue = '', $passive = false, $durable = false, $exclusive = false, $auto_delete = true, $nowait = false, $arguments = array(), $ticket = null)
 * @method basic_publish($msg, $exchange = '', $routing_key = '', $mandatory = false, $immediate = false, $ticket = null)
 * @method exchange_declare($exchange, $type, $passive = false, $durable = false, $auto_delete = true, $internal = false, $nowait = false, $arguments = array(), $ticket = null)
 * @method queue_bind($queue, $exchange, $routing_key = '', $nowait = false, $arguments = array(), $ticket = null)
 *
 */
class RabbitMqChannel
{
    protected $connection;
    protected $mqConfig;
    protected $channel;
    protected $durable;
    protected $exchange = '';

    public function __construct($queues = [], $channel = null, $mqConfig = null)
    {
        $prefix = config('mq.prefix');
        $this->exchange = "{$prefix}.main";
        $this->mqConfig = [
            'host' => config('mq.host'),
            'port' => config('mq.port'),
            'user' => config('mq.user'),
            'password' => config('mq.password'),
            'vhost' => config('mq.vhost')
        ];
        if (is_array($mqConfig)) {
            $this->mqConfig = $mqConfig;
        }
        $this->connection = new AMQPStreamConnection(
            $this->mqConfig['host'],
            $this->mqConfig['port'],
            $this->mqConfig['user'],
            $this->mqConfig['password'],
            $this->mqConfig['vhost']);
        $this->channel = $this->connection->channel($channel);
        if (false) {
            $this->channel = new AMQPChannel();
        }
        $queueConfig = [
            'name' => '',
            'passive' => false,
            'durable' => true,
            'exclusive' => false,
            'auto_delete' => false,
            'nowait' => false,
            'arguments' => array(),
            'ticket' => null
        ];
        //声明队列
        foreach ($queues as $queue) {
            if (is_array($queue)) {
                $queue = array_merge($queueConfig, $queue);
            } else {
                $name = $queue;
                $queue = $queueConfig;
                $queue['name'] = $name;
            }
            $queueName = "{$prefix}." . $queue['name'];
            $this->channel->queue_declare($queueName, $queue['passive'], $queue['durable'], $queue['exclusive'], $queue['auto_delete']);
        }
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['exchange_declare', 'queue_declare', 'queue_bind', 'basic_publish'])) {
            return call_user_func_array([$this->channel, $name], $arguments);
        }
    }

    public function exchange($exchange)
    {
        $prefix = config('mq.prefix');
        $this->exchange = "{$prefix}.{$exchange}";
        return $this;
    }

    public function send($routing_key, $data, $delivery_mode = 2, $expiration = null)
    {
        $properties = [
            'content_type' => 'text/plain'
        ];
        $json = is_array($data) ? json_encode($data) : $data;
        if (isset($delivery_mode)) {
            $properties['delivery_mode'] = $delivery_mode;
        }
        if (isset($expiration)) {
            $properties['expiration'] = $expiration;
        }
        if (empty($this->exchange) || empty($routing_key)) return false;
        $msg = new AMQPMessage($json, $properties);
        // var_dump($msg);
        $aa =  $this->basic_publish($msg, $this->exchange, $routing_key);
        return true;
    }

    public function sendOnce($routing_key, $data, $delivery_mode = 2, $expiration = null)
    {
        $res = $this->send($routing_key, $data, $delivery_mode, $expiration);
        $this->close();
        return $res;
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}