<?php

namespace app\api\controller;

use app\common\controller\Controller;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\Socket;
use bxkj_module\service\User;
use think\Db;

class Share extends Controller
{
    protected $productName;
    protected $productSlogan;

    public function __construct()
    {
        parent::__construct();
        $this->productName   = APP_NAME;
        $this->productSlogan = config('app.product_setting.slogan');
    }

    /*
     * 分享获取
     */
    //获取分享参数
    public function getParams()
    {
        $params = request()->param();
        $type   = $params['type'];
        $method = 'get' . parse_name($type, 1, true) . 'Data';

//        if (!method_exists($this, $method)) return $this->jsonError('分享类型不正确');
        $shareKey  = sha1(uniqid() . get_ucode());
        $shareData = call_user_func_array(array($this, $method), [$params, $shareKey]);
        if (is_error($shareData)) return $this->jsonError($shareData);
        if (!$shareData) return $this->jsonError('分享参数错误');
        $shareData['share_key'] = $shareKey;
        $data                   = [
            'share_key'   => $shareKey,
            'channel'     => '',
            'share_uid'   => defined('USERID') ? USERID : '0',
            'type'        => $type,
            'create_time' => time(),
            'client_ip'   => get_client_ip(),
            'meid'        => APP_MEID,
            'status'      => '0'
        ];
        if (isset($shareData['extend'])) {
            $data = array_merge($data, $shareData['extend']);
            unset($shareData['extend']);
        }
        $data['share_params'] = json_encode($shareData);
        Db::name('share_record')->insert($data);
        if (isset($shareData['share_sum'])) unset($shareData['share_sum']);
        return $this->success($shareData, '获取分享数据成功');
    }

    //分享视频
    protected function getFilmData($params, $shareKey)
    {
        $query['sk'] = $shareKey;
        $query['id'] = (int)$params['target_id'];
        $url         = H5_URL . "/share/film?" . http_build_query($query);
        $film        = Db::name('video')->where(['id' => (int)$params['target_id']])->field('describe, cover_url, share_sum')->find();
        if (empty($film)) return make_error('未找到此视频');
        $title = $film['describe'] ? short($film['describe'], 30) : '我分享了一个视频';
        return array('title' => $title, 'descr' => "打开{$this->productName}查看更多好玩的视频吧", 'url' => $url, 'share_sum' => $film['share_sum'], 'thumb' => $film['cover_url'], 'extend' => array('item_id' => $params['target_id']));
    }

    //分享直播
    protected function getLiveData($params, $shareKey)
    {
        $query['sk'] = $shareKey;
        $query['id'] = $params['room_id'];
        $url         = H5_URL . "/share/live?" . http_build_query($query);
        $live        = Db::name('live')->where(['id' => (int)$params['room_id'], 'status' => 1])->field('nickname, cover_url')->find();
        if (empty($live)) return make_error('主播已关播');
        $title = "我在{$this->productName}直播, 期待你的到来~";
        return array('title' => $title, 'descr' => $this->productSlogan, 'url' => $url, 'thumb' => $live['cover_url'], 'extend' => array('item_id' => $params['room_id']));
    }

    //分享他人个人主页
    protected function getPersonalData($params, $shareKey)
    {
        $userModel = new User();
        $userId    = $params['user_id'];
        $user      = $userModel->getBasicInfo($userId, '1');
        if (empty($user)) return make_error('用户不存在');
        $query['sk'] = $shareKey;
        $query['id'] = $userId;
        $url         = H5_URL . "/share/user?" . http_build_query($query);
        return array(
            'title'  => "快来加入{$this->productName}！为{$user['nickname']}疯狂打Call~",
            'descr'  => $user['sign'] ? short($user['sign'], 15) : '这个家伙太懒了，什么也没留下。',
            'url'    => $url,
            'thumb'  => img_url($user['avatar'], '640_640', 'avatar'),
            'extend' => array('item_id' => $params['user_id'])
        );
    }

    //获取邀请好友的分享参数
    protected function getInviteData($params, $shareKey)
    {
        $query['sk']      = $shareKey;
        $query['user_id'] = defined('USERID') ? USERID : '0';
        $user             = $this->user;
        $avatar           = $user ? $user['avatar'] : '';
        if (!empty($params['anchor'])) {
            $params['anchor'] = preg_replace('/\D/', '', $params['anchor']);
            $query['anchor']  = $params['anchor'];
            $anchorInfo       = Db::name('user')->where(['user_id' => $query['anchor']])->field('user_id,avatar')->find();
            $avatar           = $anchorInfo ? $anchorInfo['avatar'] : $avatar;
        }
        $url   = H5_URL . '/invite/index?' . http_build_query($query);
        $title = "{$this->productSlogan}，我在{$this->productName}等你";
        return array('title' => $title, 'descr' => config('app.product_setting.descr'), 'url' => $url, 'thumb' => $avatar, 'extend' => array('item_id' => $query['user_id']));
    }

    /*
     * 分享结果处理
     */
    //分享结果
    public function shareResult()
    {
        $res     = (object)[];
        $params  = request()->param();
        $channel = $params['share_channel'];
        $status  = $params['status'];
        $sk      = $params['share_key'];
        if (empty($sk)) return $this->jsonError('分享标识符不能为空');
        if (!enum_in($channel, 'share_channels')) return $this->jsonError('分享渠道不正确');
        if (!in_array($status, array('1', '2'))) return $this->jsonError('状态码错误');
        $where     = [
            'share_key' => $sk,
            'status'    => '0',
        ];
        $shareInfo = Db::name('share_record')->where($where)->find();
        if ($shareInfo) {
            $data['status']      = $status;
            $data['channel']     = $channel ? $channel : '';
            $data['result_time'] = time();
            $num                 = Db::name('share_record')->where(['id' => $shareInfo['id']])->update($data);
            if (!$num) return $this->jsonError('上传结果失败');
            $method = 'handler' . parse_name($shareInfo['type'], 1, true) . 'Result';
            if (method_exists($this, $method)) {
                $tmp = array_merge($shareInfo, $data);
                $res = call_user_func_array(array($this, $method), [$tmp]);
            }
        }
        finish_task($shareInfo['share_uid'],'shareDynamic',1,0);
        return $this->success($res, '上传成功');
    }

    //电影分享结果
    protected function handlerFilmResult($shareInfo)
    {
        $points = config('app.accumulate_points');
        $where = [
            ['status', '=', '1'],
            ['share_uid', '=', USERID],
            ['create_time', '>', strtotime(date("Y-m-d"), time())],
        ];
        $count = Db::name('share_record')->where($where)->count();
        if ($points['share_video']['condition'] >= $count) {
            $user = new User();
            $user->updateData(USERID, [
                'exp' => '+' . $points['share_video']['exp']
            ]);
        }
        $share_params  = json_decode($shareInfo['share_params'], true);
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.share_video', [
            'user_id'  => USERID,
            'video_id' => $shareInfo['item_id']
        ]);
        return ['share_sum' => $share_params['share_sum'] + 1];
    }

    //直播分享结果
    protected function handlerLiveResult($shareInfo)
    {
        $points = config('app.accumulate_points');
        $where = [
            ['type', '=', 'live'],
            ['share_uid', '=', USERID],
            ['create_time', '>', strtotime(date("Y-m-d"), time())],
        ];
        $count = Db::name('share_record')->where($where)->count();
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.share_live', [
            'user_id' => USERID,
            'room_id' => $shareInfo['item_id']
        ]);
        if ($points['share_live']['condition'] >= $count) {
            $user = new User();
            $user->updateData(USERID, [
                'exp' => '+' . $points['share_video']['exp']
            ]);
        }
        return true;
    }

    //个人主页分享结果
    protected function handlerPersonalResult($shareInfo)
    {
        $points = config('app.accumulate_points');
        $where = [
            ['type', '=', 'personal'],
            ['share_uid', '=', USERID],
            ['create_time', '>', strtotime(date("Y-m-d"), time())],
        ];
        $count = Db::name('share_record')->where($where)->count();
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.share_user', [
            'user_id' => USERID,
            'to_uid'  => $shareInfo['item_id']
        ]);
        if ($points['share_personal']['condition'] >= $count) {
            $user = new User();
            $user->updateData(USERID, [
                'exp' => '+' . $points['share_video']['exp']
            ]);
        }
    }

    //分享动态
    protected function getDynamicData($params, $shareKey)
    {
        $query['sk'] = $shareKey;
        $query['id'] = (int)$params['target_id'];
        $url  = H5_URL . '/download.html';
        $film = Db::name('friend_circle_message')->where(['id' => (int)$params['target_id']])->field('dynamic_title, cover_url,share_sum')->find();
        if (empty($film)) return make_error('未找到此动态');
        $title = $film['describe'] ? short($film['describe'], 30) : '我分享了一个动态';
        return array('title' => $title, 'descr' => "打开{$this->productName}查看更多好玩的动态吧", 'url' => $url, 'share_sum' => $film['share_sum'], 'thumb' => $film['cover_url'], 'extend' => array('item_id' => $params['target_id']));
    }

    //分享圈子
    protected function getCircleData($params, $shareKey)
    {
        $query['sk'] = $shareKey;
        $query['id'] = (int)$params['target_id'];
        $url  = H5_URL . '/download.html';
        $film = Db::name('friend_circle_circle')->where(['circle_id' => (int)$params['target_id']])->field('circle_name, circle_background_img,share_sum')->find();
        if (empty($film)) return make_error('未找到此圈子');
        $title = $film['describe'] ? short($film['circle_name'], 30) : '我分享了一个圈子';
        return array('title' => $title, 'descr' => "打开{$this->productName}查看更多好玩的圈子吧", 'url' => $url, 'share_sum' => $film['share_sum'], 'thumb' => $film['circle_background_img'], 'extend' => array('item_id' => $params['target_id']));
    }

    //分享话题
    protected function getTopicData($params, $shareKey)
    {
        $query['sk'] = $shareKey;
        $query['id'] = (int)$params['target_id'];
        $url  = H5_URL . '/download.html';
        $film = Db::name('friend_circle_topic')->where(['topic_id' => (int)$params['target_id']])->field('topic_name')->find();
        if (empty($film)) return make_error('未找到此圈话题');
        $title = $film['describe'] ? short($film['topic_name'], 30) : '我分享了一个话题';
        return array('title' => $title, 'descr' => "打开{$this->productName}查看更多好玩的话题吧", 'url' => $url, 'share_sum' => rand(100,999), 'thumb' =>'', 'extend' => array('item_id' => $params['target_id']));
    }

    //分享种草
    protected function getPlantingGrassData($params, $shareKey)
    {
        $query['sk'] = $shareKey;
        $query['id'] = (int)$params['target_id'];
        $url  = H5_URL . '/download.html';
        $film = Db::name('shop_recommend_message')->where(['id' => (int)$params['target_id']])->field('dynamic_title, cover_url,share_sum,picture')->find();
        if (empty($film)) return make_error('未找到此种草信息');
        $title = $film['describe'] ? short($film['describe'], 30) : '我分享了一个种草';
        if(!empty($film['picture'])) {
            $imgone = explode(',',$film['picture']);
        }else{
            $imgone = '';
        }
        $sharePic  = $film['cover_url']?$film['cover_url']:$imgone;
        return array('title' => $title, 'descr' => "打开{$this->productName}查看更多好玩的种草吧", 'url' => $url, 'share_sum' => $film['share_sum'], 'thumb' => $sharePic, 'extend' => array('item_id' => $params['target_id']));
    }

}