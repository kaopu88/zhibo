<?php

namespace bxkj_recommend\behavior;

use bxkj_recommend\exception\Exception;
use bxkj_recommend\model\UserVideoTag;
use bxkj_recommend\model\Video;
use bxkj_recommend\Viewed;

class WatchBehavior extends Behavior
{
    //观看视频（批量处理）
    public function batchWatch($watchDatas)
    {
        $userMark = $this->user->getUserMark();
        foreach ($watchDatas as $watchData) {
            try {
                $v = new Video($watchData['video_id'], true);
                $this->watch($v, $watchData);
            } catch (Exception $exception) {
                continue;
            }
        }
    }

    //观看视频
    public function watch(Video $video, $watchData)
    {
        $viewed = new Viewed($this->user);
        $viewed->insert('total', $video->id);
        $watchData['duration'] = max($watchData['duration'], $watchData['max_duration']);
        //单位都是 ms
        $video->watch($this->user, $watchData['start_time'], $watchData['max_duration'], $watchData['duration']);
        $this->user->watch($video, $watchData['start_time'], $watchData['max_duration'], $watchData['duration']);
        if (!$video->isOwn($this->user)) {
            $tags = $video->getTags();
            foreach ($tags as $tag) {
                $uvTag = new UserVideoTag($this->user, $tag);
                $state = $video->getSwitchState($watchData['max_duration']);
                $uvTag->watch($watchData['start_time'], $state, $watchData['duration']);
            }
        }
        return true;
    }
}