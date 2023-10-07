<?php

namespace app\api\controller;

use app\common\controller\UserController;
use bxkj_common\RabbitMqChannel;
use think\Db;


class Behavior extends UserController
{
    public function watch()
    {
        $params = input();
        $data = $params['data'] ? json_decode($params['data'], true) : [];
        $total = 0;
        if ($data) {
            $user = [];
            if (!empty(USERID)) {
                $user['alias_id'] = USERID;
                $user['alias_type'] = 'user';
            } else if (!empty(APP_MEID)) {
                $user['alias_id'] = APP_MEID;
                $user['alias_type'] = 'meid';
            }
            if (empty($user)) return $this->jsonError('用户未登录');
            $rabbitChannel = new RabbitMqChannel(['user.behavior']);
            $list = [];
            foreach ($data as $item) {
                $tmp = [
                    'video_id' => $item['video_id'],
                    'start_time' => $item['start_time'],
                    'max_duration' => isset($item['max_duration']) ? $item['max_duration'] : $item['duration'],
                    'duration' => isset($item['sum_duration']) ? $item['sum_duration'] : $item['duration']
                ];
                $tmp = array_merge($tmp, $user);
                if (!empty($tmp['video_id']) && !empty($tmp['start_time']) && !empty($tmp['max_duration']) && !empty($tmp['duration'])) {
                    $tmp['create_time'] = time();
                    Db::name('watch_history')->insert($tmp);
                    $list[] = $tmp;
                }
            }
            $rabbitChannel->exchange('main')->sendOnce('user.behavior.batch_watch', $list);
            $total = count($list);
        }
        return $this->success(['total' => $total], '接收成功');
    }
}