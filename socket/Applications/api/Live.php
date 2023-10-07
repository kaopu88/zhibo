<?php
/**
 * 客户端请求格式： {"mod": "Live","act": "create","args": {"room_id":"12315","user_id":"8046","token":"asd2asda2323sda2sda"}}
 * mod: 类名;
 * act: 方法名;
 * args: 具体参数
 */

namespace app\api;


use app\service\Kpi;
use app\service\Logger;
use app\service\MilletTools;
use app\service\Monitor;
use app\service\payment\Barrage;
use app\service\payment\LivePay;
use app\service\User;
use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;
use GuzzleHttp\Client;
use app\Common;


class Live extends Common
{
    protected static $max_timeout = 86400;
    protected static $sys_msg = '平台提倡绿色直播，封面和直播内容含低俗、引诱、暴露、辱骂他人等行为都将被封停账号，同时禁止直播聚众闹事、集合，安全小组会24小时巡查哦～';
    protected static $film_sys_msg = '声明：当前视频来源于互联网, 片中涉及的广告字幕（如：赌博、彩票、游戏等）和水印网址均不可信，请勿访问！如有侵犯版权请联系我们删除。';

    // 主播创建房间
    public static function create(array $params)
    {
        global $currentClient;
        $room_id = $params['room_id'];
        self::removeHistoryGroup();

        if (!isset($room_id) || empty($room_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '开播错误~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }

        Gateway::joinGroup($currentClient, $room_id);
        $_SESSION['group'] = $room_id;
        $_SESSION['user_identity'] = 'anchor';
        Gateway::sendToCurrentClient(self::genMsg('systemMsg', self::$sys_msg, ['content' => self::$sys_msg])); //推送系统消息

        $zombieTimerId = Timer::add(1, [__CLASS__, 'addRobot'], [$room_id, &$zombieTimerId]);// 每秒检查一次zombieTask队列数据
        Logger::info('主播创建直播间完成添加机器人定时器,timer_id=>' . $zombieTimerId, 'addTimer');

        $anchorTaskTimerId = Timer::add(600, [__CLASS__, 'anchorTask'], [$params, &$anchorTaskTimerId]);// 每600秒检查一次AnchorTask队列数据
        Logger::info('主播创建直播间完成添加任务刷新定时器,timer_id=>' . $anchorTaskTimerId, 'addTimer');

        Monitor::listen('create_after', $params);
    }


    // 用户进入房间
    public static function enter(array $params)
    {
        global $currentClient, $redis, $db;;
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];

        $data = [
            'type' => 0,
            'user_info' => [],
            'content' => '来捧场了~'
        ];

        self::removeHistoryClient($user_id);
        self::removeHistoryGroup();

        if (!isset($room_id) || empty($room_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误1~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }

        $api_room_id = $redis->get('BG_ROOM:enter:' . $user_id);
        if ($room_id != $api_room_id) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误2~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }

        $kickingKey = self::$livePrefix . $room_id . ':KICK';//踢人集合key

        if ($redis->sismember($kickingKey, $user_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，您已被拒绝进入此直播间~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }

        $user_info = self::getUserBasicInfo($user_id);

        if (empty($user_info)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误3~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }

        Gateway::sendToCurrentClient(self::genMsg('systemMsg', 'ok', ['content' => self::$sys_msg])); //发送系统信息

        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        
        
        $is_film_live = $redis->sismember(self::$livePrefix . 'Living', $anchor_id);

        if (!$is_film_live) Gateway::sendToCurrentClient(self::genMsg('systemMsg', 'ok', ['content' => self::$film_sys_msg]));

        //相同的主播id报错
        if ($user_id == $anchor_id) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误4~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }

        Gateway::joinGroup($currentClient, $room_id);
        $_SESSION['group'] = $room_id;
        $_SESSION['user_identity'] = 'user';
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        $user_info = [
            'avatar' => $user_info['avatar'],
            'user_id' => $user_info['user_id'],
            'nice_name' => $user_info['nickname'],
            'level' => $user_info['level'],
            'vip_status' => $user_info['vip_status'],
            'guard_status' => self::guardStatus($user_id, $room_id),
            'control_status' => self::controlStatus($user_id, $room_id, $user_info),
            'mount_url' => '',
            'goodnum'  => isset($user_info['goodnum'])?$user_info['goodnum']:null,
            'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
            'deng_level'  =>$dengLevel
        ];

        if ($user_info['level'] >= RANK_GOLDEN_LIGHT) $data['type'] = 1; //展示入房金光效果

        //展示坐驾效果
        $use_props_info = $redis->get('BG_PROPS:' . $user_id);

        if (!empty($use_props_info)) {
            $use_props_info = json_decode($use_props_info, true);
            $sql = "SELECT up.id, props_id, `action_desc`, expire_time, use_status, p.file_url FROM " . TABLE_PREFIX . "user_props up INNER JOIN " . TABLE_PREFIX . "props p ON up.props_id=p.id  WHERE up.`id`={$use_props_info['id']} LIMIT 1";
            $res = $db->query($sql);

            if ($res[0]['expire_time'] > time()) {
                $data['type'] = 2;
                $user_info['props_id'] = $use_props_info['props_id'];
                $user_info['mount_url'] = $res[0]['file_url'];
                $data['content'] = $res[0]['action_desc'];
                if (!isset($use_props_info['expire_time']) || $res[0]['expire_time'] != $use_props_info['expire_time']) {
                    $redis->set('BG_PROPS:' . $user_id, json_encode($res[0]));
                }
            } else {
                $db->delete(TABLE_PREFIX . 'user_props')->where('id=' . $use_props_info['id'])->query();
                $redis->del('BG_PROPS:' . $user_id);
            }
        }

        $data['user_info'] = $user_info;
        //非座驾和入房金光效果下提示入房来源
        if (!empty($params['from']) && $data['type'] == 0) {
            global $api_v;
            switch ($params['from']) {
                case 'hot' :
                    $data['content'] = '通过 热门推荐 进入直播间~';
                    break;
                case 'video' :
                    $data['content'] = '通过 视频推荐 进入直播间~';
                    break;
                case 'follow' :
                    $data['content'] = '通过 关注 进入直播间~';
                    break;
                case 'search' :
                    $data['content'] = '通过 搜索 进入直播间~';
                    break;
                case 'personal' :
                    $data['content'] = '通过 个人主页 进入直播间~';
                    break;
            }

            Gateway::sendToGroup($room_id, self::genMsg('enterMsg', 'ok', $data));
        } else {
            Gateway::sendToGroup($room_id, self::genMsg('enterMsg', $data['content'], $data));
        }

       /* $class = "app\\service\\LiveGoods";
        if (class_exists($class)) {
            if (method_exists($class, 'getLiveGoods')) {
                call_user_func_array([$class, 'getLiveGoods'], [$params]);
            }
        }*/
        //处理h5效果
        /*if (!empty($params['from']))
        {
            global $api_v;
            switch ($params['from'])
            {
                case 'hot' :
                    $api_v == 'v2' ? $message['content'] = '<p style="color:#ffffff;background: rgba(0, 0, 0, 0.2);padding: 5px 10px;border-radius: 20px;">通过<span style="color: #ec6969;font-size: 15px;padding: 0 5px;">热门推荐热门推荐热门推荐热门推荐热门推荐热门推荐热门推荐热门推荐热门推荐热门推荐热门推荐</span>进入直播间~</p>' : $data['content'] = '通过 热门推荐 进入直播间~';
                    break;
                case 'video' :
                    $api_v == 'v2' ? $message['content'] = '<p style="color:#ffffff;background: rgba(0, 0, 0, 0.2);padding: 5px 10px;border-radius: 20px;">通过<span style="color: #ec6969;font-size: 15px;padding: 0 5px;">视频推荐</span>进入直播间~</p>' : $data['content'] = '通过 视频推荐 进入直播间~';
                    break;
                case 'follow' :
                    $api_v == 'v2' ? $message['content'] = '<p style="color:#ffffff;background: rgba(0, 0, 0, 0.2);padding: 5px 10px;border-radius: 20px;">通过<span style="color: #ec6969;font-size: 15px;padding: 0 5px;">关注</span>进入直播间~</p>' : $data['content'] = '通过 视频推荐 进入直播间~';
                    break;
                case 'search' :
                    $api_v == 'v2' ? $message['content'] = '<p style="color:#ffffff;background: rgba(0, 0, 0, 0.2);padding: 5px 10px;border-radius: 20px;">通过<span style="color: #ec6969;font-size: 15px;padding: 0 5px;">搜索</span>进入直播间~</p>' : $data['content'] = '通过 视频推荐 进入直播间~';
                    break;
                case 'personal' :
                    $api_v == 'v2' ? $message['content'] = '<p style="color:#ffffff;background: rgba(0, 0, 0, 0.2);padding: 5px 10px;border-radius: 20px;">通过<span style="color: #ec6969;font-size: 15px;padding: 0 5px;">主播主页</span>进入直播间~</p>' : $data['content'] = '通过 视频推荐 进入直播间~';
                    break;
            }
            $api_v == 'v2' ? Gateway::sendToGroup($room_id, self::genMsg('h5Msg', '', $message)) : Gateway::sendToGroup($room_id, self::genMsg('enterMsg', 'ok', $data));
        }*/

        //处理pk信息
        if (isset($params['pk_id']) && !empty($params['pk_id'])) {
            $pk_info = self::getPkAnchorInfo($params['pk_id']);
            //正在Pk中，并且还未结束
            if (!empty($pk_info) && $pk_info['status'] == 0) {
                if (strcasecmp($pk_info['pk_topic'], self::$pk_topic) != 0 || strcasecmp($pk_info['ac_topic'], self::$pk_topic) != 0) {
                    //pk中主题
                    if (time() < $pk_info['pk_start_time'] + $pk_info['pk_duration']) {
                        $pk_topic = self::genMsg('systemMsg', 'ok', ['content' => '本场PK主题 【' . $pk_info['pk_topic'] . '】']);
                    } //惩罚主题
                    else {
                        //$ac_text = $pk_info['pk_status_res'] == 0 ?  : '惩罚时间';
                        $ac_text = '交流时间';
                        $pk_topic = self::genMsg('systemMsg', 'ok', ['content' => '进入' . $ac_text . '，本场PK惩罚主题 【' . $pk_info['ac_topic'] . '】']);
                    }
                    Gateway::sendToCurrentClient($pk_topic);
                }
            }
        }

        $audienceKey = self::$livePrefix . $room_id . ':audience';//直播间实时观众UID集合
        $roomZombieKey = self::$livePrefix . $room_id . ':robot';//房间所有僵尸粉UID集合

        self::setAudience($room_id, $user_id, 'enter', $user_info['level']); //增加房间人数
        $audience = $redis->zcard($audienceKey);
        $robot = $redis->zcard($roomZombieKey);
        $audience *= 2;
        $robot *= 20;
        $total_num = $audience + $robot;
        $total = ['total' => empty($total_num) ? 480 : $total_num, 'need_pull' => 1, 'user_id' => $user_id];
        $MS = self::genMsg('onlineTotal', 'ok', $total);
        Gateway::sendToGroup($room_id, $MS);
        $kpi = new Kpi();
        $kpi->active($anchor_id, $user_info); //统计主播活跃人数
        $kpi->live($user_info);//直播间在线人数
        Monitor::listen('enter_room_after', $params);
    }

    //用户切换房间
    public static function switchRoom(array $params)
    {
        global $currentClient, $redis, $db;;
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];
        $data = [
            'type' => 0,
            'user_info' => [],
            'content' => '来捧场了~'
        ];

        if (!isset($room_id) || empty($room_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误~', [], 1));
            return;
        }

        $kickingKey = self::$livePrefix . $room_id . ':KICK';//踢人集合key
        if ($redis->sismember($kickingKey, $user_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，您已被拒绝进入此直播间~', [], 1));
            return;
        }

        $api_room_id = $redis->get('BG_ROOM:enter:' . $user_id);
        if ($room_id != $api_room_id) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误~', [], 1));
            return;
        }

        $user_info = self::getUserBasicInfo($user_id);
        if (empty($user_info)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误~', [], 1));
            return;
        }
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        //相同的主播id报错
        if ($user_id == $anchor_id) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '进入直播间错误~', [], 1));
            return;
        }

        if (isset($_SESSION['group']) && !empty($_SESSION['group'])) {
            self::setAudience($_SESSION['group'], $user_id, 'exitRoom');
            $oldAudienceKey = self::$livePrefix . $_SESSION['group'] . ':audience';//直播间实时观众UID集合
            $oldAoomZombieKey = self::$livePrefix . $_SESSION['group'] . ':robot';//房间所有僵尸粉UID集合
            $oldAudience = $redis->zcard($oldAudienceKey);
            $oldRobot = $redis->zcard($oldAoomZombieKey);
            $oldAudience *= 2;
            $oldRobot *= 20;
            $old_total_num = $oldAudience + $oldRobot;
            $old_total = ['total' => empty($old_total_num) ? 480 : $old_total_num, 'need_pull' => 1, 'user_id' => $user_id];
            $oldMS = self::genMsg('onlineTotal', 'ok', $old_total);
            Gateway::sendToGroup($_SESSION['group'], $oldMS);
        }

         //移除之前主播间的人数待完善
        self::removeHistoryGroup();

        Gateway::sendToCurrentClient(self::genMsg('systemMsg', 'ok', ['content' => self::$sys_msg]));
        $is_film_live = $redis->sismember(self::$livePrefix . 'Living', $anchor_id);
        if (!$is_film_live) Gateway::sendToCurrentClient(self::genMsg('systemMsg', 'ok', ['content' => self::$film_sys_msg]));

        $_SESSION['group'] = $room_id;
        Gateway::joinGroup($currentClient, $room_id);
        $_SESSION['user_identity'] = 'user';
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        $user_info = [
            'avatar' => $user_info['avatar'],
            'user_id' => $user_info['user_id'],
            'nice_name' => $user_info['nickname'],
            'level' => $user_info['level'],
            'vip_status' => $user_info['vip_status'],
            'guard_status' => self::guardStatus($user_id, $room_id),
            'control_status' => self::controlStatus($user_id, $room_id, $user_info),
            'mount_url' => '',
            'goodnum'  =>isset($user_info['goodnum'])?$user_info['goodnum']:null,
            'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
            'deng_level'  =>$dengLevel
        ];

        if ($user_info['level'] >= RANK_GOLDEN_LIGHT) $data['type'] = 1; //展示入房金光效果

        //展示坐驾效果
        $use_props_info = $redis->get('BG_PROPS:' . $user_id);
        if (!empty($use_props_info)) {
            $use_props_info = json_decode($use_props_info, true);
            $sql = "SELECT up.id, props_id, `action_desc`, expire_time, use_status, p.file_url FROM " . TABLE_PREFIX . "user_props up INNER JOIN " . TABLE_PREFIX . "props p ON up.props_id=p.id  WHERE up.`id`={$use_props_info['id']} LIMIT 1";
            $res = $db->query($sql);
            if ($res[0]['expire_time'] > time()) {
                $data['type'] = 2;
                $user_info['props_id'] = $use_props_info['props_id'];
                $user_info['mount_url'] = $res[0]['file_url'];
                $data['content'] = $res[0]['action_desc'];
                if (!isset($use_props_info['expire_time']) || $res[0]['expire_time'] != $use_props_info['expire_time']) {
                    $redis->set('BG_PROPS:' . $user_id, json_encode($res[0]));
                }
            } else {
                $db->delete(TABLE_PREFIX . 'user_props')->where('id=' . $use_props_info['id'])->query();
                $redis->del('BG_PROPS:' . $user_id);
            }
        }
        $data['user_info'] = $user_info;
        //非座驾和入房金光效果下提示入房来源
        if (!empty($params['from']) && $data['type'] == 0) {
            $data['content'] = '通过 直播页 进入直播间~';
            Gateway::sendToGroup($room_id, self::genMsg('enterMsg', 'ok', $data));
        } else {
            Gateway::sendToGroup($room_id, self::genMsg('enterMsg', $data['content'], $data));
        }
        $redis->set('BG_ROOM:enter:' . $user_id, $room_id); //进入某个直播间

       /* $class = "app\\service\\LiveGoods";
        if (class_exists($class)) {
            if (method_exists($class, 'getLiveGoods')) {
                call_user_func_array([$class, 'getLiveGoods'], [$params]);
            }
        }*/

        //处理pk信息
        if (isset($params['pk_id']) && !empty($params['pk_id'])) {
            $pk_info = self::getPkAnchorInfo($params['pk_id']);
            //正在Pk中，并且还未结束
            if (!empty($pk_info) && $pk_info['status'] == 0) {
                if (strcasecmp($pk_info['pk_topic'], self::$pk_topic) != 0 || strcasecmp($pk_info['ac_topic'], self::$pk_topic) != 0) {
                    //pk中主题
                    if (time() < $pk_info['pk_start_time'] + $pk_info['pk_duration']) {
                        $pk_topic = self::genMsg('systemMsg', 'ok', ['content' => '本场PK主题 【' . $pk_info['pk_topic'] . '】']);
                    } //惩罚主题
                    else {
                        //$ac_text = $pk_info['pk_status_res'] == 0 ? '交流时间' : '惩罚时间';
                        $ac_text = '交流时间';
                        $pk_topic = self::genMsg('systemMsg', 'ok', ['content' => '进入' . $ac_text . '，本场PK惩罚主题 【' . $pk_info['ac_topic'] . '】']);
                    }
                    Gateway::sendToCurrentClient($pk_topic);
                }
            }
        }

        $audienceKey = self::$livePrefix . $room_id . ':audience';//直播间实时观众UID集合
        $roomZombieKey = self::$livePrefix . $room_id . ':robot';//房间所有僵尸粉UID集合
        self::setAudience($room_id, $user_id, 'enter', $user_info['level']); //增加房间人数
        $audience = $redis->zcard($audienceKey);
        $robot = $redis->zcard($roomZombieKey);
        $audience *= 2;
        $robot *= 20;
        $total_num = $audience + $robot;
        $total = ['total' => empty($total_num) ? 480 : $total_num, 'need_pull' => 1, 'user_id' => $user_id];
        $MS = self::genMsg('onlineTotal', 'ok', $total);
        Gateway::sendToGroup($room_id, $MS);
        $kpi = new Kpi();
        $kpi->active($anchor_id, $user_info); //统计主播活跃人数
        $kpi->live($user_info);//直播间在线人数
        Monitor::listen('enter_room_after', $params);

    }

    // 用户使用直播物品通用事件
    public static function sendGift(array $params)
    {
        global $redis, $currentClient;
        $api_room_id = $redis->get('BG_ROOM:enter:' . $params['user_id']);
        /*if ($params['room_id'] != $api_room_id) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '直播间id错误~', [], 1));
            Gateway::closeClient($currentClient); //踢掉该用户的连接
            return;
        }*/

        if (!isset($params['type'])) $params['type'] = 'gift';
        $class = "app\\service\\" . ucfirst($params['type']);

        if (class_exists($class)) {
            if (method_exists($class, 'useGift')) {
                call_user_func_array([$class, 'useGift'], [$params]);
            }
        }
    }


    // 用户断线重连
    public static function joinAgain(array $params)
    {
        global $currentClient, $redis;
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];
        $anchorId = MilletTools::getAnchorIdByRoomId($room_id);

        if (empty($anchorId)) {
            Gateway::sendToCurrentClient(self::genMsg('kickRoom', '数据错误~1', [], 1));
            Gateway::closeClient($currentClient);
            return;
        }

        if ($anchorId != $user_id) {
            $api_room_id = $redis->get('BG_ROOM:enter:' . $user_id);
            if ($room_id != $api_room_id) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '直播间id错误~', [], 1));
                Gateway::closeClient($currentClient); //踢掉该用户的连接
                return;
            }
        }

        $kickingKey = self::$livePrefix . $room_id . ':KICK'; //踢人集合key

        if (!isset($room_id) || empty($room_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '重连错误~', [], 1));
            Gateway::closeClient($currentClient);
            return;
        }

        if ($redis->sismember($kickingKey, $user_id)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，您已被拒绝进入此直播间~', [], 1));
            Gateway::closeClient($currentClient);
            return;
        }

        self::removeHistoryClient($user_id);
        self::removeHistoryGroup();
        Gateway::joinGroup($currentClient, $room_id);
        $_SESSION['group'] = $room_id;
        $_SESSION['user_identity'] = $anchorId == $user_id ? 'anchor' : 'user';
        Gateway::sendToCurrentClient(self::genMsg('systemMsg', 'ok', ['content' => '重连成功～']));
    }


    // 用户发送聊天消息
    // type 1 普通消息 2弹幕
    public static function sendMsg(array $params)
    {
        global $currentClient, $redis;
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];
        $type = $params['type'];
        $content = $params['content'];
        
        
        if (!isset($room_id) || $user_id != $_SESSION['user_id'] || empty($content)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '数据错误~2'));
            return;
        }

        $isInList = $redis->hget(self::$livePrefix . $room_id . ':SHUT', $user_id); //是否被禁言判断

        if ($isInList && time() <= $isInList) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，您已被禁言~'));
            return;
        }

        $userInfo = User::getUser($user_id);

        if (empty($userInfo)) return;

        // if (empty($userInfo['phone']) && $userInfo['isvirtual'] == 0) {
        //     Gateway::sendToCurrentClient(self::genMsg('tipMsg', '请绑定手机后继续~'));
        //     return;
        // }

        if ($userInfo['credit_score'] <= 0) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，帐户功能受限~'));
            return;
        }

        $anchorId = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchorId);
        if ($anchorId != $user_id) {
            $api_room_id = $redis->get('BG_ROOM:enter:' . $user_id);
            if ($room_id != $api_room_id) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '直播间id错误~', [], 1));
                Gateway::closeClient($currentClient); //踢掉该用户的连接
                return;
            }
        }
        $dengLevel = self::dengLevel($anchorId,$user_id);
        $medal = $redis->zscore('activity:pk_rank:fans_medal', $user_id);
        $data = [
            'type' => $type,
            'user_info' => [
                'user_id' => $userInfo['user_id'],
                'nice_name' => $userInfo['nickname'],
                'avatar' => $userInfo['avatar'],
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_expire'] > time() ? 1 : 0,
                'guard_status' => self::guardStatus($user_id, $room_id),
                'control_status' => self::controlStatus($user_id, $room_id, $userInfo),
                'medal' => $medal ? 1 : 0,
                'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
                'deng_level'  =>$dengLevel
            ],
            'content' => $content,
        ];

        try {
            /*$sensitives = file_get_contents(SERVICE_URL . '/core/filter/check?content=' . $content);
            $sensitives = json_decode($sensitives, true);
            $sensitive = isset($sensitives['data']) ? $sensitives['data'] : '';
            var_dump($sensitive);*/
            if (!defined('SERVICE_URL') || empty(SERVICE_URL)) throw new \Exception('未配置服务地址');
            $client = new Client(['base_uri' => SERVICE_URL]);
            $response = $client->request('POST', '/core/filter/check?content=' . $content);
            $sensitives = $response->getBody()->getContents();
            $sensitives = json_decode($sensitives, true);
            $sensitive = isset($sensitives['data']) ? $sensitives['data'] : '';
        } catch (\Exception $e) {
            $sensitive = '';
        }

        if (!empty($sensitive))
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '内容含有敏感词'));
            return;
            //对接rabbitMQ
            /* $rabbitChannel = new RabbitMqChannel(['user.credit']);
             $rabbitChannel->exchange('main')->sendOnce('user.credit.live_speak_illegal', ['user_id' => $user_id, 'room_name' => $room_id, 'keyword'=>$content]);*/
        }

        if ($type == 2) {
            if ($userInfo['level'] < BARRAGE_LEVEL) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，'.BARRAGE_LEVEL.'级及以上用户才能发送弹幕~'));
                return;
            }

            if ($userInfo['bean'] < BARRAGE_FEE) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，余额不足~', [], 1005));
                return;
            }


            /**
             * 飘瓶付费
             *
             */
            $pay = Barrage::payment([
                'user_id' => $user_id,
                'total' => BARRAGE_FEE,
                'to_uid' => $anchorId
            ]);

            if ($pay === false) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', Barrage::getMessage(), [], Barrage::getCode()));
                return;
            }

            //推送用户账户余额信息
            Gateway::sendToCurrentClient(bin2hex(json_encode(['emit' => 'cuckooInfo', 'data' => ['cuckoo' => $userInfo['bean'] - BARRAGE_FEE]])));
        }

        if ($type == 1) {
            if ($userInfo['level'] < MESSAGE_LEVEL) {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '抱歉，'.MESSAGE_LEVEL.'级及以上用户才能发送消息~'));
                return;
            }
        }

        if (empty($sensitive) && $userInfo['credit_score'] >= CREDIT_SCORE) {
            $message = self::genMsg('chatMsg', $content, $data);
            Gateway::sendToGroup($room_id, $message);
            $params['message'] = $message;
            Monitor::listen('send_message_after', $params);
            return;
        } else {
            $toAnchorMsg = $data;

            if (!empty($sensitive)) {
                $toAnchorMsg['content'] = '【私】 ' . $toAnchorMsg['content'];
            }

            //获取主播连接标识符
            $currentClients = Gateway::getClientIdByUid($anchorId);

            if (!empty($currentClients) && $currentClients[0] == $currentClient) {
                Gateway::sendToUid($anchorId, self::genMsg('chatMsg', $toAnchorMsg['content'], $toAnchorMsg));
            } else {
                Gateway::sendToUid($anchorId, self::genMsg('chatMsg', $toAnchorMsg['content'], $toAnchorMsg));
                Gateway::sendToCurrentClient(self::genMsg('chatMsg', $content, $data));
            }
        }
    }


    // 用户点亮星星(最新版本)
    public static function sendLighting(array $params)
    {
        global $redis;
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];

        if (!isset($room_id) || $user_id !== $_SESSION['user_id']) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '数据错误3', [], 1));
            return;
        }

        $userInfo = self::getUserBasicInfo($user_id);
        $likeKey = self::$livePrefix . $room_id . ':like';
        $redis->incr($likeKey);

        if ($userInfo['vip_status'] == 0) {
            $max = $userInfo['level'] < 40 ? 4 : 8;
            $icon = 'livelight_' . mt_rand(1, $max);
        } else {
            $icon = 'lightvip_' . mt_rand(1, 3);
        }

        Gateway::sendToGroup($room_id, self::genMsg('showLighting', '点亮了~', ['icon' => $icon]));
        $lightTime = time();

        if (!isset($_SESSION['light_time'])) $_SESSION['light_time'] = 0;
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        //推送点亮消息
        if (($lightTime - $_SESSION['light_time']) > 3600) {
            $data = [
                'user_info' => [
                    'avatar' => $userInfo['avatar'],
                    'user_id' => $userInfo['user_id'],
                    'nice_name' => $userInfo['nickname'],
                    'level' => $userInfo['level'],
                    'vip_status' => $userInfo['vip_status'],
                    'guard_status' => self::guardStatus($user_id, $room_id),
                    'control_status' => self::controlStatus($user_id, $room_id, $userInfo),
                    'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                    'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
                    'deng_level'  =>$dengLevel
                ],
                'content' => '点亮了~'
            ];
            Gateway::sendToGroup($room_id, self::genMsg('lightMsg', '点亮了~', $data));
        }

        $_SESSION['light_time'] = $lightTime;
        Monitor::listen('light_after', $params);
    }


    // 用户分享主播(user_id为分享者)
    public static function sendShare(array $params)
    {
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];
        $userInfo = Common::getUserBasicInfo($user_id);
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        $data = [
            'user_info' => [
                'avatar' => $userInfo['avatar'],
                'user_id' => $userInfo['user_id'],
                'nice_name' => $userInfo['nickname'],
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_status'],
                'guard_status' => self::guardStatus($user_id, $room_id),
                'control_status' => self::controlStatus($user_id, $room_id, $userInfo),
                'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
                'deng_level'  =>$dengLevel
            ],
            'content' => '分享了主播，主播动力十足~',
        ];
        Gateway::sendToGroup($room_id, self::genMsg('shareMsg', '分享了主播，主播动力十足~', $data));
    }


    // 用户关注主播(user_id为关注者)
    public static function sendFollow(array $params)
    {
        $user_id = $params['user_id'];
        $room_id = $params['room_id'];
        $userInfo = Common::getUserBasicInfo($user_id);
        
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        //推送分享消息
        $data = [
            'user_info' => [
                'avatar' => $userInfo['avatar'],
                'user_id' => $userInfo['user_id'],
                'nice_name' => $userInfo['nickname'],
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_status'],
                'guard_status' => self::guardStatus($user_id, $room_id),
                'control_status' => self::controlStatus($user_id, $room_id, $userInfo),
                'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
                'deng_level'  =>$dengLevel
            ],
            'content' => '关注了主播，主播动力十足~',
        ];

        Gateway::sendToGroup($room_id, self::genMsg('followMsg', '关注了主播，主播动力十足~', $data));
        Monitor::listen('follow_after', $params);
    }


    // 禁言 主播/场控/超管/守护在直播间内禁言其它普通用户
    public static function sendShutup(array $params)
    {
        $room_id = $params['room_id'];
        $target_id = $params['target_id'];
        $userInfo = Common::getUserBasicInfo($target_id);
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        $data = [
            'user_info' => [
                'avatar' => $userInfo['avatar'],
                'user_id' => $userInfo['user_id'],
                'nice_name' => $userInfo['nickname'],
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_status'],
                'guard_status' => self::guardStatus($target_id, $room_id),
                'control_status' => self::controlStatus($target_id, $room_id, $userInfo),
                'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
                'deng_level'  =>$dengLevel
            ],
            'content' => '已被禁言~',
        ];
        Gateway::sendToGroup($room_id, self::genMsg('shutupMsg', '已被禁言~', $data));
    }


    // 用户被设置为场控后
    public static function sendControl(array $params)
    {
        $room_id = $params['room_id'];
        $target_id = $params['target_id'];
        $userInfo = Common::getUserBasicInfo($target_id);
        //推送分享消息
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchor_id);
        $dengLevel = self::dengLevel($anchor_id,$user_id);
        $data = [
            'user_info' => [
                'avatar' => $userInfo['avatar'],
                'user_id' => $userInfo['user_id'],
                'nice_name' => $userInfo['nickname'],
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_status'],
                'guard_status' => self::guardStatus($target_id, $room_id),
                'control_status' => (int)!$userInfo['isvirtual'],
                'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                'anchor_name' =>isset($anchor_info['nickname'])?$anchor_info['nickname']:'粉丝牌',
                'deng_level'  =>$dengLevel
            ],
            'content' => '已被设置为场控~',
        ];
        Gateway::sendToGroup($room_id, self::genMsg('controlMsg', '已被设置为场控~', $data));
    }


    // 踢人 主播/场控/超管/守护在直播间内踢出其它普通用户
    public static function sendKicking(array $params)
    {
        $room_id = $params['room_id'];
        $target_id = $params['target_id'];
        $data = [
            'room_id' => $room_id,
            'user_id' => $target_id,//被踢对象的uid
            'msg' => "抱歉，您已被踢出直播间~",
            'user_info' => [
                'user_id' => $target_id,
            ]
        ];

        $target_client = Gateway::getClientIdByUid($target_id);

        if (empty($target_client)) return;

        Gateway::sendToUid($target_id, self::genMsg('kicking', '抱歉，您已被踢出直播间~', $data));
        Gateway::closeClient($target_client[0]);
        $anchorId = MilletTools::getAnchorIdByRoomId($room_id);
        $anchor_info = self::getUserBasicInfo($anchorId);
        //推送踢人的动作消息
        $userInfo = Common::getUserBasicInfo($target_id);
        
        $dengLevel = self::dengLevel($anchorId,$user_id);
        $kickData = [
            'user_info' => [
                'avatar' => $userInfo['avatar'],
                'user_id' => $userInfo['user_id'],
                'nice_name' => $userInfo['nickname'],
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_status'],
                'guard_status' => self::guardStatus($target_id, $room_id),
                'control_status' => self::controlStatus($target_id, $room_id, $userInfo),
                'goodnum'  =>isset($userInfo['goodnum'])?$userInfo['goodnum']:null,
                'anchor_name' =>$anchor_info['nickname'],
                'deng_level'  =>$dengLevel
            ],
            'content' => '已被踢出直播间~',
        ];
        Gateway::sendToUid($anchorId, self::genMsg('kickingMsg', '已被踢出直播间~', $kickData));
        //减少房间人数
        self::setAudience($room_id, $target_id, 'exitRoom');
    }


    // 主播关闭直播间
    public static function close(array $params)
    {
        global $db, $redis;
        if (isset($params['pk_id']) && !empty($params['pk_id'])) Pk::abnormalEndPk(['pk_id' => $params['pk_id'], 'user_id' => $params['user_id']]);

        $data = ['type' => 1, 'room_id' => $params['room_id']];
        Gateway::sendToGroup($params['room_id'], self::genMsg('close', '主播已关播~', $data));
        Gateway::ungroup($params['room_id']);
        $row_count = $db->query("DELETE FROM " . TABLE_PREFIX . "live_goods WHERE room_id=" . $params['room_id']);
        $key = "livegoods:{$params['room_id']}";
        $redis->del($key);
        Monitor::listen('close_after', $params);
    }


    // 强制关闭直播间
    public static function superClose(array $params)
    {
        global $currentClient, $db, $redis;
        $msg = $params['msg'];
        $room_id = $params['room_id'];
        $data = ['room_id' => $room_id, 'msg' => $msg];
        isset($params['pk_id']) && !empty($params['pk_id']) && Pk::abnormalEndPk(['pk_id' => $params['pk_id'], 'user_id' => $params['user_id']]);
        Gateway::sendToGroup($room_id, self::genMsg('superClose', $msg, $data));
        Gateway::sendToCurrentClient(bin2hex(json_encode(['code' => 0, 'msg' => '关闭成功~'])));
        Gateway::closeClient($currentClient);
        $row_count = $db->query("DELETE FROM " . TABLE_PREFIX . "live_goods WHERE room_id=" . $params['room_id']);
        $key = "livegoods:{$params['room_id']}";
        $redis->del($key);
        Monitor::listen('close_after', $params);
    }


    // 主播暂停直播
    public static function change(array $params)
    {
        global $currentClient;
        $room_id = $params['room_id'];
        $data = $params['status'] == 0 ? ['content' => '主播离开一小会，马上就回来～'] : ['content' => '主播回来了，精彩继续～'];
        Gateway::sendToGroup($room_id, self::genMsg('systemMsg', 'ok', $data), [$currentClient]);
    }


    // 用户退出直播间
    public static function exitRoom(array $params)
    {
        global $currentClient;
        $room_id = $params['room_id'];
        $user_id = $params['user_id'];
        Gateway::leaveGroup($currentClient, $room_id);
        //减少房间人数
        self::setAudience($room_id, $user_id, 'exitRoom');
        Gateway::sendToCurrentClient(self::genMsg('exitRoom', 'ok', ['room_id' => $room_id]));
        Gateway::closeClient($currentClient);
        Monitor::listen('exit_room_after', $params);
    }


    // 强制下线(客户端强制下线)
    public static function kickRoom(array $params)
    {
        global $currentClient;
        $user_id = $params['user_id'];

        if (Gateway::isUidOnline($user_id)) {
            $msg = empty($params['msg']) ? '强制下线，账号已在其他客户端登录' : $params['msg'];
            Gateway::sendToUid($user_id, self::genMsg('kickRoom', $msg, ['msg' => $msg]));
        }

        Gateway::sendToCurrentClient(bin2hex(json_encode(['code' => 0, 'msg' => '关闭成功~'])));
        Gateway::closeClient($currentClient);
    }


    // 强制停用用户帐户功能
    public static function stopUserAccountDiscontinued(array $params)
    {
        global $currentClient;
        $user_id = $params['user_id'];

        if (Gateway::isUidOnline($user_id)) {
            $msg = empty($params['msg']) ? '您的账号涉嫌违规现已被强制停用~' : $params['msg'];
            Gateway::sendToUid($user_id, self::genMsg('kickRoom', $msg, ['msg' => $msg]));
        }

        Gateway::sendToCurrentClient(bin2hex(json_encode(['code' => 0, 'msg' => '关闭成功~'])));
        Gateway::closeClient($currentClient);
    }


    // 主播更改直播间类型
    public static function switchRoomMode(array $params)
    {
        global $db;
        $type = $params['type'] ? $params['type'] : 0;
        $room_id = $params['room_id'];
        $type_val = $params['type_val'] ? $params['type_val'] : 0;
        $data = [
            'type' => $type,
            'type_val' => $type_val,
            'content' => '',
        ];
        $room_type = ['普通', '私密', '付费', '计费', 'VIP', '等级'];
        $switch_type_msg = ['密码', '每场所收' . APP_BEAN_NAME, '每分钟所收' . APP_BEAN_NAME, '', '限制等级'];
        $sql = "select * from " . TABLE_PREFIX . "live WHERE id={$room_id} LIMIT 1";
        $result = $db->query($sql);

        if (empty($result)) Gateway::sendToCurrentClient(self::genMsg('switchMode', '直播间信息有误~', $data, 1));

        if ($result[0]['type'] == $type) Gateway::sendToCurrentClient(self::genMsg('switchMode', '直播间已是' . $room_type[$type] . '模式,无需更改', $data, 1));

        if ($type && $type != 4) {
            if (empty($type_val)) Gateway::sendToCurrentClient(self::genMsg('switchMode', '请设置' . $room_type[$type] . '直播模式' . $switch_type_msg[$type - 1], $data, 1));
        } else {
            $type_val = 0;
        }

        $res = $db->update(TABLE_PREFIX . 'live')->cols(['type' => $type, 'type_val' => $type_val])->where("id=" . $room_id)->query();

        if (!$res) Gateway::sendToCurrentClient(self::genMsg('switchMode', '设置错误,请重试~', $data, 1));

        switch ($type) {
            case 2 :
                //$exclude = self::isPay($room_id);
                $data['content'] = '主播已将房间设置为收费模式,' . $type_val . APP_BEAN_NAME . '/场';
                break;
            case 3 :
                $data['content'] = '主播已将房间设置为计费模式,' . $type_val . APP_BEAN_NAME . '/分钟';
                break;
            case 4 :
                //$exclude = self::vip($room_id);
                $data['content'] = '主播已将房间设置为VIP特权模式,VIP用户才能继续观看';
                break;
            case 5 :
                //$exclude = self::level($room_id, $type_val);
                $data['content'] = '主播已将房间设置为等级模式,' . $type_val . '级用户才能继续观看';
                break;
        }
        isset($exclude) ? Gateway::sendToGroup($room_id, self::genMsg('switchMode', '设置完成', $data), $exclude) : Gateway::sendToGroup($room_id, self::genMsg('switchMode', '设置完成', $data));
    }

    /**
     * 主播操作直播间商品
     * @param array $params
     * live_status 0表示添加商品 1表示讲解该商品 -1表示移除该直播商品 2表示取消讲解 3表示设置卖点 4表示置顶
     * goods_type 0：第三方商品；1：自营商品
     */
    public static function switchLiveGoods(array $params)
    {
        global $db;
        $room_id = $params['room_id'];
        $sql = "select * from " . TABLE_PREFIX . "live WHERE id={$room_id} LIMIT 1";
        $result = $db->query($sql);
        if (empty($result)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '直播间信息有误~', '', 1));
            return;
        }
        $goods_id = $params['goods_id'];
        $user_id = $params['user_id'];
        $live_status = isset($params['live_status']) ? $params['live_status'] : 0;

        $class = "app\\service\\LiveGoods";
        switch ($live_status) {
            case 0:
                $goods_sql = "select * from " . TABLE_PREFIX . "anchor_goods WHERE goods_id={$goods_id} and user_id={$user_id}  LIMIT 1";
                $goods_result = $db->query($goods_sql);
                if (empty($goods_result)) {
                    Gateway::sendToCurrentClient(self::genMsg('tipMsg', '商品错误~', '', 1));
                    return;
                }
                $params['anchor_id'] = $goods_result[0]['id'];
                if (class_exists($class)) {
                    if (method_exists($class, 'addGoods')) {
                        call_user_func_array([$class, 'addGoods'], [$params]);
                    }
                }
                break;
            case 1:
                if (class_exists($class)) {
                    if (method_exists($class, 'sayGoods')) {
                        call_user_func_array([$class, 'sayGoods'], [$params]);
                    }
                }
                break;
            case 2:
                if (class_exists($class)) {
                    if (method_exists($class, 'cancelSayGoods')) {
                        call_user_func_array([$class, 'cancelSayGoods'], [$params]);
                    }
                }
                break;
            case 3:
                if (class_exists($class)) {
                    if (method_exists($class, 'sellGoods')) {
                        call_user_func_array([$class, 'sellGoods'], [$params]);
                    }
                }
                break;
            case 4:
                if (class_exists($class)) {
                    if (method_exists($class, 'topGoods')) {
                        call_user_func_array([$class, 'topGoods'], [$params]);
                    }
                }
                break;
            case -1:
                if (class_exists($class)) {
                    if (method_exists($class, 'delGoods')) {
                        call_user_func_array([$class, 'delGoods'], [$params]);
                    }
                }
                break;
        }

        /*if (class_exists($class)) {
            if (method_exists($class, 'addLivegoods')) {
                call_user_func_array([$class, 'addLivegoods'], [$params]);
            }
        }*/
    }

    // socket内客户端支付
    public static function payByLive(array $params)
    {
        global $db, $redis;
        $type = $params['type'];
        $room_id = $params['room_id'];
        $roomType = [2, 3];
        $message = [
            'emit' => 'livePay',
            'code' => 0,
            'data' => [
                'type' => 0,
                'type_val' => 0,
            ],
            'msg' => '支付完成'
        ];
        $roomSql = 'select * from ' . TABLE_PREFIX . 'live where `id`=' . $room_id . ' limit 1';
        $room = $db->query($roomSql);

        if (empty($room)) {
            $message['code'] = 1;
            $message['msg'] = '直播间已关闭~';
            Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
            return;
        }

        if (!in_array($room[0]['type'], $roomType)) {
            $message['code'] = 1;
            $message['msg'] = '非付费直播间~';
            Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
            return;
        }

        if ($room[0]['type'] != $type) {
            $message['code'] = 1;
            $message['msg'] = '直播间信息有误~';
            Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
            return;
        }

        if ($room[0]['type'] == min($roomType)) {
            $isPay = $redis->zscore(self::$livePrefix . $room_id . ':PAY', $_SESSION['user_id']);

            if (!empty($isPay)) {
                $message['msg'] = '本次无需付费';
                Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
                return;
            }
        }

        if ($_SESSION['user_id'] == $room[0]['user_id']) {
            $message['code'] = 1;
            $message['msg'] = '不能给自己付款';
            Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
            return;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'to_uid' => $room[0]['user_id'],
            'totalFee' => $room[0]['type_val'],
            'room_id' => $room[0]['id'],
            'room_model' => $room[0]['room_model']
        ];
        $my_room_id = $_SESSION['group'];
        if ($type == 3) {
            $live_pay_timer = Timer::add(60, function ($data) use (&$live_pay_timer, $message, $my_room_id) {
                if (!Gateway::isUidOnline($data['user_id']) || !Gateway::isUidOnline($data['to_uid']) || $my_room_id  != $data['room_id']) {
                    Timer::del($live_pay_timer);
                    return;
                }
                
                $pay = LivePay::payment($data);

                if ($pay === false) {
                    Timer::del($live_pay_timer);
                    $message['code'] = LivePay::getCode();
                    $message['msg'] = LivePay::getMessage();
                    Gateway::sendToUid($data['user_id'], bin2hex(json_encode($message)));
                    return;
                }

            }, [$data], false);
        } else {
            $pay = LivePay::payment($data);

            if ($pay === false) {
                $message['code'] = LivePay::getCode();
                $message['msg'] = LivePay::getMessage();
                Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
                return;
            }
        }

        $type == min($roomType) && $redis->zadd(self::$livePrefix . $room_id . ':PAY', time(), $_SESSION['user_id']);
        $message['data']['type'] = $room[0]['type'];
        $message['data']['type_val'] = $room[0]['type_val'];
        Gateway::sendToCurrentClient(bin2hex(json_encode($message)));
    }


    // 添加机器人(计时器)
    public static function addRobot($room_id, $timer_id)
    {
        global $redis;
        $zombieTaskKey = self::$livePrefix . $room_id . ':robotTask';
        if (!empty($redis->llen($zombieTaskKey))) {
            $strData = $redis->rpop($zombieTaskKey);
            $data = json_decode($strData, true);
            $roomZombieKey = self::$livePrefix . $room_id . ':robot';
            $msgTemp = array(
                'emit' => 'enterMsg',
                'data' => array(
                    'type' => 0,//0入房提示消息
                    'user_info' => array(
                        'avatar' => $data['avatar'],
                        'user_id' => $data['zid'],
                        'nice_name' => $data['zname'],
                        'level' => $data['zlevel'],
                        'vip_status' => 0,
                        'guard_status' => 0,
                        'control_status' => 0,
                        'mount_url' => '',
                        'goodnum'  =>'',
                        'deng_level'  =>1
                    ),
                    'content' => '来捧场了~',
                ),
            );

            Gateway::sendToGroup($room_id, bin2hex(json_encode($msgTemp)));
            $redis->zadd($roomZombieKey, $data['zlevel'], $data['zid']);
        } else {
            $rs = Timer::del($timer_id);
            $rs && Logger::info('机器人添加完毕销毁定时器,timer_id=>' . $timer_id, 'destroyTimer');
        }
    }


    // 主播日常任务数据刷新(计时器)
    public static function anchorTask(array $params, $timer_id)
    {
        global $db, $redis;

        if (empty($params['current_client_id'])) {
            Logger::info('日常任务异常,timer_id=>' . $timer_id .', anchor_id=>'. $params['user_id'], 'taskError');
            return;
        }

        //如果主播已离线则删除计时器
        if (!Gateway::isOnline($params['current_client_id'])) {
            Timer::del($timer_id);
            Logger::info('主播日常任务毁定时器,timer_id=>' . $timer_id, 'destroyTimer');
        }

        $day = date('Ymd');
        $done = 0;
        $task_sql = "SELECT * FROM " . TABLE_PREFIX . "live_task WHERE `user_id`={$params['user_id']} AND `date_day`={$day} LIMIT 1";
        $live_sql = "SELECT * FROM " . TABLE_PREFIX . "live WHERE `id`={$params['room_id']} AND `status`=1 LIMIT 1";
        $task_info = $db->query($task_sql);
        $live_info = $db->query($live_sql);

        if (!empty($task_info[0]) && !empty($live_info[0])) {
            $task_setting = json_decode($task_info[0]['task_setting'], true);
            $now = time();
            $live_duration = $task_info[0]['live_duration'] + ($now - $live_info[0]['create_time']);
            $light_num = $task_info[0]['light_num'] + $redis->get("BG_LIVE:{$live_info[0]['id']}:like");
            $gift_profit = $task_info[0]['gift_profit'] + $redis->get("BG_LIVE:{$live_info[0]['id']}:incomeTotal");
            $new_fans = $task_info[0]['new_fans'] + $redis->zcount("fans:{$live_info[0]['user_id']}", $live_info[0]['create_time'], $now + 86400);
            $pk_win_num = $task_info[0]['pk_win_num'] + $redis->get("BG_LIVE:{$live_info[0]['id']}:pk_num");
            $live_duration_progress = round($live_duration / $task_setting['live_duration'], 2);
            $light_num_progress = round($light_num / $task_setting['light_num'], 2);
            $gift_profit_progress = round($gift_profit / $task_setting['gift_profit'], 2);
            $new_fans_progress = round($new_fans / $task_setting['new_fans'], 2);
            $pk_win_num_progress = round($pk_win_num / $task_setting['pk_win_num'], 2);
            /*$live_duration_progress = $live_duration_progress > 1 ? 1 : $live_duration_progress;
            $light_num_progress = $light_num_progress > 1 ? 1 : $light_num_progress;
            $gift_profit_progress = $gift_profit_progress > 1 ? 1 : $gift_profit_progress;
            $new_fans_progress = $new_fans_progress > 1 ? 1 : $new_fans_progress;
            $pk_win_num_progress = $pk_win_num_progress > 1 ? 1 : $pk_win_num_progress;
            $done = $live_duration_progress+$light_num_progress+$gift_profit_progress+$new_fans_progress+$pk_win_num_progress;*/
            $live_duration_progress >= 1 && $done++;
            $light_num_progress >= 1 && $done++;
            $gift_profit_progress >= 1 && $done++;
            $new_fans_progress >= 1 && $done++;
            $pk_win_num_progress >= 1 && $done++;
        }

        $task_data = [
            'emit' => 'taskLive',
//            'data' => ['done' => $done < 1 ? '1%' : (round($done/5, 1)*100).'%',]
            'data' => ['done' => $done]
        ];
        Gateway::sendToGroup($params['room_id'], bin2hex(json_encode($task_data)));
    }


    // 用于直播落地页h5链接
    public static function connectH5(array $params)
    {
        global $currentClient;
        $room_id = $params['room_id'];
        if (isset($_SESSION['auth_timer_id'])) {
            $rs = Timer::del($_SESSION['auth_timer_id']);
            $rs && Logger::info('webH5连接销毁定时器,timer_id=>' . $_SESSION['auth_timer_id'], 'destroyTimer');
        }
        self::removeHistoryGroup();
        if (!empty($room_id)) {
            Gateway::joinGroup($currentClient, $room_id);
            $_SESSION['group'] = $room_id;
        }
    }


    // 推送广播信息
    public static function pushBroadCast(array $params)
    {
        global $currentClient;
        Gateway::sendToAll(self::genMsg('adminSystemMsg', '', ['content' => $params['content']]));
        Gateway::closeClient($currentClient);
    }

    // 推送房间
    public static function pushBroadRoom(array $params)
    {
        global $currentClient;
        $room_id = $params['room_id'];
        $data = ['content' => $params['content']];
        Gateway::sendToGroup($room_id, self::genMsg('adminSystemMsg', 'ok', $data));
        Gateway::closeClient($currentClient);
    }


    // 推送中奖房间
    public static function pushLotteryRoom(array $params)
    {
        global $currentClient;
        Gateway::sendToAll(self::genMsg('adminSystemMsg', '', ['content' => $params['content']]));
        Gateway::closeClient($currentClient);
    }

    // 推送房间更改价格
    public static function pushBroadRoomChangePrice(array $params)
    {
        global $currentClient;
        $room_id = $params['room_id'];
        $data = ['content' => $params['content']];
        Gateway::sendToGroup($room_id, self::genMsg('shopChangePrice', 'ok', $data));
        Gateway::closeClient($currentClient);
    }

}