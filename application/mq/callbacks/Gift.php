<?php

namespace app\mq\callbacks;

use bxkj_module\service\Fenxiao;
use bxkj_module\service\GiftLog;
use PhpAmqpLib\Message\AMQPMessage;

class Gift extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (empty($data)) return $this->failed($msg, true);
        $act = strtolower($data['act']);
        $funName = parse_name($act, 1, false) . "Handler";
        if (!method_exists($this, $funName)) return $this->failed($msg, true);
        $res = call_user_func_array([$this, $funName], [$data['data']]);
        if (!$res) return $this->failed($msg, true);
        $this->ack($msg);
    }

    protected function giveHandler($data)
    {
        if (empty($data)) return false;
        $gift = new GiftLog();
        $result = $gift->give($data);
        if (!$result) {
            $this->log->info('gift give error ' . $gift->getError()->getMessage());
            return true;
        }
        $fenxiao = new Fenxiao();
        $res = $fenxiao->distributionCommission($data['user_id'], $data['total_millet'], $data['gift_id'], 'video_gift');
        if (!$res) {
            $this->log->info('fenxiao video gift error ');
            return true;
        }
        return true;
    }
}
