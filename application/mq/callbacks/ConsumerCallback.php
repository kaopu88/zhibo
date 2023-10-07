<?php

namespace app\mq\callbacks;

use bxkj_module\controller\Controller;
use app\mq\service\RunLog;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class ConsumerCallback extends Controller
{
    protected $process_name;
    protected $log;
    protected $processInfo;

    public function __construct(&$log, $processInfo)
    {
        parent::__construct();

        $this->log = &$log;
        if (false) $this->log = new RunLog();
        $this->process_name = $processInfo['process_name'];
        $this->processInfo = $processInfo;
    }

    //消息处理失败
    protected function failed(AMQPMessage $msg, $ack = false, $error = null, $maxRetry = null)
    {
        $code = 1;
        $str = 'undefined';
        if (is_error($error)) {
            $code = $error->getStatus();
            $str = $error->getMessage();
        } else if (is_string($error)) {
            $str = $error;
        }
        $channel = $msg->delivery_info['channel'];
        if (false) {
            $channel = new AMQPChannel();
        }
        $exchange_name = $msg->get('exchange');//获取消息所在的交换机名称
        $prefix = config('mq.prefix');
        $tmpName = preg_replace("/^{$prefix}\./", '', $exchange_name);
        $exchangeConfigs = config('mq.exchanges');
        $exchangeConfigs = is_array($exchangeConfigs) ? $exchangeConfigs : [];
        $exchangeConfig = is_array($exchangeConfigs[$tmpName]) ? $exchangeConfigs[$tmpName] : [];
        $isRetry = $exchangeConfig['retry'];
        $maxRetry = isset($maxRetry) ? $maxRetry : $exchangeConfig['max_retry'];
        $retry_delay = $exchangeConfig['retry_delay'] ? (int)$exchangeConfig['retry_delay'] : (10 * 1000);
        if ($isRetry) {
            $retry = $this->getRetryCount($msg);
            //重试超过3次则直接抛给错误队列
            $oldRoutingKey = $msg->get('routing_key');
            if ($retry >= $maxRetry) {
                $failedExchangeName = "{$exchange_name}.failed";
                $channel->exchange_declare($failedExchangeName, 'topic', false, true, false);
                $failedQueueName = "{$exchange_name}.failed";
                $channel->queue_declare($failedQueueName, false, true, false, false, false);
                $channel->queue_bind($failedQueueName, $failedExchangeName, '#');
                $channel->basic_publish($msg, $failedExchangeName, $oldRoutingKey);
            } else {
                $retryExchangeName = "{$exchange_name}.retry";
                $retryQueueName = "{$exchange_name}.retry";
                $headers = [
                    //'x-dead-letter-routing-key'=>'',//默认使用消息自身的routing-key
                    'x-dead-letter-exchange' => $exchange_name,
                    'x-message-ttl' => $retry_delay
                ];
                $channel->exchange_declare($retryExchangeName, 'topic', false, true, false);
                $table = new AMQPTable($headers);
                $channel->queue_declare($retryQueueName, false, true, false, false, false, $table);
                $channel->queue_bind($retryQueueName, $retryExchangeName, '#');
                $channel->basic_publish($msg, $retryExchangeName, $oldRoutingKey);
            }
        }
        if ($ack) {
            $channel->basic_ack($msg->delivery_info['delivery_tag']);
        }
        return false;
    }

    //应答消息
    protected function ack(AMQPMessage $msg)
    {
        $channel = $msg->delivery_info['channel'];
        $channel->basic_ack($msg->delivery_info['delivery_tag']);
    }

    //获取消息重试次数
    protected function getRetryCount(AMQPMessage $msg)
    {
        $retry = 0;
        if ($msg->has('application_headers')) {
            $headers = $msg->get('application_headers')->getNativeData();
            if (isset($headers['x-death'][0]['count'])) {
                $retry = $headers['x-death'][0]['count'];
            }
        }
        return (int)$retry;
    }


}
