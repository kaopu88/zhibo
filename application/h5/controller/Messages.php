<?php

namespace app\h5\controller;

use think\Db;

class Messages extends BxController
{
    function notice_detail()
    {
        $id = input('id');
        $detail = Db::name('message')->where(['id' => $id, 'delete_time' => null])->find();
        if (!$detail) $this->error('通知消息不存在或者已删除');
        $content = json_decode($detail['content'], true);
        $this->assign('_info', [
            'logo' => img_url($detail['send_avatar']),
            'author' => $detail['send_nickname'],
            'title' => $detail['title'],
            'content' => $content['text']
        ]);
        return $this->fetch();
    }

    function push_detail()
    {
        $id = input('id');
        $detail = Db::name('system_message')->where(['id' => $id, 'delete_time' => null])->find();
        if (!$detail) $this->error('通知消息不存在或者已删除');
        $this->assign('_info', [
            'logo' => img_url('', '', 'logo'),
            'author' => APP_PREFIX_NAME.'小助手',
            'title' => $detail['title'],
            'content' => $detail['content']
        ]);
        return $this->fetch('notice_detail');
    }
}