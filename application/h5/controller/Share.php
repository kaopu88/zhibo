<?php

namespace app\h5\controller;

use bxkj_common\CoreSdk;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Session;
use think\Request;
use bxkj_module\service\User;

class Share extends Controller
{
    protected static $guardKey = 'BG_GUARD:';
    protected static $guardAvatar = 'https://static.cnibx.cn/1.jpg';
    protected static $giftPrefix = 'BG_GIFT:', $giftKey = 'gift', $giftIncr = 'incr';
    // 直播间礼物类型
    protected static $gift_category = 0;

    protected function getLivePullUrl($stream)
    {
        $live_config = config('app.live_setting');
        if (0 === strcasecmp($live_config['platform'], 'tencent')) {
//            return sprintf("http://%s/live/%s.m3u8", $live_config['tencent_live']['pull'], $stream);
            return sprintf("http://%s/live/%s.m3u8", $live_config['platform_config']['pull'], $stream);
        } else {
            return sprintf("http://%s/%s/%s.m3u8", $live_config['qiniu_live']['pull'], $live_config['qiniu_live']['live_space_name'], $stream);
//            return sprintf("http://%s/%s/%s.m3u8", $live_config['qiniu_live']['pull'], $live_config['qiniu_live']['live_space_name'], $stream);
        }
    }

    //获取主播排名第一的守护者头像
    protected function guardAvatar($user_id)
    {
        $default    = ['guard_avatar' => self::$guardAvatar, 'guard_uid' => ''];
        $redis      = RedisClient::getInstance();
        $guardCount = $redis->zcard(self::$guardKey . $user_id); //当前主播的守护量
        if (!empty($guardCount)) {
            $redis->zremrangebyscore(self::$guardKey . $user_id, 1, time()); //移除过期的
            $top = $redis->zrevrange(self::$guardKey . $user_id, 0, 0); //获取第一个用户id
            if (!empty($top)) {
                $topUser = (new CoreSdk())->getUsers($top[0]);
                if (!empty($topUser[0])) {
                    if (!empty($topUser[0]['avatar'])) {
                        $default['guard_avatar'] = $topUser[0]['avatar'];
                        $default['guard_uid']    = $topUser[0]['user_id'];
                    }
                }
            }
        }
        return $default;
    }

    //ajax直播间观众信息
    public function getUser(Request $request)
    {
        $user = $request->param();
        $core = new CoreSdk();
        $res  = $core->post('user/get_user', ['user_id' => $user['user_id']]);
        if (is_error($res)) return json_error('错误');
        return json_success($res);
    }

    //守护列表
    public function getGuard(Request $request)
    {
        $anchor_id   = $request->post();
        $users       = [];
        $redis       = RedisClient::getInstance();
        $guardTotals = $redis->zrevrange(static::$guardKey . $anchor_id['user_id'], 0, -1, true);
        if (empty($guardTotals)) return json_error('为空');
        $coreSdk = new CoreSdk();
        $lists   = $coreSdk->getUsers(array_keys($guardTotals));
        $now     = time();
        foreach ($lists as $key => $val) {
            $users[] = [
                'nickname'    => $val['nickname'],
                'avatar'      => $val['avatar'],
                'sign'        => $val['sign'],
                'user_id'     => $val['user_id'],
                'level'       => $val['level'],
                'vip_status'  => $val['vip_expire'] < $now ? 0 : 1,
                'is_creation' => $val['is_creation'],
                'verified'    => $val['verified'],
                'gender'      => $val['gender'],
            ];
        }
        return json_success($users);
    }

    //直播分享
    public function live(Request $request)
    {
        $params    = $request->get();
        $live_info = Db::name('live')->where('id', $params['id'])->find();
        if (!empty($params['sk'])) {
            Db::name('share_record')->where('share_key', $params['sk'])->setInc('pv', 1);
        }
        if (!empty($live_info)) {
            $live_info['pull_url'] = $this->getLivePullUrl($live_info['stream']);
            //    $live_info['pull_url'] = 'https://static.cnibx.cn/zhiboshiping.mp4';
            $audienceList = (new CoreSdk())->post('zombie/getAudienceList', ['room_id' => $params['id']]);
            $live_bean    = Db::name('bean')->where('user_id', $live_info['user_id'])->find();
            $user_info    = [
                'avatar'    => $live_info['avatar'],
                'nickname'  => $live_info['nickname'],
                'user_id'   => $live_info['user_id'],
                'cover_url' => $live_info['cover_url'],
                'live_bean' => $live_bean['bean'],
            ];
            $this->assign('live_info', $live_info);
            $this->assign('user_info', $user_info);
            $this->assign('audience', $audienceList);
        } else {
            $his_info             = Db::name('live_history')->field('avatar, nickname, cover, start_time, end_time')->where('room_id', $params['id'])->find();
            $his_info['duration'] = time_str($his_info['end_time'] - $his_info['start_time']);
            $user_info            = [
                'avatar'   => $his_info['avatar'],
                'nickname' => $his_info['nickname']
            ];
            $this->assign('now', time());
            $this->assign('his_live', $his_info);
            $this->assign('user_info', $user_info);
            $this->view->config('default_filter', '')->assign('video_config', '');
        }
        $moreFilm = Db::name('video')->alias('v')
            ->join('__USER__ u', 'v.user_id=u.user_id')
            ->field('cover_url, u.nickname, u.avatar, v.user_id')
            ->order('v.score desc')
            ->limit(10)
            ->select();
        foreach ($moreFilm as &$value) {
            $value['avatar'] = img_url($value['avatar'], '', 'avatar');
        }
        $down_url = H5_URL . '/download.html';
        $this->assign('down_url', $down_url);
        $shareName = !empty($live_info) ? $live_info['nickname'] . '的直播间' : '';
        $Live      = new Live();
        $dynamic   = $Live->getLiveDynamic();
        $livelist  = $Live->getLiveRoom();
        if (!empty($dynamic->getdata()['data'])) {
            $this->assign('dynamic', $dynamic->getdata()['data']);
        }
        if (!empty($livelist->getdata()['data'])) {
            $this->assign('livelist', $livelist->getdata()['data']);
        }
        $this->assign('livelist', $livelist->getdata()['data']);
        $this->assign('share_name', $this->getSeoTitle($shareName));
        $this->view->engine->layout('share/share');
        $this->assign('h5_image', config('upload.image_defaults'));
        $this->assign('more', $moreFilm);
        $this->assign('type', __FUNCTION__);
        //新加的
        Session::get('access_token');
        $now = time();
        $this->assign('now', $now);
        $para       = [
            "room_id"      => $params['id'],
            "access_token" => $_SESSION['think']['access_token'],
        ];
        $httpClient = new HttpClient();
        //$result     = $httpClient->post(API_URL . ".php?s=Room.enterRoom", $para)->getData('json');
        $ws         = get_live_config();
        @list($protocol, $link) = explode(':', $ws['message_server']['chat_server']);
        $link = $_SERVER['SERVER_NAME'];
        $this->assign('ws', [
            'protocol'   => 'ws',
            'url'        => trim($link, '/'),
            'port'       => $ws['chat_server_port'],
            'packet'     => '0x',
            'heart_time' => 20,
            'heart_msg'  => [
                'mod'  => 'Live',
                'act'  => 'heart',
                'sign' => ["token" => $_SESSION['think']['access_token'], "user_id" => $_SESSION['think']['user_id']],
            ],
        ]);
        $msg = [
            'mod'   => 'Live',
            'act'   => 'enter',
            'args'  => ['room_id' => $params['id'], "user_id" => $_SESSION['think']['user_id']],
            "api_v" => "v2",
            'sign'  => ["token" => $_SESSION['think']['access_token'], "user_id" => $_SESSION['think']['user_id']],
        ];
        //     var msg1 = '{"mod":"Live","act":"sendMsg","api_v":"v2","args":{"room_id":"12273","type":"1","content":"发发发"},"sign":{"token":"44d75e7f7d6f5e8eab199cdf3395341b5751b081","user_id":"10004103"}}';
        $msg1 = [
            'mod'   => 'Live',
            'act'   => 'sendMsg',
            "api_v" => "v2",
            'args'  => ['mod' => 'Live', 'room_id' => $params['id'], "user_id" => $_SESSION['think']['user_id'], 'type' => '1', "content" => "22233221"],
            'sign'  => ["token" => $_SESSION['think']['access_token'], "user_id" => $_SESSION['think']['user_id']],
        ];
        //     {"mod":"Live","act":"sendGift","api_v":"v2","args":{"room_id":"12275","pk_id":"","gift_id":"98","type":"gift","gift_amount":"1"},"sign":{"token":"593ff227ed848c9faac560ce69dcc824ed774919","user_id":"10003937"}}
        $msg2 = [
            'mod'   => 'Live',
            'act'   => 'sendGift',
            "api_v" => "v2",
            'args'  => ['room_id' => $params['id'], "pk_id" => "", "gift_id" => "98", "type" => "gift", "gift_amount" => 1, "user_id" => $_SESSION['think']['user_id']],
            'sign'  => ["token" => $_SESSION['think']['access_token'], "user_id" => $_SESSION['think']['user_id']],
        ];
        $info = $live_info;
        switch ($info['room_model']) {
            case 0:
                $info['pull'] = $this->getLivePullUrl($info['stream']);
                $info['ext']  = 'flv';
                break;
            case 1:
                $info['ext'] = 'mp4';
                break;
            case 2:
                $this->parseMovieUrl($info);
                break;
        }
        $this->assign('live_info', $info);
        $this->assign('msg', $msg);
        $this->assign('msg1', $msg1);
        $this->assign('msg2', $msg2);
        $this->assign('user_id', $_SESSION['think']['user_id']);
        $this->assign('token', $_SESSION['think']['access_token']);
        return $this->fetch('live');
    }

    protected function getSeoTitle($title = '')
    {
        $limiter        = !empty($title) ? '—' : '';
        $product_name   = config('app.product_setting.name');
        $product_slogan = config('app.product_setting.slogan');
        return sprintf('%s%s%s, %s', $title, $limiter, $product_name, $product_slogan);
    }

    //电影视频分享
    public function film(Request $request)
    {
        $params    = $request->get();
        $videoInfo = Db::name('video')->field('describe,user_id,video_url,zan_sum,zan_sum2,comment_sum,play_sum,share_sum,cover_url')->where('id', $params['id'])->find();
        if (!empty($params['sk'])) {
            Db::name('share_record')->where('share_key', $params['sk'])->setInc('pv', 1);
        }
        if (!empty($videoInfo)) {
            $userService = new User();
            $userInfo    = $userService->getUser(intval($videoInfo['user_id']));
            $this->formatData($videoInfo, ['zan_sum', 'comment_sum']);
            if (!empty($videoInfo['music_id'])) {
                $music = Db::name('music')->field('title,image')->where(array('id' => $videoInfo['music_id'], 'status' => 1))->find();
                if (!empty($music)) {
                    $videoInfo['music'] = $music;
                }
            }
            $this->assign('video_info', $videoInfo);
            $this->assign('user_info', $userInfo);
        } else {
            $video_config = [];
        }
        $moreFilm = Db::name('video')->alias('v')
            ->join('__USER__ u', 'v.user_id=u.user_id')
            ->field('cover_url, u.nickname, u.avatar, v.user_id')
            ->order('v.score desc')
            ->limit(10)
            ->select();
        foreach ($moreFilm as &$value) {
            $value['avatar'] = img_url($value['avatar'], '', 'avatar');
        }
        $this->assign('h5_image', config('upload.image_defaults'));
        $down_url = H5_URL . '/download.html';
        $this->assign('down_url', $down_url);
        $shareName = !empty($videoInfo) ? $videoInfo['describe'] : '';
        $this->assign('share_name', $this->getSeoTitle($shareName));
        $this->view->engine->layout('share/share');
        $this->assign('more', $moreFilm);
        $this->assign('type', __FUNCTION__);
        return $this->fetch('film');
    }

    //用户分享
    public function user(Request $request)
    {
        $params      = $request->param();
        $userService = new User();
        $user_info   = $userService->getUser(intval($params['id']));
        if (!empty($params['sk'])) {
            Db::name('share_record')->where('share_key', $params['sk'])->setInc('pv', 1);
        }
        if (!empty($user_info)) {
            empty($user_info['sign']) && $user_info['sign'] = '这个家伙太懒了，什么也没留下。';
            $user_videos = Db::name('video')->where(['user_id' => $params['id']])->field('cover_url, zan_sum')
                ->order('id desc')
                ->limit(21)
                ->select();
            $user_likes  = Db::name('video_like')->alias('a')
                ->join('video b', 'a.target_id=b.id')
                ->field('b.cover_url, b.zan_sum')
                ->where(['a.user_id' => $params['id']])
                ->order('a.create_time desc')
                ->limit(21)
                ->select();
            $this->assign('user_videos', $user_videos);
            $this->assign('user_likes', $user_likes);
            $this->assign('user_info', $user_info);
        }
        $down_url = H5_URL . '/download.html';
        $this->assign('down_url', $down_url);
        $this->view->engine->layout('share/share');
        $shareName = !empty($user_info) ? '用户-' . $user_info['nickname'] : '';
        $this->assign('share_name', $this->getSeoTitle($shareName));
        $this->assign('type', __FUNCTION__);
        return $this->fetch('user');
    }

    protected function format_date($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000'  => '个月',
            '604800'   => '星期',
            '86400'    => '天',
            '3600'     => '小时',
            '60'       => '分钟',
            '1'        => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }

    public function getAudienceList(Request $request)
    {
        $room         = $request->post();
        $audienceList = (new CoreSdk())->post('zombie/getAudienceList', ['room_id' => $room['room_id']]);
        return json_success($audienceList);
    }

    //格式化数字
    protected function formatData(&$data, $field = [])
    {
        if (is_array($data) && !empty($field) && is_array($field)) {
            foreach ($field as $key => $val) {
                key_exists($val, $data) && $data[$val] = $this->formatData($data[$val]);
            }
        } else {
            if ($data >= 100000000) {
                $real = sprintf("%.3f", $data / 100000000);
                $data = $real . 'e';
            } else if ($data >= 10000) {
                $real = sprintf("%.1f", $data / 10000);
                $data = $real . 'w';
            }
        }
        return (string)$data;
    }

    /**
     * 直播间获取礼品
     * @param Request $request
     * @return mixed
     */
    public function getGift()
    {
        Session::get('access_token');
        $httpClient = new HttpClient();
        $para       = [
            "access_token" => $_SESSION['think']['access_token'],
        ];
        $result     = $httpClient->post(API_URL . ".php?s=Gift.getLiveGift&api_v=v2", $para)->getData('json')['data'];
        return json_success($result);
    }

    //登录接口
    public function login(Request $request)
    {
        $params   = $request->param();
        $validate = new \app\h5\validate\User();
        $result   = $validate->scene('login')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $httpClient = new HttpClient();
        $para       = [
            "api_v"   => 'v2',
            'v'       => 1,
            'meid'    => mt_rand(11111111, 99999999),
            'os_name' => 'web'
        ];
        $result     = $httpClient->post(API_URL . ".php?s=Common.appinit", $para)->getData('json')['data'];
        if (empty($result)) {
            return $this->jsonError("登录失败");
        }
        $paras = [
            'username'     => $params['username'],
            'password'     => $params['password'],
            'access_token' => $result['access_token'],
        ];
        $rest = $httpClient->post(API_URL . ".php?s=Account.login", $paras)->getData('json')['data'];
        if (empty($rest)) return $this->jsonError("登录失败");
        Session::set('user_id', $rest['user_id']);
        Session::set('access_token', $result['access_token']);
        \session('access_token', $result['access_token']);
        return $this->successr($paras, '登录成功');
    }

    //成功返回 兼容返回
    protected function successr($data, $msg = '')
    {
        header('Access-Control-Allow-Origin: *');
        return $this->jsonSuccess($data, $msg);
    }

    protected function jsonSuccess($data, $msg = '')
    {
        return json(array(
            'code' => 0,
            'data' => $data,
            'msg'  => $msg
        ));
    }

    //错误返回
    protected function jsonError($msg, $code = 1, $data = null)
    {
        $message = '系统繁忙~';
        if (is_string($msg)) {
            $message = $msg;
        } else if (is_error($msg)) {
            $message = $msg->getMessage();
            $code    = $msg->getStatus();
        }
        $obj = array(
            'code' => $code,
            'msg'  => $message
        );
        if (isset($data)) $obj['data'] = $data;
        return json($obj);
    }
}