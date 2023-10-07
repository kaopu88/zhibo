<?php

namespace app\admin\controller;

use bxkj_common\DateTools;
use think\Db;

class LiveFilmTimeline extends Controller
{
    public function schedule()
    {
        $this->checkAuth('admin:live_film:schedule');
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:live_film:schedule');
        $post = input();
        $liveFilmTimeline = new \app\admin\service\LiveFilmTimeline();
        $result = $liveFilmTimeline->add($post);
        if (!$result) $this->error($liveFilmTimeline->getError());
        alog("live.film_timeline.add", "新增电影时间线 ID：".$result);
        $this->success('创建任务成功', $result);
    }

    public function get_timeline()
    {
        $startTime = input('start_time');
        $endTime = input('end_time');
        if (empty($startTime) || empty($endTime)) $this->error('缺少必要参数');
        $timeline = new \app\admin\service\LiveFilmTimeline();
        $list = $timeline->getTimeline($startTime, $endTime);
        $this->success('获取成功', $list ? $list : []);
    }

    public function init()
    {
        $this->checkAuth('admin:live_film:schedule');
        $data['server_time'] = time();
        $data['film_ranks'] = [];
        $timeline = new \app\admin\service\LiveFilmTimeline();
        $playingList = Db::name('live_film_timeline')->field('id,live_title,live_cover,start_time,status')->where(['status' => '1'])->order('start_time desc,id desc')->limit(10)->select();
        $data['playing'] = $playingList ? $playingList : [];
        $data['rank'] = $timeline->getRankList(DateTools::getWeekNum());
        return json_success($data, '获取成功');
    }

    public function get_detail()
    {
        $id = input('id');
        if (empty($id)) $this->error('请选择电影');
        $timeline = new \app\admin\service\LiveFilmTimeline();
        $info = $timeline->getInfo($id);
        if (!$info) $this->error('电影不存在');
        return json_success($info, '获取成功');
    }

    public function cancel()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择电影');
        $timeline = new \app\admin\service\LiveFilmTimeline();
        $res = $timeline->cancel($ids);
        if (!$res) $this->error($timeline->getError());
        alog("live.film_timeline.cancel", "取消电影时间线 ID：".implode(",", $ids));
        return json_success($res, '撤档成功');
    }

    public function get_last_status()
    {
        $startTime = input('start_time');
        $endTime = input('end_time');
        if (empty($startTime) || empty($endTime)) $this->error('缺少必要参数');
        $timeline = new \app\admin\service\LiveFilmTimeline();
        $list = $timeline->getTimeline($startTime, $endTime);
        $playingList = Db::name('live_film_timeline')->field('id,live_title,live_cover,start_time,status')->where(['status' => '1'])->order('start_time desc,id desc')->limit(10)->select();
        $data = [
            'list' => $list ? $list : [],
            'rank' => $timeline->getRankList(DateTools::getWeekNum()),
            'playing' => $playingList ? $playingList : []
        ];
        $this->success('获取成功', $data);
    }
}
