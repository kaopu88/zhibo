<?php

namespace app\mq\callbacks;

use bxkj_module\service\UserAddressBook;
use PhpAmqpLib\Message\AMQPMessage;

class UserAddBook extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        //处理数据
        if(!empty($data) && !empty($data['user_id'])){
           $addBookMod = new UserAddressBook();
           $addBookMod->save($data['user_id'],$data['data']);
        }
        $this->ack($msg);
    }
}
