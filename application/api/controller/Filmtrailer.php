<?php

namespace app\api\controller;
use app\common\controller\Controller;
use app\api\service\LiveFilmTimeline;

class FilmTrailer extends Controller
{
    public function getNotice()
    {
        $timelineService = new LiveFilmTimeline();
        $list = $timelineService->getTrailerList(0, 5);
        $str = '';
        foreach ($list as $item) {
            $day = date('m-d', $item['start_time']) . '日';
            if (date('Y-m-d', $item['start_time']) == date('Y-m-d')) {
                $day = '今日';
            } else if (date('Y-m-d', $item['start_time']) == date('Y-m-d', time() + 86400)) {
                $day = '明日';
            }
            $str .= "{$day}" . date('H:i', $item['start_time']) . "播出《{$item['live_title']}》    ";
        }
        return $this->success(['notice' => rtrim($str)], '获取成功');
    }

    public function getTimeLine()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $timelineService = new LiveFilmTimeline();
        $list = $timelineService->getTrailerList($offset, $length);
        if (!$list) return $this->jsonError($timelineService->getError());
        return $this->success($list, '获取成功');
    }
}
