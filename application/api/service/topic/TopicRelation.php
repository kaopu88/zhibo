<?php

namespace app\api\service\topic;

use app\common\service\Service;
use think\Db;

class TopicRelation extends Service
{
    public function inserts($data, $target_id)
    {
        $rows = [];
        $now = time();
        foreach ($data as $topic)
        {
            $rows[] = ['topic_id' => $topic, 'video_id' => $target_id, 'create_time' => $now];
        }
        $rs =  Db::name('topic_relation')->insertAll($rows);
        if (!$rs) return false;
        return true;
    }
}