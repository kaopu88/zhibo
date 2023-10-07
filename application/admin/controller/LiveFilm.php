<?php

namespace app\admin\controller;

use app\admin\service\LiveFilmTimeline;
use bxkj_common\YunBo;
use think\Db;
use think\facade\Request;

class LiveFilm extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:live_film:select');
        $liveFilmService = new \app\admin\service\LiveFilm();
        $get = input();
        $total = $liveFilmService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $liveFilmService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:live_film:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $params = input();
            $liveFilm = new \app\admin\service\LiveFilm();
            $result = $liveFilm->add($params);
            if (!$result) $this->error($liveFilm->getError());
            alog("live.film.add", "新增直播视频 ID：".$result);
            $this->success('新增成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:live_film:update');
        if (Request::isGet()) {
            $filmId = input('id');
            if (empty($filmId)) $this->error('请选择电影');
            $liveFilm = new \app\admin\service\LiveFilm();
            $info = $liveFilm->getInfo($filmId);
            if (empty($info)) $this->error('电影不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $params = input();
            $liveFilm = new \app\admin\service\LiveFilm();
            $result = $liveFilm->update($params);
            if (!$result) $this->error($liveFilm->getError());
            alog("live.film.edit", "编辑直播视频 ID：".$params['id']);
            $this->success('更新成功');
        }
    }

    public function change_status()
    {
        $this->checkAuth('admin:live_film:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择电影');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('live_film')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("live.film.edit", "编辑直播视频 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function collecting()
    {
        $this->checkAuth('admin:live_film:select');
        $third_url = input('third_url');
        $result = YunBo::getVideo($third_url);
        if (is_error($result)) $this->error($result);
        $this->success('播放地址解析成功', $result);
    }


    public function get_suggests()
    {
        $promoterService = new \app\admin\service\LiveFilm();
        $result = $promoterService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:live_film:select');
        $liveFilmService = new \app\admin\service\LiveFilm();
        $get = input();
        $total = $liveFilmService->getTotal($get);
        $page = $this->pageshow($total);
        $films = $liveFilmService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $films);
        return $this->fetch();
    }

    public function get_film_info()
    {
        $this->checkAuth('admin:live_film:select');
        $film_id = input('film_id');
        if (empty($film_id)) $this->error('请选择影片');
        $liveFilmService = new \app\admin\service\LiveFilm();
        $info = $liveFilmService->getInfo($film_id);
        if (empty($info)) $this->error('影片不存在');
        $this->success('获取影片信息成功', $info);
    }

    public function get_anchor_info()
    {
        $this->checkAuth('admin:live_film_anchor:select');
        $anchor_uid = input('anchor_uid');
        $startTimeStr = input('start_time');
        $endTimeStr = input('end_time');
        if (empty($anchor_uid)) $this->error('请选择主播');
        $liveFilmService = new \app\admin\service\LiveFilmPeriod();
        $info = $liveFilmService->getInfo($anchor_uid, $startTimeStr, $endTimeStr);
        if (empty($info)) $this->error('主播不存在');
        $this->success('获取主播信息成功', $info);
    }

    public function find_anchor()
    {
        $this->checkAuth('admin:live_film_anchor:select');
        $get = input();
        $liveFilmService = new \app\admin\service\LiveFilmPeriod();
        $total = $liveFilmService->getAnchorTotal($get);
        $page = $this->pageshow($total);
        $films = $liveFilmService->getAnchorList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $films);
        return $this->fetch();
    }

    public function anchor()
    {
        $this->checkAuth('admin:live_film_anchor:select');
        $anchorService = new \app\admin\service\Anchor();
        $get = input();
        $get['join_live_film'] = '1';
        $total = $anchorService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $anchorService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function add_anchor()
    {
        $this->checkAuth('admin:live_film_anchor:add');
        $anchor_uid = input('anchor_uid');
        if (empty($anchor_uid)) $this->error('请选择主播');
        $anchor = Db::name('anchor')->where(['user_id' => $anchor_uid, 'delete_time' => null])->find();
        if (empty($anchor)) $this->error('请先将用户设置成主播');
        if ($anchor['join_live_film'] == '1') $this->error('主播已有电影直播权限');
        $num = Db::name('anchor')->where(['user_id' => $anchor_uid, 'delete_time' => null])->update([
            'join_live_film' => '1'
        ]);
        if (!$num) $this->error('设置失败');
        alog("live.film.set_anchor", "设置视频主播 USER_ID：".$anchor_uid);
        $this->success('设置成功');
    }

    public function del_anchor()
    {
        $this->checkAuth('admin:live_film_anchor:delete');
        $ids = get_request_ids('user_id');
        if (empty($ids)) $this->error('请选择主播');
        $num = Db::name('anchor')->where(['delete_time' => null])->whereIn('user_id', $ids)->update([
            'join_live_film' => '0'
        ]);
        if (!$num) $this->error('取消失败');
        alog("live.film.cancel_anchor", "取消视频主播 USER_ID：".implode(",", $ids));
        $this->success('取消成功,共计取消了' . $num . '主播的电影直播权限');
    }

    public function del()
    {
        $this->checkAuth('admin:live_film:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择影片');
        $liveFilm = new \app\admin\service\LiveFilm();
        $num = $liveFilm->delete($ids);
        if (!$num) $this->error($liveFilm->getError());
        alog("live.film.del", "删除直播视频 ID：".implode(",", $ids));
        $this->success('删除成功，共计删除' . $num . '部影片');
    }

}
