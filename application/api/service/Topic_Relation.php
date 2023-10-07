<?php


namespace app\api\service;


use app\common\service\Service;
use think\Db;

class Topic_Relation extends Service
{
    public function getVideoByTopic($topic_id, $offset=0, $length=9)
    {
        $res = Db::name('video')
            ->alias('f')
            ->join('topic_relation t', 'f.id=t.video_id')
            ->join('user u', 'f.user_id=u.user_id')
            ->where(['t.topic_id'=>$topic_id])
            ->field('f.*, u.nickname, u.avatar')
            ->limit($offset,$length)
            ->select();

        return $res;
    }
}