<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/8
 * Time: 17:36
 */
namespace app\mq\callbacks;

use bxkj_common\HttpClient;
use bxkj_common\RabbitMqChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/*
 * 对于需要应答的消息，不管消息业务有没有处理成功一定要应答，不能中断，防止出现死信
 * $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
 * 如果业务处理失败了可以
 * return $this->failed($msg,true);
 * $msg->delivery_info;
 * 'routing_key'
 */

class Goods extends ConsumerCallback
{
    /**
     * 商品处理
     * @param AMQPMessage $msg
     */
    public function process(AMQPMessage $msg)
    {
        $list = [];
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $page = $data['page'];
            $httpClient = new HttpClient();
            $para['page'] = $page;
            $para['pageSize'] = 100;
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL."goods/getGoodsList", $para)->getData('json');
            if($result['code'] == '200'){
                $list = $result['result']['list'];
                if($list) {
                    $goods = new \app\taokegoods\service\Goods();
                    $goods->addAsycGoods($list);
                    $rabbitChannel = new RabbitMqChannel(['goods.add_goods']);
                    $rabbitChannel->exchange('main')->sendOnce('goods.add.process', ['page' => $page+1]);
                } else {
                    $this->log->info("list empty");
                    $this->ack($msg);
                }
            } else {
                $this->log->info("host error");
                $this->ack($msg);
            }
        }
        $this->log->info("addgood ok");
        $this->ack($msg);
    }
}