<?php

namespace app\api\controller;

use app\api\service\live\Zombie;
use app\common\behavior\ResponseSend;
use app\common\controller\UserController;
use app\common\service\DsSession;
use app\common\service\Validate;
use app\api\service\live\Channel;
use app\api\service\live\Close;
use app\api\service\live\Create;
use app\api\service\live\Enter;
use app\api\service\live\Guard;
use app\api\service\live\LinkMic;
use app\api\service\live\Lists;
use app\api\service\live\Manage;
use app\api\service\live\Payment;
use app\api\service\live\Pk as PkDomain;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use think\Db;
use app\api\service\music\MusicData;
use think\Request;

class Room extends UserController
{
    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
    }

    /**
     * 创建直播间
     * @desc 主播创建直播间
     * @return string
     *
     * 语聊需要多传的参数
     * @param voice_value  语聊上麦的人数
     * @param background_url 语聊背景图
     * @param room_model 4 和5新增
     */
    public function makeRoom()
    {
        $params = request()->param();
        $params['type'] = isset($params['type']) ? $params['type'] :0;
        if ($params['type'] && $params['type'] != 4) {
            if (empty($params['type_val'])) return $this->jsonError('直播类型值不能为空');
        }
        $live_config = config('app.live_setting');
        $rules_anchor = [];
        $params['room_model'] = isset($params['room_model']) ? $params['room_model'] : 0;

        $rules_anchor['live_status'] = ['rule' => 'isFalse', 'error_msg' => '暂无直播权限,请联系客服', 'status' => $live_config['validate_live_status']];

        $anchor = DsSession::get('user');
        $sensitive_config = get_sensitive_config('live');
        $anchor = copy_array($anchor, 'user_id, nickname, avatar, gender, verified, level, province_name, city_name, live_status, district_name');
        $params = array_merge($params, $anchor);
        $rules = [
            'verified' => ['rule' => 'isFalse', 'error_msg' => '您需要进行实名认证，才能继续直播', 'status' => $live_config['validate_verified'], 'error_code' => 1006],
            'title' => ['rule' => 'sensitive', 'error_msg' => 'sensitive:标题中含有敏感词{$sensitive},请修改后重新开播', 'status' => $sensitive_config['filter_on'], 'params' => $sensitive_config],
            'level' => ['rule' => 'egt:' . $live_config['validate_level_value'], 'error_msg' => 'egt:等级达到{$egt}级才能开播', 'status' => $live_config['validate_level']],
            'validate_banned' => ['rule' => 'exists:' . $live_config['validate_banned_value'], 'error_msg' => 'exists:您已被禁播{$exists}天,其间不能在此平台开播', 'status' => $live_config['validate_banned']],
        ];
        $rules = array_merge($rules, $rules_anchor);
        $validate = new Validate();
        $rs = $validate->check($params, $rules);
        //可能校验规则报错 先注释掉
        
        if ($rs) {
            $error = $validate->getError();
            $error = array_shift($error);
            return $this->jsonError($error['error_msg'], $error['error_code']);
        }
        $roomDomain = new Create();
        $serviceParams = $roomDomain->initializeService($params)
            ->addServiceRobot()
            ->addServiceTask()
            ->getServiceParams();
        
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->send('user.behavior.notice', [
            'anchor_id' => $anchor['user_id'],
            'room_id' => $serviceParams['room']['room_id']
        ]);

        return $this->success($serviceParams);
    }

    /**
     * 电影直播列表
     * @return array
     */
    public function filmLiveList()
    {
        $p = request()->param('p');
        $liveDomain = new Lists();
        $filmLive = $liveDomain->setFilmLive($p)->getLiveList();
        if (empty($filmLive)) return $this->success([]);
        $filmLive = $liveDomain->initializeLive($filmLive, 1);
        return $this->success($filmLive);
    }

    /**
     * pk直播列表
     * @return array
     */
    public function pkLiveList()
    {
        $p = request()->param('p');
        $pkDomain = new PkDomain();
        $pkLive = $pkDomain->getPkList($p);
        return $this->success($pkLive);
    }

    //直播间检查
    public function verifyRoom()
    {
        $room_id = request()->param('room_id');
        $Enter = new Enter();
        $res = $Enter->verifyClient($room_id);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success($res);
    }

    //进入直播间
    public function enterRoom()
    {
        $room_id = request()->param('room_id');
        $password = request()->param('password');
        $Enter = new Enter();
        $room = $Enter->getRoomOne($room_id);
        if (empty($room)) return $this->jsonError('直播已结束');
        if (USERID == $room['user_id']) return $this->jsonError('您不可以进入自已的直播间');
        $shopId = json_decode($this->redis->get("user:" . $room['user_id']), true);
        if ($room['type'] == 1) {
            $pss = $this->redis->zscore('BG_LIVE:' . $room_id . ':PWD', USERID);
            if (!$pss) {
                if (0 != strcmp($room['type_val'], $password)) return $this->jsonError('密码错误');
                $this->redis->zadd('BG_LIVE:' . $room_id . ':PWD', time(), USERID);
            }
        }
        $rs = $Enter->initializeClient($room)->getClientParams();
        ResponseSend::$dataType = $rs['room']['is_pk'] ? '1' : '0';
        $this->redis->set('BG_ROOM:enter:' . USERID, $room_id, 43200);
        $rs['room']['shop_type'] = $shopId['shop_id'] ? 1 : 0;
        return $this->success($rs);
    }

    //直播间观众集合
    public function onlineAudience()
    {
        $room_id = request()->param('room_id');
        $audienceArr = Zombie::getAudienceList($room_id);
        if (is_error($audienceArr) || empty($audienceArr)) return $this->success([]);
        return $this->success($audienceArr);
    }

    //web端直播间观众集合
    public function webOnlineAudience()
    {
        $room_id = request()->param('room_id');
        $audienceArr = Zombie::getAudienceList($room_id);
        if (is_error($audienceArr) || empty($audienceArr)) return $this->success([]);
        return $this->success($audienceArr);
    }

    /**
     * 关闭直播间
     * @desc 主播主动结束直播
     */
    public function closeRoom()
    {
        $room_id = request()->param('room_id');
        $coreSdk = new CoreSdk();
        $rs = $coreSdk->post('live/closeRoom', ['room_id' => $room_id]);
        if ($rs === false) return $this->jsonError($coreSdk->getError());
        return $this->success([]);
    }

    //开播成功后的确认
    public function ackLive()
    {
        $room_id = request()->param('room_id');
        $ack = request()->param('ack');
        $Create = new Create();
        if ($ack == 1) {
            $res = $Create->ackLive($room_id);
            $room_info = $Create->getRoomOne($room_id);
            $room_info['type'] != 1 && $Create->pushAnchorLiveMsg(USERID, $room_id); //确认开播后在通知关注的人
            if ($res) return $this->success(['status' => 1], '推流成功');
        }
        $coreSdk = new CoreSdk();
        $res = $coreSdk->post('live/closeRoom', ['room_id' => $room_id]);
        if ($res === false) return $this->jsonError($coreSdk->getError());
        return $this->success(['status' => 0], '推流断开,请重新开播');
    }

    //直播收益(主播端)
    public function liveIncome()
    {
        $room_id = request()->param('room_id');
        $Card = new Close();
        $res = $Card->facePlateByAnchor($room_id);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success($res);
    }

    //直播收益(看播端)
    public function clientLiveIncome()
    {
        $room_id = request()->param('room_id');
        $Card = new Close();
        $res = $Card->facePlateByClient($room_id);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success($res);
    }

    /**
     * 禁言
     * @desc 主播对直播间观众禁言操作
     */
    public function shutSpeak()
    {
        $room_id = request()->param('room_id');
        $user_id = request()->param('user_id');
        $res = (new Manage())->shutSpeak($room_id, $user_id);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success(['status' => 1], '禁言成功');
    }

    /**
     * 踢人
     * @desc 主播对直播间观众踢人
     */
    public function kicking()
    {
        $room_id = request()->param('room_id');
        $user_id = request()->param('user_id');
        $res = (new Manage())->kicking($room_id, $user_id);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success(['status' => 1], '踢人成功');
    }

    /**
     * 设置/取消管理员
     * @ignore 用于获取礼物列表
     */
    public function manageSwitch()
    {
        $anchor_id = request()->param('anchor_id');
        $user_id = request()->param('user_id');
        $res = (new Manage())->liveManageSwitch($anchor_id, $user_id);
        if (is_error($res)) return $this->jsonError($res);
        $msg = $res['msg'];
        array_shift($res);
        return $this->success($res, $msg);
    }

    /**
     * 守护列表
     * @return array|string
     */
    public function guardList()
    {   
        // $aa = Db::query("SELECT * FROM bx_deng_level WHERE level_up <= 300 ORDER BY level_up DESC");
        // var_dump($aa[0]);die;
        $res = (new Guard())->getGuard(request()->param('user_id'));
        return $this->success($res);
    }

    //获取直播频道
    public function liveChannel()
    {
        $parent_id = request()->param('parent_id');
        $res = (new Channel())->getLiveChannel($parent_id);
        return $this->success(empty($res) ? [] : $res);
    }

    /**
     * 管理员列表
     * @ignore 用于获取管理员列表
     */
    public function manageList()
    {
        $res = (new Manage())->getManageList();
        return $this->success($res);
    }

    //超管关播
    public function superCloseRoom()
    {
        $room_id = request()->param('room_id');
        $liveDomain = new Manage();
        if (!$liveDomain->validateSuper(USERID)) return $this->jsonError('您无此权限');
        $coreSdk = new CoreSdk();
        $res = $coreSdk->post('live/superCloseRoom', ['room_id' => $room_id]);
        if ($res === false) return $this->jsonError($coreSdk->getError());
        return $this->success([], '关播成功');
    }

    //超管禁播
    public function superStop()
    {
        $user_id = request()->param('user_id');
        $coreSdk = new CoreSdk();
        $res = $coreSdk->post('live/superStop', ['user_id' => $user_id]);
        if ($res === false) return $this->jsonError($coreSdk->getError());
        return $this->success([], '您已被超管禁播');
    }

    //直播付费
    public function livePaa()
    {
        $room_id = request()->param('room_id');
        $res = (new Payment())->livePay($room_id);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success([], '支付完成');
    }

    //推流重连确认(主播端意外断流后恢复推流确认房间是否存在)
    public function joinagainack()
    {
        $room_id = input('room_id');
        $Create = new Create();
        $res = $Create->joinAgainAck($room_id);
        return $this->success(['status' => (int)$res], '');
    }

    /**
     * 直播间音乐主页
     * @return array
     */
    public function musicHome()
    {
        $show = [];
        $MusicData = new MusicData();
        $params = ['length' => 12];
        foreach (MusicData::$default_category as &$value) {
            $order = $value['category_id'] == 1 ? 'use_num desc' : 'id asc';
            $where = $value['category_id'] == 1 ? ['category_id' => 100] : ['category_id' => 101];
            $res = $MusicData->page($params)->musicsByOrder($where, $order);
            $MusicData->initialize($res);
            $value['item'] = $res;
            unset($value['icon']);
        }
        $categorys = $MusicData->categoryByAll();
        //处理分类显示的数据
        foreach ($categorys as $category) {
            if (!$category['is_recommend']) continue;
            $res = $MusicData->page($params)->musicsByCategoryId($category['category_id']);
            if (empty($res)) continue;
            $MusicData->initialize($res);
            $tmp = [
                'title' => $category['title'],
                'category_id' => $category['category_id'],
                'item' => $res,
            ];
            array_push($show, $tmp);
        }
        $recommend = $MusicData->page($params)->musicsByOrder(['category_id' => 101], 'id desc');
        $MusicData->initialize($recommend);
        list($os,) = explode('_', APP_V);
        $ID = $os == 'ios' ? '' : 0;
        $recommend = [
            'title' => '推荐音乐',
            'category_id' => $ID,
            'item' => $recommend,
        ];
        array_unshift(MusicData::$default_category, $recommend);
        $result = array_merge(MusicData::$default_category, $show);
        return $this->success($result);
    }

    public function getNavTree()
    {
        $type = input('nav_type');
        $version = input('version');
        $tree = [];
        if ($type == 'hot') {
            $tree = [
                [
                    'name' => '魅力榜',
                    'icon' => !empty($version) ? DOMAIN_URL . '/static/common/image/nav_tree/meilibang_3x_new.png' : DOMAIN_URL . '/static/common/image/nav_tree/meilibang_3x.png',
                    'link' => getJump('charm_rank'),
                    'descr' => '魅力释放中'
                ],
                [
                    'name' => '英雄榜',
                    'icon' => !empty($version) ? DOMAIN_URL . '/static/common/image/nav_tree/yingxiongbang_new.png' : DOMAIN_URL . '/static/common/image/nav_tree/yingxiongbang_.png',
                    'link' => getJump('heroes_rank'),
                    'descr' => '尽显王者荣耀'
                ],
                [
                    'name' => '封面之星',
                    'link' => '{$h5_service_url}/cover_star',
                    'icon' => !empty($version) ? DOMAIN_URL . '/static/common/image/nav_tree/fengmianzhixing_3x_new.png' : DOMAIN_URL . '/static/common/image/nav_tree/fengmianzhixing_3x.png',
                    'descr' => '火热投票中'
                ]
            ];
        }
        foreach ($tree as &$item) {
            $item['link'] = parse_tpl($item['link'], [
                'h5_service_url' => H5_URL,
                'user_id' => USERID
            ]);
        }
        return $this->jsonSuccess($tree, '获取成功');
    }

    /**
     * 获取直播间活跃用户
     *
     */
    public function getLiveActiveUser(Request $request)
    {
        $params = $request->param();
        $p = $request->param('p', 1);
        $LinkMic = new LinkMic();
        $list = $LinkMic->getActiveUserList($params['room_id'], $p);
        return $this->jsonSuccess($list);
    }

    /**
     * 获取直播间连麦申请列表
     *
     */
    public function getLinkReplyList(Request $request)
    {
        $params = $request->param();
        $p = $request->param('p', 1);
        $LinkMic = new LinkMic();
        $list = $LinkMic->getLinkReplyList($params['room_id'], $p);
        return $this->jsonSuccess($list);
    }

    /**
     * 获取直播连麦列表
     *
     */
    public function getLinkMicList(Request $request)
    {
        $params = $request->param();
        $p = $request->param('p', 1);
        $LinkMic = new LinkMic();
        $list = $LinkMic->getLinkMicList($params['room_id'], $p);
        return $this->jsonSuccess($list);
    }

    /**
     * 获取进房附加信息
     *
     */
    public function getLiveAppendInfo(Request $request)
    {
        $params = $request->param();
        $Live = new Lists();
        $room = $Live->getRoomOne($params['room_id']);
        $Follow = new \app\api\service\Follow();
        $is_follow = $Follow->isFollow($room['user_id']);
        if ($is_follow) return $this->jsonSuccess([]);
        $data = [
            'follow_remind' => '60, 180'
        ];
        return $this->jsonSuccess($data);
    }

    /**
     * 热门直播列表
     *
     * @return array
     */
    public function hotLiveList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $liveDomain = new Lists();
        $hot = [];
        if ($offset < 1) {
            $hot = $liveDomain->getHotTopList();
            if (!empty($hot)) {
                $liveDomain->reset()->setLengthDec(count($hot));
            }
        }
        $liveDomain->setHotLive($offset);
        $hotList = $liveDomain->getLiveList();
        if (!empty($hotList)) $hotList = $liveDomain->initializeLive($hotList, 1);
        if (config('app.live_setting.is_rest_display')) {
            $offList = $liveDomain->getLiveHistory();
            if (!empty($offList)) $offList = $liveDomain->initializeUser($offList, 1);
            $res = array_merge($hot, $hotList, $offList);
        } else {
            $res = $hotList;
        }
        return $this->success($res);
    }


    /**
     * 热门直播列表2
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function liveGetHotLiveList(Request $request)
    {
        $offset = $request->param('offset', 0);
        $Lists = new Lists();
        $Lists->setHotLive($offset);
        $hotList = $Lists->getLiveList();
        if (!empty($hotList)) $hotList = $Lists->initializeLive($hotList, 1);
        /* if (config('app.live_setting.is_rest_display')) {
             $offList = $Lists->getLiveHistory();
             if (!empty($offList)) $offList = $Lists->initializeUser($offList, 1);
             $hotList = array_merge($hotList, $offList);
         } else {
             $hotList = $hotList;
         }*/
        return $this->success($hotList ? $hotList : []);
    }

    /**
     * 最新直播列表
     *
     * @return array
     */
    public function newLiveList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $Lists = new Lists();
        $Lists->setNewLive($offset);
        $newList = $Lists->getLiveList();
        if (!empty($newList)) $newList = $Lists->initializeLive($newList, 1);
        if (config('app.live_setting.is_rest_display')) {
            $offList = $Lists->getLiveHistory();
            if (!empty($offList)) $offList = $Lists->initializeUser($offList, 1);
            $res = array_merge($newList, $offList);
        } else {
            $res = $newList;
        }
        return $this->success($res);
    }

    /**
     * 频道相关直播列表
     *
     * @return array
     */
    public function liveList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $channels = [
            'talk_show' => 5,
            'sing_show' => 2,
            'nice_show' => 1
        ];
        $channel = isset($params['nav_type']) ? $params['nav_type'] : $channels[$params['channel']];
        $Lists = new Lists();
        $Lists->setChannelLive($channel, $offset);
        $liveList = $Lists->getLiveList();
        if (!empty($liveList)) $liveList = $Lists->initializeLive($liveList, 1);
        if (config('app.live_setting.is_rest_display')) {
            $offList = $Lists->getLiveHistory();
            if (!empty($offList)) $offList = $Lists->initializeUser($offList, 1);
            $res = array_merge($liveList, $offList);
        } else {
            $res = $liveList;
        }
        return $this->success($res);
    }

    /**
     * 直播间用户信息弹窗
     *
     * @return \think\response\Json
     */
    public function liveUserPop()
    {
        $room_id = request()->param('room_id');
        $user_id = request()->param('user_id');
        $Manage = new Manage();
        $rs = $Manage->userCard($room_id, $user_id);
        if (is_error($rs)) return $this->jsonError($rs);
        return $this->success($rs);
    }

    /**
     * 主播印象
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function impression(Request $request)
    {
        $params = $request->param();
        if (empty($params['user_id'])) return $this->jsonError('参数错误');
        $is_anchor = Db::name('user')->where('user_id', $params['user_id'])->value('is_anchor');
        if (empty($is_anchor)) return $this->jsonError('对方不是主播身份');
        $all_impression = Db::name('impression')->field('id, name, color')->where(['status' => 1, 'type' => 0])->select();
        //先拿用户选择的
        $user_impression = Db::name('user_impression')->where(['user_id' => USERID, 'anchor_uid' => $params['user_id']])->select();
        !empty($user_impression) && $user_impression_ids = array_column($user_impression, 'impression_id');
        //再拿主播选择的
        $own_impression = Db::name('user_impression')->where(['user_id' => $params['user_id'], 'anchor_uid' => $params['user_id']])->select();
        !empty($own_impression) && $own_impression_ids = array_column($own_impression, 'impression_id');
        foreach ($all_impression as &$value) {
            $value['anchor_select'] = $value['user_select'] = 0;
            if (isset($own_impression_ids) && in_array($value['id'], $own_impression_ids)) {
                $value['anchor_select'] = 1;
            }
            if (isset($user_impression_ids) && in_array($value['id'], $user_impression_ids)) {
                $value['user_select'] = 1;
            }
        }
        return $this->success($all_impression);
    }

    /**
     * 保存对主播印象的选择
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function saveImpression(Request $request)
    {
        $params = $request->param();
        if (empty($params['user_id']) || empty($params['ids'])) return $this->jsonError('参数错误');
        $is_anchor = Db::name('user')->where('user_id', $params['user_id'])->value('is_anchor');
        if (empty($is_anchor)) return $this->jsonError('对方不是主播身份');
        $all_impression = Db::name('impression')->where('status', 1)->column('id');
        $select_id = explode(',', $params['ids']);
        if (count($select_id) > 3) return $this->jsonError('您最多只能对主播添加3个印象');
        $exists = array_diff($select_id, $all_impression);
        if (!empty($exists)) return $this->jsonError('参数错误');
        $now = time();
        $user_impression = Db::name('user_impression')->where(['user_id' => USERID, 'anchor_uid' => $params['user_id']])->select();
        if (!empty($user_impression)) {
            $user_select_ids = array_column($user_impression, 'impression_id');
            $save_ids = array_diff($select_id, $user_select_ids);
            if (empty($save_ids) && count($select_id) == count($user_select_ids)) return $this->success([], '保存成功');
            $del = Db::name('user_impression')->where(['user_id' => USERID, 'anchor_uid' => $params['user_id']])->delete();
            if (!$del) return $this->jsonError('保存出错');
        }
        $insert = [];
        foreach ($select_id as $p_id) {
            $tmp = [
                'anchor_uid' => $params['user_id'],
                'user_id' => USERID,
                'impression_id' => $p_id,
                'create_time' => $now
            ];
            array_push($insert, $tmp);
        }
        $rs = Db::name('user_impression')->insertAll($insert);
        if (!$rs) return $this->jsonError('保存错误');
        return $this->success([], '保存成功');
    }

    /**
     * 热门直播列表
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function getHotLiveList(Request $request)
    {
        $offset = $request->param('offset', 0);
        $res = [
            'recommendOne' => [],
            'recommendTwo' => [],
            'hotList' => [],
        ];
        $Lists = new Lists();
        if ($offset < 1) {
            $Lists->setNewHotLive($offset);
        } else {
            $Lists->setNewHotLive($offset + 6);
        }

        //$Lists->setHotLive($offset + 6);
        $hotList = $Lists->getLiveList();
        if (!empty($hotList)) $hotList = $Lists->initializeLive($hotList, 1);
        if (config('app.live_setting.is_rest_display')) {
            $offList = $Lists->getLiveHistory();
            // var_dump($offList);die;
            if (!empty($offList)) $offList = $Lists->initializeUser($offList, 1);
            $res['hotList'] = array_merge($hotList, $offList);
        } else {
            $res['hotList'] = $hotList;
        }
        // var_dump($res);die;
        if ($offset < 1) {
            $res['recommendOne'] = array_slice($res['hotList'] , 0, 4);
            $res['recommendTwo'] = array_slice($res['hotList'] , 4, 2);
            $res['hotList'] = array_slice($res['hotList'] , 6, 4);
        }
        return $this->success($res);
    }

    /**
     * 关注直播列表
     *
     * @return array
     */
    public function followLiveList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $liveDomain = new Lists();
        $followLive = $liveDomain->setFollowLive($offset)->getLiveList();
        //if (empty($followLive)) return [];
        $followLive = $liveDomain->initializeLive($followLive);
        return $this->success($followLive);
    }

    /**
     *  导购直播列表
     *
     * @return array
     */
    public function shoppingLiveList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $liveDomain = new Lists();
        //$shoppingLive = $liveDomain->setShoppingLive($offset)->getLiveList();
        $shoppingLive = $liveDomain->setShoppingLive($offset);
        if ($shoppingLive) {
            $shoppingLive = $liveDomain->initializeLive($shoppingLive);
        }
        return $this->success($shoppingLive ? $shoppingLive : []);
    }

    /**
     * 嗨聊直播列表
     */
    public function voiceList()
    {
        $params = request()->param();
        if (isset($params['voice_value'])) {
            if (!enum_in($params['voice_value'], 'voice_type')) return $this->jsonError('系统错误');
        }
        $voice_value = isset($params['voice_value']) ? $params['voice_value'] : -1;

        $offset = $params['offset'] ? $params['offset'] : 0;
        $liveDomain = new Lists();
        $voicepingLive = $liveDomain->setVoiceLive($offset, $voice_value);
        if ($voicepingLive) {
            $voicepingLive = $liveDomain->initializeLive($voicepingLive);
        }
        return $this->success($voicepingLive ? $voicepingLive : []);
    }

    /**
     *  附近直播列表
     *
     * @return array
     */
    public function nearbyLiveList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $lng = $params['lng'] ? $params['lng'] : 0;
        $lat = $params['lat'] ? $params['lat'] : 0;
        $city = $params['city'] ? $params['city'] : 0;
        $sex = $params['sex'] ? $params['sex'] : 0;// 性别不限 1 男  2 女 0不限
        $age = $params['age'] ? $params['age'] : 0; //0 不限  20 25 30
        $liveDomain = new Lists();
        //$nearbyLive = $liveDomain->setNearbyLive($offset, $lng, $lat, $city, $sex, $age)->getLiveList();
        $nearbyLive = $liveDomain->setNearbyLive($offset, $lng, $lat, $city, $sex, $age);
        if ($nearbyLive) {
            $nearbyLive = $liveDomain->initializeLive($nearbyLive, 0, $lng, $lat);
        }
        return $this->success($nearbyLive ? $nearbyLive : []);
    }

    /**
     * 是否关注主播
     */
    public function isFollow(Request $request)
    {
        $params = $request->param();
        $Follow = new \app\api\service\Follow();
        $userId = $params['user_id'];
        $is_follow = $Follow->isFollow($userId);

        return $this->jsonSuccess(['is_follow' => $is_follow]);
    }

    /**
     * zack
     * 获取单场pk房间粉丝的贡献榜
     */
    public function pkRank(Request $request)
    {
        $params = $request->param();
        $coreSdk = new CoreSdk();
        $userRes = $coreSdk->post('zombie/get_pk_rank', $params);
        if ($userRes === false) return $this->jsonError($coreSdk->getError());
        return $this->success($userRes ? $userRes : [], '获取成功');
    }

    /**
     * 主播是否开启当前场次的连麦
     * @param $roomId 房间号
     * @param $status 0表示关麦 1表示开麦
     */
    public function openMic(Request $request)
    {
        $params = $request->param();
        $status = isset($params['status']) ? $params['status'] : 0;
        $roomId = $params['room_id'];
        if ($status != 0 && $status != 1) return $this->jsonError('操作有问题');
        if (empty($roomId)) return $this->jsonError('房间号有问题');
        $live = Db::name('live')->where(['id' => $roomId, 'status' => 1])->find();
        if (empty($live)) return $this->jsonError('直播间不存在');
        if ($live['user_id'] != USERID) return $this->jsonError('直播间错误');
        $this->redis->set('LinkMic:roomid:' . $roomId, $status);
        return $this->jsonSuccess($roomId, '设置成功');
    }

    /**
     * 崔鹏   2020/07/13
     * 在线用户统计接口
     */
    public function onlineInRoom()
    {
        $params = request()->param();
        $room_id = $params['room_id'];
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size = $params['page_size'] ? $params['page_size'] : 10;
        $audienceArr = Zombie::getAudienceList($room_id);
        foreach ($audienceArr as $k => $v) {
            //$userdetail = userMsg($v['user_id'], 'user_id,gender,birthday');
            //年龄计算
            if ($v['birthday']) {
                $audienceArr[$k]['age'] = birthday_to_age(strtotime($v['birthday']));
            } else {
                $audienceArr[$k]['age'] = 0;
            }
        }
        if (is_error($audienceArr) || empty($audienceArr)) {
            return $this->success([
                'total_count' => 0,
                'page_count' => 1,
                'data' => [],
            ]);
        }
        $rest = $this->page_array($page_size, $page_index, $audienceArr, 0);
        return $this->success($rest, '查询成功');
    }

    /**
     * 崔鹏
     * 数组分页函数  核心函数  array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $count   每页多少条数据
     * $page   当前第几页
     * $array   查询出来的所有数组，要进行分页的数据
     * order   0 不变     1 反序
     */
    function page_array($count, $page, $array, $order)
    {
        $redis = RedisClient::getInstance();
        global $countpage; #定全局变量
        $page = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata = [];
        $pagedata = array_slice($array, $start, $count);
        $data = [
            'total_count' => $totals,
            'page_count' => $countpage,
            'data' => $pagedata,
        ];
        return $data;  //返回查询数据
    }


    /**
     *  导购直播列表
     *
     * @return array
     */
    public function shoppingLiveListNew()
    {

        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $goods_type = $params['goods_type'] ? $params['goods_type'] : '';  //0：第三方商品；1：自营商品
        $room_second_channel = $params['room_second_channel'] ? $params['room_second_channel'] : 0; //二级分类
        $liveDomain = new Lists();

        $shoppingLive = $liveDomain->setShoppingLiveNew($offset,$goods_type,$room_second_channel); //正在直播商品的主播信息
        if ($shoppingLive) {
            $shoppingLive = $liveDomain->initializeLiveNew($shoppingLive);
        }
        return $this->success($shoppingLive ? $shoppingLive : []);
    }
}