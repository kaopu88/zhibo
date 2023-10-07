<?php

namespace app\agent\controller;

use bxkj_common\YunBo;
use bxkj_common\CoreSdk;
use think\Db;
use think\Request;
use bxkj_common\RedisClient;

class Live extends Controller
{
    /**
     * 直播管理
     *
     */
    public function index()
    {
        $liveService = new \app\agent\service\Live();
        $get = input();
        $total = $liveService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $liveService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        return $this->fetch();
    }

    public function delete(Request $request)
    {
        $params = $request->param();
        $liveService = new \app\agent\service\Live();
        $has = $liveService->getOne(['id' => $params['room_id'], 'agent_id' => AGENT_ID]);
        if (empty($has)) $this->error('直播间不存在');
        $CoreSdk = new CoreSdk();
        $rs = $CoreSdk->post('live/superCloseRoom', ['room_id' => $params['room_id'], 'msg' => config('app.agent_setting.agent_name') . '超管关播~']);
        alog("live.live.del", "关闭直播间 room_id：" . $params['room_id']);
        $this->success('关播成功');
    }

    /**
     * 播放
     * @return mixed
     */
    public function tcplayer(Request $request)
    {
        $params = $request->param();
        $info = Db::name('live')->field('title, room_model, id, pull, cover_url, stream')->where(['id' => $params['id'], 'agent_id' => AGENT_ID])->find();
        if (empty($info)) $this->error('房间不存在');
        switch ($info['room_model']) {
            case 0:
                $info['pull'] = $this->getLivePullUrl($info['stream']);
                $info['ext'] = 'flv';
                break;
            case 1:
                //$this->parseMovieUrl($info);
                $info['ext'] = 'mp4';
                break;
            case 2:
                $this->parseMovieUrl($info);
                break;
        }
        $this->assign('live_info', $info);
        return $this->fetch();
    }

    protected function parseMovieUrl(&$room)
    {
        $timeline = Db::name('live_film_timeline')->where('room_id', $room['id'])->find();
        $film_info = Db::name('live_film')->where('id', $timeline['film_id'])->find();
        if (!empty($film_info['video_url'])) {
            $room['pull'] = $film_info['video_url'];
            $room['ext'] = $film_info['play'];
        } else if (!empty($film_info['third_url'])) {
            $third_info = YunBo::getVideo($film_info['third_url']);
            if ($third_info && !is_error($third_info)) {
                if ($third_info['play'] == 'mp4' || $third_info['play'] == 'hls') {
                    $room['pull'] = $third_info['src'];
                    $room['ext'] = $third_info['play'] == 'mp4' ?: 'm3u8';
                }
            }
        }
        $room['w'] = '986';
        $room['h'] = '300';
    }

    protected function getLivePullUrl($stream)
    {
        $live_config = config('app.live_setting');

        if (0 === strcasecmp($live_config['platform'], 'tencent')) {
            return sprintf("http://%s/live/%s.flv", $live_config['platform_config']['pull'], $stream);
        } else {
            return sprintf("http://%s/%s/%s.flv", $live_config['platform_config']['pull'], $live_config['platform_config']['live_space_name'], $stream);
        }
    }
}