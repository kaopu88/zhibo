<?php

namespace app\api\service;
use app\common\service\Service;
use app\common\service\User;
use think\Db;

class LiveFilmTimeline extends Service
{
    public function getTrailerList($offset = 0, $length = 5)
    {
        $db = Db::name('live_film_timeline')->field('id,live_title,live_cover,start_time,anchor_uid');
        $db->where('start_time >='.time());
        $list = $db->limit($offset,$length)->order('start_time asc')->select();
        $list = $list ? $list : [];
        $user_ids = $this->getIdsByList($list, 'anchor_uid', false);
        $userService = new User();
        $users = [];
        if ($user_ids) {
            $users = $userService->getUsers($user_ids, null, 'user_id,nickname,avatar');
        }
        $now = time();
        foreach ($list as &$item) {
            $item['date_str']=date('dm月', $item['start_time']);
            $item['date'] = date('m-d', $item['start_time']);
            $item['time'] = date('H:i', $item['start_time']);
            $item['anchor'] = self::getItemByList($item['anchor_uid'], $users, 'user_id');
            $item['title'] = "{$item['anchor']['nickname']}播放《{$item['live_title']}》";
            $item['time_status'] = '0';
            if ($item['start_time'] <= 3600 + $now) {
                $item['time_status'] = '1';
            }
        }
        return $list;
    }
}