<?php

namespace app\mq\callbacks;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Index extends ConsumerCallback
{
    public function test(AMQPMessage $msg)
    {
        $retry = $this->getRetryCount($msg);
        $this->log->info("retry {$retry}");
        $this->log->info('failed');
        return $this->failed($msg, true, make_error('test', time()));
    }
}
