<?php

namespace app;

use app\service\DateTools;
use app\service\MilletTools;
use app\service\Monitor;
use app\service\User;
use GatewayWorker\Lib\Gateway;


class Common
{

    protected static $livePrefix = 'BG_LIVE:';

    protected static $pk_topic = '未设置';


    //生成统一格式消息
    public static function genMsg($emit, $msg, $data=[], $code=0)
    {
        $json = json_encode(['emit' => $emit, 'msg' => $msg, 'code' => $code, 'data' => $data]);

        return bin2hex($json);
    }


    //踢出历史连接
    protected static function removeHistoryClient($user_id)
    {
        global $currentClient;

        $clients = Gateway::getClientIdByUid($user_id);

        if (!empty($clients))
        {
            foreach ($clients as $clid)
            {
                if ($clid != $currentClient)
                {
                    Gateway::closeClient($clid);
                }
            }
        }
    }


    //踢出历史组
    protected static function removeHistoryGroup()
    {
        global $currentClient;

        if (!empty($_SESSION['group']))
        {
            Gateway::leaveGroup($currentClient, $_SESSION['group']);
        }
    }


    //验证是否为主播的守护
    protected static function guardStatus($user_id, $room_id, $userInfo=[])
    {
        global $redis;
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $is_status = $redis->zscore('BG_GUARD:'.$anchor_id, $user_id);
        return empty($is_status) ? 0 : 1;
    }


    //验证是否为主播的场控
    protected static function controlStatus($user_id, $room_id, $userInfo=[])
    {
//        if($userInfo && $userInfo['isvirtual'] == 1) return 0;
        global $redis;
        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);
        $is_status = $redis->sismember('liveManage:'.$anchor_id, $user_id);
        return (int)$is_status;
    }


    //操作直播间观众数
    protected static function setAudience($room_id, $visitorId, $type = 'enter', $level=1)
    {
        global $redis;

        $audienceKey = self::$livePrefix.$room_id.':audience';//直播间实时观众UID集合

        $totalAudNumKey = self::$livePrefix.$room_id.':allNum';//直播间总计观看人数

        $onlineKey = 'cache:online:'.date('Ymd'); //统计当日APP在线人数（未完善）

        if($type == 'enter')
        {
            $redis->zadd($audienceKey, $level, $visitorId);

            $redis->incr($totalAudNumKey);

            $redis->incr($onlineKey);
        }else if($type == 'exitRoom'){
            $redis->zrem($audienceKey, $visitorId);

            $redis->decr($onlineKey);
        }
        self::getAudienceList($room_id);
    }

    protected static function getAudienceList($room_id)
    {
        global $redis;
        $rs = [];
        $num = 30;
        $audienceKey = self::$livePrefix.$room_id.':audience';
        $audiens = $redis->zrevrange($audienceKey, 0, $num-1, 1);
        if (!empty($audiens)) {
            $userIds = array_keys($audiens);
            foreach ($userIds as $key =>$userid) {
                $user_info = $redis->get('user:'.$userid);
                if (empty($user_info)) continue;
                $user_info = json_decode($user_info, true);
                $rs[] = [
                    'user_id' => $user_info['user_id'],
                    'avatar' => $user_info['avatar'],
                    'nickname' => $user_info['nickname'],
                    'level' => $user_info['level'],
                    'gender' => $user_info['gender'],
                ];
            }
        }

        if (empty($audiens) || count($audiens) < $num) {
            $robotKey = self::$livePrefix.$room_id.':robot';
            $robot = [];
            $robots_num = $num-count($audiens);
            $robotList = $redis->zrevrange($robotKey, 0, $robots_num-1, 1);
            foreach ($robotList as $robot_id=>$robot_level) {
                $robot_info = $redis->get('robot:'.$robot_id);
                if (empty($robot_info)) continue;
                $robot_info = json_decode($robot_info, true);

                $robot[] = [
                    'user_id' => $robot_id,
                    'avatar' => $robot_info['avatar'],
                    'nickname' => $robot_info['nickname'],
                    'level' => $robot_level,
                    'gender' => rand(0,1),
                ];
            }
            $rs = array_merge($rs, $robot);
        }
        Gateway::sendToGroup($room_id, self::genMsg('allAudienceList', 'ok', $rs));
    }


    protected static function sendGiftMsgToRoom($room_id, $gift_info, $user_info, $anchor_info)
    {
        global $redis;

        // $weekNum = "anchor_millet:w:".DateTools::getWeekNum();
        $dayNum = "anchor_millet:d:".date('Ymd');

        $anchor_income = $redis->zscore($dayNum, $anchor_info['user_id']);

        $user_info_data = [
            'user_id'=> $user_info['user_id'],
            'nice_name'=> $user_info['nickname'],
            'avatar'=> $user_info['avatar'],
            'level' => $user_info['level'],
            'vip_status' => $user_info['vip_expire'] > time() ? 1 : 0,
            'guard_status' => self::guardStatus($user_info['user_id'], $room_id),
            'control_status' => self::controlStatus($user_info['user_id'], $room_id, $user_info),
        ];

        $showGiftData = [
            'gift_info' =>[
                'id' => $gift_info['id'],
                'name' => $gift_info['name'],
                'icon' => $gift_info['picture_url'],
                'type' => $gift_info['type'],
                'show_params' => !empty($gift_info['show_params']) ? json_decode($gift_info['show_params'], true) : '',
            ],
            'user_info'=> $user_info_data,
            'anchor_info' => [
                'avatar' => $anchor_info['avatar'],
                'anchor_income' => (string)($anchor_income+0),
            ],
            'anchor_income' => (string)($anchor_income+0),
            /**
             * $anchor_income+0
             * 作用为在使用道具的情况下，主播收益可能为0
             * $anchor_income则为false
             *
             */
        ];

        //发送礼物展示数据
        Gateway::sendToGroup($room_id, self::genMsg('showGift', 'ok', $showGiftData));
    }



    //赠送礼物后消息
    protected static function sendGiftMsg($room_id, $gift_info, $user_info, $anchor_info)
    {
        global $redis, $config, $currentClient;

        // $weekNum = "anchor_millet:w:".DateTools::getWeekNum();
        $dayNum = "anchor_millet:d:".date('Ymd');

        $anchor_income = $redis->zscore($dayNum, $anchor_info['user_id']);

        $user_info_data = [
            'user_id'=> $user_info['user_id'],
            'nice_name'=> $user_info['nickname'],
            'avatar'=> $user_info['avatar'],
            'level' => $user_info['level'],
            'vip_status' => $user_info['vip_expire'] > time() ? 1 : 0,
            'guard_status' => self::guardStatus($user_info['user_id'], $room_id),
            'control_status' => self::controlStatus($user_info['user_id'], $room_id, $user_info),
        ];
        $gift_ids = json_decode($redis->get('BG_GIFT:guard_all'), true);
        $is_guard = (!empty($gift_ids) && in_array($gift_info['id'], $gift_ids)) ? 1 : 0;

        $showGiftData = [
            'gift_info' =>[
                'id' => $gift_info['id'],
                'name' => $gift_info['name'],
                'icon' => $gift_info['picture_url'],
                'type' => $gift_info['type'],
                'append' => ['is_guard' => $is_guard],
                'show_params' => !empty($gift_info['show_params']) ? json_decode($gift_info['show_params'], true) : '',
            ],
            'user_info'=> $user_info_data,
            'anchor_info' => [
                'avatar' => $anchor_info['avatar'],
                'anchor_income' => (string)($anchor_income+0),
            ],
            'anchor_income' => (string)($anchor_income+0),
            /**
             * $anchor_income+0
             * 作用为在使用道具的情况下，主播收益可能为0
             * $anchor_income则为false
             *
             */
        ];

        //发送礼物展示数据
        Gateway::sendToGroup($room_id, self::genMsg('showGift', 'ok', $showGiftData), [$currentClient]);
        Gateway::sendToCurrentClient(self::genMsg('showGift', 'ok', $showGiftData));

        //推送大礼物信息
        if($gift_info['type'] == 0)
        {
            $ct = '送了一个礼物 '.$gift_info['name'];

            Gateway::sendToGroup($room_id, self::genMsg('giftMsg', $ct, ['user_info'=> $user_info_data, 'content' => $ct]), [$currentClient]);
            Gateway::sendToCurrentClient(self::genMsg('giftMsg', $ct, ['user_info'=> $user_info_data, 'content' => $ct]));
        }

        //如果礼物价值超过5000则全局广播
        if($gift_info['price'] >= 5000)
        {
            $bcMsg = sprintf('%s 给 %s 送了1个 %s, 引爆全场~', $user_info['nickname'], $anchor_info['nickname'], $gift_info['name']);

            $broad_data = [
                'to_user_id' => $anchor_info['user_id'],
                'to_nickname' => $anchor_info['nickname'],
                'to_avatar' => $anchor_info['avatar'],
                'user_id'=> $user_info['user_id'],
                'nice_name'=> $user_info['nickname'],
                'avatar'=> $user_info['avatar'],
                'bc_msg' => "送了1个 {$gift_info['name']}, 引爆全场~",
            ];

            //1大礼物消费 2游戏高额投注获胜 3其它
            $redis->lpush('broadCasts', self::genMsg('broadCast', $bcMsg, ['type'=> 1, 'user_info' => $broad_data, 'msg' => $bcMsg]));
        }
    }


    //过滤已是VIP的终端用户
    protected static function vip($group)
    {
        $exclude = [];

        $group_all = Gateway::getClientSessionsByGroup($group);

        foreach ($group_all as $client_id => $session)
        {
            if ($session['user_identity'] == 'user')
            {
                if ($session['user_vip_status'] != 1) continue;

                array_push($exclude, $client_id);
            }
        }

        return $exclude;
    }



    //过滤已符合等级的终端用户
    protected static function level($group, $level)
    {
        $exclude = [];

        $group_all = Gateway::getClientSessionsByGroup($group);

        foreach ($group_all as $client_id => $session)
        {
            if ($session['user_identity'] == 'user')
            {
                if ($session['user_level'] < $level) continue;

                array_push($exclude, $client_id);
            }
        }

        return $exclude;
    }


    //格式化数据量
    protected static function formatData(&$data, $field = [])
    {
        if (is_array($data) && !empty($field) && is_array($field)) {
            foreach ($field as $key => $val) {
                $data[$val] = self::formatData($data[$val]);
            }
        } else {
            if ($data >= 10000) {
                $real = sprintf("%.2f", $data / 10000);

                $data = $real . 'w';
            }
        }
        return $data;
    }


    //过滤已完成支付的终端用户
    protected static function isPay($group)
    {
        global $redis;

        $exclude = [];

        $group_all = Gateway::getClientSessionsByGroup($group);

        foreach ($group_all as $client_id => $session)
        {
            if ($session['user_identity'] == 'user')
            {
                $isPay = $redis->zscore(self::$livePrefix.$group.':PAY', $session['user_id']);

                if (!empty($isPay)) array_push($exclude, $client_id);
            }
        }

        return $exclude;
    }


    //获取用户基础信息
    public static function getUserBasicInfo($user_id)
    {
        global $redis;

        $users = $redis->get('user:'.$user_id);

        if (empty($users)) return [];

        $users = json_decode($users, true);

        $users['vip_status'] = $users['vip_expire'] > time() ? 1 : 0;

        return $users;
    }


    //获取主播Pk信息
    protected static function getPkAnchorInfo($pk_id)
    {
        global $db;

        $sql = "SELECT * FROM ".TABLE_PREFIX."live_pk WHERE `status`=0 AND `id`={$pk_id} LIMIT 1";

        $res = $db->query($sql);
        if (empty($res)) return false;
        return $res[0];
    }


    //完成pk更新数据
    protected static function completePk($pk_id, $pk_res, $ending_method = 0, $end_user_id = 0)
    {
        if (empty($pk_id)) return;

        global $db, $redis;

        $update = ['pk_end_time' => time(), 'pk_res' => $pk_res, 'status'=>1, 'ending_method'=> $ending_method, 'end_user_id'=> $end_user_id];

        MilletTools::updatePkScore($pk_id, $update);

        $sql = "SELECT * FROM ".TABLE_PREFIX."live_pk WHERE `status`=1 AND `id`={$pk_id} LIMIT 1";

        $pk_info = $db->query($sql);

        if (!empty($pk_info))
        {
            //主播任务pk胜场数据
            $room_id = $pk_res == 1 ? $pk_info[0]['active_room_id'] : $pk_info[0]['target_room_id'];

            $redis->incr(self::$livePrefix.$room_id.':pk_num');
        }

        Monitor::listen('complete_pk_after', $pk_info[0]);
    }


    //赠送礼物时pk更新
    protected static function giftPkUpdate(array $params, $sendCost)
    {
        global $redis;
        if (empty($params['pk_id']) || empty($params['room_id'])) return true;

        $pk_info = self::getPkAnchorInfo($params['pk_id']);

        //当前送礼物时间在pk中
        if (!empty($pk_info) && time() <= $pk_info['pk_start_time']+$pk_info['pk_duration'])
        {
            $active_energy = $pk_info['active_income'];
            $target_energy = $pk_info['target_income'];

            //写入pk打赏榜单
            $key = self::$livePrefix .'pk_rank_' . $params['pk_id']. '_' .$params['room_id'];
            $redis->zIncrBy($key, $sendCost, $params['user_id']);

            if ($params['room_id'] == $pk_info['active_room_id']) {
                $active_energy = $active_energy+$sendCost;
                $update = ['active_income'=>$active_energy];
            } else {
                $target_energy = $target_energy+$sendCost;
                $update = ['target_income'=>$target_energy];
            }

            $active_user = $redis->zRevRange(self::$livePrefix .'pk_rank_' . $params['pk_id']. '_' . $pk_info['active_room_id'], 0, 3, true);
            $target_user = $redis->zRevRange(self::$livePrefix .'pk_rank_' . $params['pk_id']. '_' . $pk_info['target_room_id'], 0, 3, true);
            $active_user_detail = [];
            $target_user_detail = [];

            if (!empty($active_user)) {
                foreach ($active_user as $user_id => $user_score) {
                    $user_info = self::getUserBasicInfo($user_id);
                    $active_user_detail[] = [
                        'coin' => $user_score,
                        'user_id' => $user_id,
                        'avatar' => $user_info['avatar'],
                        'nickname' => $user_info['nickname']
                    ];
                }
            }

            if (!empty($target_user)) {
                foreach ($target_user as $user_id => $user_score) {
                    $user_info = self::getUserBasicInfo($user_id);
                    $target_user_detail[] = [
                        'coin' => $user_score,
                        'user_id' => $user_id,
                        'avatar' => $user_info['avatar'],
                        'nickname' => $user_info['nickname']
                    ];
                }
            }

            MilletTools::updatePkScore($params['pk_id'], $update);
            $params['pk_type'] = $pk_info['pk_type'];
            Monitor::listen('update_pk_income_after', $params);
            $energy = round(($active_energy+50)/($active_energy+$target_energy+100), 2);

            if ($energy < 0.05 || $energy > 0.95) $energy = $energy < 0.05 ? 0.05 : 0.95;

            $pk_energy = [
                'active_energy' => $active_energy,
                'target_energy' => $target_energy,
                'active_rank' => $active_user_detail,
                'target_rank' => $target_user_detail,
                'active_info' => ['user_id' => $pk_info['active_id']],
                'energy' => $energy,
            ];

            Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('updatePkEnergy', 'ok', $pk_energy));
            Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('updatePkEnergy', 'ok', $pk_energy));
        }

        return $pk_info;
    }
    //查缓存,没有就重新定义
    public static function dengLevel($anchor_id,$user_id)
    {
        global $db, $redis;
        $dengLevelKey = 'DengLevel:'.$anchor_id.'_'.$user_id;
        $dengLevel = $redis->get($dengLevelKey);
        if($dengLevel===null){
            $count = $db->query("SELECT SUM(millet) as count FROM bx_kpi_millet WHERE get_uid = $anchor_id AND cont_uid = $user_id");
            $mcount = $count[0]['count'];
            $dengLevel = 0;
            if(!empty($mcount)){
                $arr = $db->query("SELECT * FROM bx_deng_level WHERE level_up <= $mcount ORDER BY level_up DESC");
                $dengLevel = $arr[0]['name'];
            }
            $redis->set($dengLevelKey,$dengLevel);
        }
        return $dengLevel;
    }
}