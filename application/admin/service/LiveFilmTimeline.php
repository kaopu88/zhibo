<?php

namespace app\admin\service;

use bxkj_common\DateTools;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use bxkj_module\service\Timer;
use think\Db;

class LiveFilmTimeline extends Service
{
    public function getTimeline($startTime, $endTime)
    {
        $where = [
            ["start_time", '>=', $startTime],
            ["start_time", '<', $endTime]
        ];
        $list = Db::name('live_film_timeline')->where($where)->order('start_time asc,id asc')->select();
        $list = $list ? $list : [];
        $anchorUids = self::getIdsByList($list, 'anchor_uid');
        $anchorList = [];
        if (!empty($anchorUids)) {
            $anchorList = Db::name('user')->whereIn('user_id', $anchorUids)->field('user_id,avatar,nickname,level,phone')->select();
        }
        foreach ($list as &$item) {
            if (!empty($item['anchor_uid'])) {
                $anchor = self::getItemByList($item['anchor_uid'], $anchorList, 'user_id');
                $item = array_merge($item, $anchor ? $anchor : []);
            }
            $this->extendLiveNum($item);
        }
        return $list;
    }

    protected function extendLiveNum(&$item)
    {
        $redis = RedisClient::getInstance();
        $item['live_num'] = 0;
        if (!empty($item['room_id'])) {
            $key = "BG_LIVE:{$item['room_id']}:audience";
            $item['live_num'] = (int)$redis->zCount($key, 0, '+inf');
            $item['live_num'] = number_format2($item['live_num']);
        }
    }

    public function getRankList($weekNum, $length = 5)
    {
        $weekTime = DateTools::getWeekTime($weekNum);
        $where = [
            ['box_office', '>', 0],
            ['start_time', '>=', $weekTime],
            ['start_time', '<', $weekTime + (7 * 24 * 3600)],
        ];
        $rankList = Db::name('live_film_timeline')->field('id,live_title,live_cover,start_time,status,box_office')->where($where)->order('box_office desc,id desc')->limit($length)->select();
        $rankList = $rankList ? $rankList : [];
        $weeks = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
        foreach ($rankList as $index => &$rank) {
            $rank['rank'] = $index + 1;
            $rank['week_name'] = $weeks[(int)date('w', $rank['start_time'])];
            $rank['time_str'] = date('H:i', $rank['start_time']);
        }
        return $rankList;
    }

    public function add($inputData)
    {
        $filmId = $inputData['film_id'];
        if (empty($filmId)) return $this->setError('请选择电影');
        $filmInfo = Db::name('live_film')->where(['status' => '1', 'id' => $filmId])->find();
        if (empty($filmInfo)) return $this->setError('电影不存在');
        $play_position = durationstrtotime($inputData['play_position']);
        if ($play_position < 0) return $this->setError('播放位置不能为负值');
        if (empty($inputData['live_title'])) return $this->setError('直播标题不能为空');
        if (empty($inputData['live_cover'])) return $this->setError('直播封面不能为空');
        $type = $inputData['type'];
        if (!in_array($type, ['0', '1', '2', '3', '4', '5'])) return $this->setError('直播类型不正确');
        if ($type != '0' && $type != '4' && empty($inputData['type_val'])) return $this->setError('类型值不能为空');
        $ad_ids = $inputData['ad_ids'] ? explode(',', trim($inputData['ad_ids'])) : [];
        if (durationstrtotime($inputData['ad_duration']) > 0 && empty($ad_ids)) return $this->setError('请设置广告');
        if (empty($inputData['anchor_uid'])) return $this->setError('请选择主播');
        $adArr = [];
        $totalAdDuration = 0;
        foreach ($ad_ids as $ad_id) {
            $ad = Db::name('live_film_ad')->where(['status' => '1', 'id' => $ad_id])->find();
            if (empty($ad)) return $this->setError('片头广告不存在');
            $adArr[] = $ad;
            $totalAdDuration += $ad['video_duration'];
        }
        if (empty($inputData['start_time'])) return $this->setError('请设置开始时间');
        $startTime = strtotime($inputData['start_time']);
        if ($startTime < (time() - 600)) return $this->setError('开始时间不能小于当前时间');
        if (empty($filmInfo['video_duration']) || empty($filmInfo['video_rate'])) return $this->setError('电影媒体信息不全');
        $tmp = $inputData['ad_duration'] ? (durationstrtotime($inputData['ad_duration'])) : $totalAdDuration;
        //结束时间=开始时间+影片时长+广告时长-已播放完的时间+30秒缓冲
        $endTime = $startTime + $filmInfo['video_duration'] + $tmp - $play_position + 30;
        $liveFilmPeriod = new LiveFilmPeriod();
        $anchorInfo = $liveFilmPeriod->getInfo($inputData['anchor_uid'], date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', $endTime));
        if ($anchorInfo['available_status'] != '1') return $this->setError('播出时段主播已占用');
        $where = ['start_time' => $startTime, 'end_time' => $endTime, 'anchor_uid' => $anchorInfo['user_id'], 'film_id' => $filmId];
        $timelineNum = Db::name('live_film_timeline')->where($where)->count();
        if ($timelineNum > 0) return $this->setError('排片重复');
        if (($endTime - $startTime) < 300) return $this->setError('播出总时长不得低于5分钟');
        $data = [
            'film_id' => $filmId,
            'anchor_uid' => $anchorInfo['user_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'offset' => $play_position,
            'aid' => 0,
            'ad_ids' => $ad_ids ? implode(',', $ad_ids) : '',
            'box_office' => 0,
            'type' => $type,
            'type_val' => $inputData['type_val'],
            'live_title' => $inputData['live_title'],
            'live_cover' => $inputData['live_cover'],
            'status' => '0',
            'ad_duration' => $tmp,
            'create_time' => time()
        ];
        Service::startTrans();
        $id = Db::name('live_film_timeline')->insertGetId($data);
        if (!$id) return $this->setError('创建任务失败01');
        $data['id'] = $id;
        $periodId = Db::name('live_film_period')->insertGetId([
            'user_id' => $anchorInfo['user_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'timeline_id' => $id
        ]);
        if (!$periodId) return $this->setError('创建任务失败02');
        $timerService = new Timer();
        $key = $timerService->add([
            'trigger_time' => $startTime,
            'cycle' => 0,
            'method' => 'post',
            'data' => ['timeline_id' => $id],
            'url' => CORE_URL . '/open/start_live_film'
        ]);
        if (!$key) return $this->setError('创建任务失败03');
        $num = Db::name('live_film_timeline')->where('id', $id)->update([
            'start_timer_key' => $key
        ]);
        if (!$num) return $this->setError('创建任务失败04');
        Service::commit();
        Db::name('live_film')->where('id', $filmId)->setInc('use_num', 1);
        $data['start_timer_key'] = $key;
        $anchor = Db::name('user')->where('user_id', $anchorInfo['user_id'])->field('user_id,avatar,nickname,level,phone')->find();
        $data['live_num'] = 0;
        $data = array_merge($data, $anchor ? $anchor : []);
        return $data;
    }

    public function cancel($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $timelines = Db::name('live_film_timeline')->whereIn('id', $ids)->select();
        if (empty($timelines)) return $this->setError('电影不存在');
        $timer = new Timer();
        $httpClient = new HttpClient();
        $url = CORE_URL . '/open/end_live_film';
        foreach ($timelines as $timeline) {
            if ($timeline['status'] == '1') {
                $params = ['room_id' => $timeline['room_id'], 'timeline_id' => $timeline['id']];
                $httpClient->post($url, $params, 3)->getData();
            }
            if (!empty($timeline['start_timer_key'])) {
                $timer->remove([$timeline['start_timer_key']]);
            }
            if (!empty($timeline['end_timer_key'])) {
                $timer->remove([$timeline['end_timer_key']]);
            }
            Db::name('live_film_period')->where('timeline_id', $timeline['id'])->delete();
        }
        $num = Db::name('live_film_timeline')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('撤档失败');
        return $num;
    }

    public function getInfo($id)
    {
        $info = Db::name('live_film_timeline')->where(['id' => $id])->find();
        if ($info) {
            $filmInfo = Db::name('live_film')->where('id', $info['film_id'])->find();
            if ($filmInfo) {
                $filmInfo['video_duration_str'] = duration_format($filmInfo['video_duration']);
            }
            $info['film_info'] = $filmInfo ? $filmInfo : [];
            $info['ad_duration_str'] = duration_format($info['ad_duration']);
            $info['offset_str'] = duration_format($info['offset']);
            if (!empty($info['ad_ids'])) {
                $adIds = explode(',', $info['ad_ids']);
                $ads = Db::name('live_film_ad')->whereIn('id', $adIds)->field('id,ad_title')->select();
                $info['ads'] = $ads ? $ads : [];
            }
            $anchor = Db::name('user')->where('user_id', $info['anchor_uid'])->field('user_id,nickname,avatar')->find();
            $info['anchor'] = $anchor;
        }
        return $info;
    }


}