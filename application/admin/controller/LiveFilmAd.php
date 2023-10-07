<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class LiveFilmAd extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:live_film_ad:select');
        $liveFilmAdService = new \app\admin\service\LiveFilmAd();
        $get = input();
        $total = $liveFilmAdService->getTotal($get);
        $page = $this->pageshow($total);
        $result = $liveFilmAdService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $result);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:live_film_ad:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $params = input();
            $liveFilm = new \app\admin\service\LiveFilmAd();
            $result = $liveFilm->add($params);
            if (!$result) $this->error($liveFilm->getError());
            alog("live.live_film_ad.add", "新增直播视频广告 ID：".$result);
            $this->success('新增成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:live_film_ad:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('live_film_ad')->where('id', $id)->find();
            if (empty($info)) $this->error('广告不存在');
            $video_duration = $info['video_duration'];
            $video_duration_h = $video_duration_i = $video_duration_s = 0;
            if ($video_duration > 0){
                $video_duration_h = floor($video_duration/3600);
                $video_duration_i = floor(($video_duration - $video_duration_h * 3600)/60);
                $video_duration_s = floor($video_duration - $video_duration_h * 3600 - $video_duration_i * 60);
            }
            $info['video_duration_h'] = $video_duration_h;
            $info['video_duration_i'] = $video_duration_i;
            $info['video_duration_s'] = $video_duration_s;
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
        }
    }

    public function get_suggests()
    {
        $lfaService = new \app\admin\service\LiveFilmAd();
        $result = $lfaService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:live_film_ad:select');
        $liveFilmService = new \app\admin\service\LiveFilmAd();
        $get = input();
        $total = $liveFilmService->getTotal($get);
        $page = $this->pageshow($total);
        $films = $liveFilmService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $films);
        return $this->fetch();
    }


    public function change_status()
    {
        $this->checkAuth('admin:live_film_ad:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择视频');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('live_film_ad')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("live.live_film_ad.edit", "编辑直播视频广告 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('admin:live_film_ad:delete');
    }

    public function get_ads_duration()
    {
        $this->checkAuth('admin:live_film_ad:select');
        $ids = input('aids');
        if (empty($ids)) $this->error('请选择片头广告');
        $idsArr = explode(',', trim($ids));
        $idsArr = array_unique($idsArr);
        $list = Db::name('live_film_ad')->whereIn('id', $idsArr)->limit(count($idsArr))->select();
        $totalDuration = 0;
        foreach ($list as $item) {
            $totalDuration += $item['video_duration'];
        }
        $this->success('获取成功', ['duration' => $totalDuration, 'duration_str' => duration_format($totalDuration)]);
    }

}
