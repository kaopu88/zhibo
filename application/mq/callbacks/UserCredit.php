<?php

namespace app\mq\callbacks;

use bxkj_module\service\UserCreditLog;
use PhpAmqpLib\Message\AMQPMessage;

class UserCredit extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $routing_key = $msg->delivery_info['routing_key'];
        $routing_key_arr = explode('.', $routing_key);
        $creditType = $routing_key_arr[2];
        $data = json_decode($msg->body, true);
        if (empty($data) || empty($creditType)) return false;
        $userCredit = new UserCreditLog();
        $res = $userCredit->record($creditType, $data);
        if (!$res) {
            $this->log->notice($userCredit->getError());
            return false;
        }
        return true;
    }
}
