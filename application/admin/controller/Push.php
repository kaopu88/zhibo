<?php

namespace app\admin\controller;

use think\facade\Request;

class Push extends Controller
{
    public function release()
    {
        if (Request::isGet()) {
            $this->assign('_info', ['content_type' => 'url']);
            return $this->fetch();
        } else {
            $post = input();
            $trigger_time = empty($post['trigger_time']) ? time() : strtotime($post['trigger_time']);
            unset($post['trigger_time']);
            if (empty($post['title'])) $this->error('推送标题不能为空');
            if (empty($post['url']) && empty($post['text'])) $this->error('推送内容或者链接不能为空');
            $timer = new \bxkj_module\service\Timer();
            $key = $timer->add([
                'trigger_time' => $trigger_time,
                'cycle' => 0,
                'method' => 'post',
                'data' => $post,
                'url' => ERP_URL."/push_callback/release"
            ]);
            $this->success("任务已提交，key:{$key}");
        }
    }
}
