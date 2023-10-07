<?php

namespace app\admin\controller;


use bxkj_common\YunBo;
use bxkj_common\CoreSdk;
use think\Db;
use think\Request;
use bxkj_common\RedisClient;

class Live extends Controller
{

    public function home()
    {
        return $this->fetch();
    }

    /**
     * 直播管理
     *
     */
    public function index()
    {
        $this->checkAuth('admin:live:select');
        $liveService = new \app\admin\service\Live();
        $get = input();
        $total = $liveService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $liveService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        return $this->fetch();
    }

    /**
     * 直播间增加机器人
     * @param Request $request
     * @throws \bxkj_module\exception\ApiException
     */
    public function addRobot(Request $request)
    {
        $this->checkAuth('admin:live:select');
        $params = $request->param();
        $CoreSdk = new CoreSdk();
        $rs = $CoreSdk->post('Zombie/addRobot', ['room_id' => $params['room_id'], 'count' => $params['num']]);
        if ($rs === false) return $this->error($CoreSdk->getError());
        alog("live.live.add_robot", "直播间 room_id：".$params['room_id']." 增加 ".$params['num']." 个机器人");
        $this->success('添加成功');
    }

    /**
     * @param Request $request
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function addTopSort(Request $request)
    {
        $this->checkAuth('admin:live:select');
        $params = $request->param();
        $num = Db::name('live')->where(['id' => $params['room_id']])->update(['sort'=>$params['sort']]);
        if($num === false){
            $this->error('编辑失败');
        }
        alog("live.live.edit", "编辑直播间 room_id：".$params['room_id']);
        $this->success('设置成功', $num);
    }

    //置顶
    public function change_top()
    {
        $this->checkAuth('admin:live:top');
        $ids = get_request_ids();
        $redis = RedisClient::getInstance();
        if (empty($ids)) $this->error('请选择直播间');
        $status = input('top');
        if (!in_array($status, ['0', '1'])) $this->error('传值不正确');
        $num = Db::name('live')->whereIn('id', $ids)->update(['top_status' => $status]);
        if (!$num) $this->error('置顶切换失败');
        $room = Db::name('live')->where('id', $ids[0])->find();
        $tops = $redis->get('cache:hotTop');
        if(!empty($tops)){
            $tops = explode(',', $tops);
        }else{
            $tops=[];
        }
        if($status==0){
            if(!empty($tops)) $tops=array_diff($tops, [$room['user_id']]);
        }else{
            array_push($tops,$room['user_id']);
            $tops=array_unique($tops);
        }
        $tops = trim(implode(',', $tops),',');
        $redis->set('cache:hotTop',$tops);
        alog("live.live.edit", "编辑直播间 room_id：".implode(",", $ids)."<br>切换置顶状态：".($status == 1 ? "置顶" : "普通"));
        $this->success('置顶切换成功');
    }

    /**
     * 播放
     * @return mixed
     */
    public function tcplayer(Request $request)
    {
        $params = $request->param();
        $info = Db::name('live')->field('title, room_model, id, pull, cover_url, stream')->where('id', $params['id'])->find();
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

    //下热门
    public function change_hot()
    {
        $this->checkAuth('admin:live:hot');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择直播间');
        $status = input('hot');
        if (!in_array($status, ['0', '1'])) $this->error('传值不正确');
        $num = Db::name('live')->whereIn('id', $ids)->update(['hot_status' => $status]);
        if (!$num) $this->error('热门切换失败');
        alog("live.live.edit", "编辑直播间 room_id：".implode(",", $ids)."<br>切换热门状态：".($status == 1 ? "热门" : "普通"));
        $this->success('热门切换成功');
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


    public function delete(Request $request)
    {
        $this->checkAuth('admin:live:select');
        $params = $request->param();
        $CoreSdk = new CoreSdk();
        $rs = $CoreSdk->post('live/superCloseRoom', ['room_id' => $params['room_id'], 'msg' => '超管关播~']);
        alog("live.live.del", "关闭直播间 room_id：".$params['room_id']);
        $this->success('关播成功');
    }

    /**
     * 添加视频直播
     * @param Request $request
     */
    public function add(Request $request)
    {
        $params = $request->param();
        $redis = RedisClient::getInstance();
        if ($request->isPost()) {
            $this->checkAuth('admin:live:select');
            $userId = (int)$params['user_id'];
            $userService = new \app\admin\service\User();
            $user = $userService->getInfo($userId);
            if (empty($user)) $this->error('用户不存在');
            if (!empty($params['type']) && empty($params['type_val'])) $this->error('请填写价格或密码');
            if (!is_url($params['pull'])) $this->error('请输入正确的播放地址');
            if (empty($params['cover_url'])) $this->error('请上传封面图');
            $liveService = new \app\admin\service\Live();
            $has = $liveService->getOne(['user_id' => $userId]);
            if (!empty($has))$this->error('该用户已经在直播中');
            $id = $liveService->addLive($params, $user);
            if ($id) {
                $CoreSdk = new CoreSdk();
                $redis->sadd( 'BG_LIVE:Living', $userId);
                $rs = $CoreSdk->post('live/backgroundCreate', ['room_id' => $id, 'msg' => '超管开播~']);
                alog("live.live.add", "新增直播间 room_id：".$id);
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $key = 'parent';
            $res = $redis->get('BG_NAV:LIVE:' . $key);
            if (empty($res)) {
                $res = Db::name('live_channel')->where(['parent_id' => 0])->field('id, sub_channel, icon, name, description')->order('sort_order')->select();
                $res = json_encode($res);
            }
            $channel = json_decode($res, true);
            $this->assign('channel', $channel);
            return $this->fetch();
        }
    }

    public function edit(Request $request)
    {
        $this->checkAuth('admin:live:select');
        $params = $request->param();
        $id = $params['id'];
        if (empty($id)) return $this->error('非法操作');
        $liveService = new \app\admin\service\Live();
        $redis = RedisClient::getInstance();
        if ($request->isPost()) {
            $userId = $params['user_id'];
            $userService = new \app\admin\service\User();
            $user = $userService->getInfo($userId);
            if (empty($user)) $this->error('用户不存在');
            if (!empty($params['type']) && empty($params['type_val'])) $this->error('请填写价格或密码');
            if (!is_url($params['pull'])) $this->error('请输入正确的播放地址');
            if (empty($params['cover_url'])) $this->error('请上传封面图');

            $id = $liveService->editLive($params, $user);
            if ($id) {
                alog("live.live.edit", "编辑直播间 room_id：".$id);
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $live = $liveService->getOne(['id' => $id]);
            $key = 'parent';
            $res = $redis->get('BG_NAV:LIVE:' . $key);
            if (empty($res)) {
                $res = Db::name('live_channel')->where(['parent_id' => 0])->field('id, sub_channel, icon, name, description')->order('sort_order')->select();
                $res = json_encode($res);
            }
            $channel = json_decode($res, true);
            $this->assign('channel', $channel);
            $this->assign('_info', $live);
            if (empty($live)) return $this->error('直播不存在');
            return $this->fetch();
        }
    }

    public function robot_delete(Request $request)
    {
        $this->checkAuth('admin:live:select');
        $redis = RedisClient::getInstance();
        if (!$redis->exists('supplies')) return $this->success('暂时不需修复');
        $redis->del('suppliesPage');
        $res = $redis->del('supplies');
        if ($res) return $this->success('修复成功');
        return $this->error('修复失败');
    }
}