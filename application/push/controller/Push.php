<?php

namespace app\push\controller;

use bxkj_push\AomyPush;
use bxkj_common\Console;
use bxkj_module\push\AppPush;
use bxkj_module\service\Message;
use think\facade\Request;

class Push extends Api
{
    public function handler()
    {
        $this->persistent();
        $taskId = Request::post('task');
        if (!empty($taskId)) {
            $appPush = new AppPush();
            $result = $appPush->send($taskId);
            bxkj_console('push result:' . $result);
        }
        //返回10nodejs不做任何处理
        return json(array('status' => 10, 'info' => '发送成功'));
    }

    public function print_data()
    {
        $msg_data = Request::post('msg_data');
        $arr = json_decode($msg_data, true);
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function push_test()
    {
        $userId = Request::get('user_id');
        //$userId = 10108;
        $AomyPush = new AomyPush();
        $queryData = [
            'cat_type' => 'like',
            'type' => 'like_film',
            'msg_id' => 81,
            'user_id' => $userId,//10383 10178
        ];
        $res = $AomyPush->setUser(array('user_id' => $userId))->allTo(array(
            'title' => '你是真的皮' . get_ucode(4) . '赞了你的作品',
            'text' => '非常好哦',
            'after_open' => 'go_app',
            'custom' => array(
                'header' => 'url',
                'url' => getJump('message_list', $queryData)
            )
        ));
        var_dump($res);
    }


}
