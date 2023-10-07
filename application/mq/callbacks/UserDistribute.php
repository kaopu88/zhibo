<?php

namespace app\mq\callbacks;

use bxkj_module\service\Fenxiao;
use PhpAmqpLib\Message\AMQPMessage;
use think\Db;

class UserDistribute extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (empty($data)) return false;
        $fenxiao = new Fenxiao();
        $userId= $data['user_id'];
        $user = Db::name('user')->field('pid')->where('user_id', $userId)->find();
        if (empty($user) || empty($user['pid'])) return false;
        $fenxiao->distribute($user['pid'], ['child_num' => 1]);
        $distribute_info = Db::name('sys_config')->where(array('mark' => 'giftdistribute'))->find();
        $ds_data = json_decode($distribute_info['value'], true);
        if ( !empty($distribute_info)  && !empty($ds_data['is_open'])) {
            $fenxiao->distributeUpgrade($user['pid']);
        }

        return true;
    }
}
