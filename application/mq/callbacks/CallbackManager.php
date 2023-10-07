<?php

namespace app\mq\callbacks;

use bxkj_common\HttpClient;
use PhpAmqpLib\Message\AMQPMessage;
use think\Db;

/*
 * 对于需要应答的消息，不管消息业务有没有处理成功一定要应答，不能中断，防止出现死信
 * $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
 * 如果业务处理失败了可以
 *return $this->failed($msg,true);
 */

class CallbackManager extends ConsumerCallback
{
    public function process(AMQPMessage $msg)
    {
        $routing_key = $msg->delivery_info['routing_key'];
        $routing_key_arr = explode('.', $routing_key);
        $type = $routing_key_arr[2];
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $type = isset($data['type']) ? $data['type'] : $type;
            $funName = parse_name($type, 1, false) . "Handler";
            if (method_exists($this, $funName)) {
                $res = call_user_func_array([$this, $funName], [$data]);
                if (!$res) return $this->failed($msg, true);
            }
        }
        $this->ack($msg);
    }

    protected function tencentcloudVodHandler($data)
    {
        $this->log->notice("tencentcloudVodHandler:{$data['id']}");
        $info = Db::name('tencentcloud_vod')->where(['id' => $data['id']])->find();
        if (empty($info) || empty($info['callback']) || empty($info['events']) || $info['notice_status'] != '0') return true;
        $http = new HttpClient();
        $result = $http->setContentType('json')->post($info['callback'], $info['events'], 10)->getData('');
        $result = $result ? strtoupper(trim($result)) : 'FAILED';
        $this->log->info('vod_callback: ' . $result);
        if ($result != 'OK') return false;
        Db::name('tencentcloud_vod')->where(['id' => $data['id']])->update(['notice_status' => '1', 'notice_time' => time()]);
        return true;
    }

    protected function urlHandler($data)
    {
        $this->log->notice("urlHandler:{$data['url']}");
        if (empty($data['url'])) return true;
        $http = new HttpClient();
        $method = $data['method'] ? $data['method'] : 'post';
        $timeout = (int)$data['timeout'] ? $data['timeout'] : 10;
        $fields = $data['fields'] ? (is_string($data['fields']) ? json_decode($data['fields'], true) : $data['fields']) : [];
        $result = $http->curl($data['url'], $method, $fields, (int)$timeout)->getData('');
        if ($data['confirm'] == '1') {
            $result = $result ? strtoupper(trim($result)) : 'FAILED';
            if ($result != 'OK') return false;
        }
        return true;
    }
}
