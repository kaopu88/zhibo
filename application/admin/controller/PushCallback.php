<?php

namespace app\admin\controller;


use bxkj_module\service\Message;
use think\facade\Request;

class PushCallback extends \think\Controller
{
    public function release()
    {
        $post = Request::post();
        $message = new Message();
        if (empty($post['group_id']) || empty($post['title'])) return json_error('推送参数不正确');
        $message->setSender('', 'helper')->setReceiver('', $post['group_id'])->sendPush([
            'title' => $post['title'],
            'text' => $post['text'] ? $post['text'] : '',
            'url' => $post['url'] ? $post['url'] : '',
            'summary' => $post['summary'] ? $post['summary'] : '',
            'directly' => $post['directly']
        ]);
        return json_success([], '推送成功');
    }
}