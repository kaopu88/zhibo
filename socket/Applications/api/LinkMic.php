<?php

namespace app\api;


use app\Common;
use app\service\MilletTools;
use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

class LinkMic extends Common
{

    //status=0 申请或受邀中还未同意
    //status=1 已建立连麦还未结束
    //status=2 已结束连麦
    //status=-1 拒绝连麦


    //正常结束、非正常结束、客户端突然退出房间处理、网络中断处理


    //验证当前连麦是否有效
    public static function verifyLinkMic(array $params)
    {
        global $db;

        if (empty($params['link_mic_id'])) return;

        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE id = ' . $params['link_mic_id'];

        $res = $db->query($sql);

        if ($res[0]['status'] == 1) return;

        $finish_data = [
            'link_mic_id' => $res[0]['id'],
            'link_mic_duration' => duration_format($res[0]['end_time']-$res[0]['create_time']),
            'link_audience' => $res[0]['audience'],
            'link_fans' => '0',
        ];

        Gateway::sendToCurrentClient(self::genMsg('finish_link_mic', '', ['content' => '已结束连麦~', 'link_mic_data' => $finish_data]));
    }


    //刷新主播端连麦申请
    protected static function refreshReply($room_id, $link_min_count=null, $is_remind=false)
    {
        global $db, $redis;

        $sql = 'SELECT user_id FROM '. TABLE_PREFIX .'link_mic_log WHERE status=0 AND room_id = ' . $room_id;

        $res = $db->query($sql);

        $data = [];

        if (!empty($res))
        {
            foreach ($res as $value)
            {
                $user_info = self::getUserBasicInfo($value['user_id']);

                $tmp = [
                    'avatar' => $user_info['avatar'],
                ];

                array_push($data, $tmp);
            }
        }

        $link_min_count === null && $link_min_count = self::linkMicCount($room_id);

        //当前房间内活跃的用户数量
        $active = $redis->zcard(self::$livePrefix.$room_id.':audience');

        $reply_data = [
            'reply_total' => count($res),
            'active_total' => (int)$active,
            'link_mic_total' => $link_min_count,
            'is_remind' => (int)$is_remind,
            'data' => $data,
        ];

        $anchor_id = MilletTools::getAnchorIdByRoomId($room_id);

        //向主播发送申请
        Gateway::sendToUid($anchor_id, self::genMsg('refresh_reply_linkmic', '', ['link_mic_data' => $reply_data]));

        return $res;
    }


    //用户申请连麦
    public static function reply(array $params)
    {
        global $db, $redis;

        $users = self::getUserBasicInfo($params['user_id']);

        if ($redis->exists('LinkMic:roomid:'. $params['room_id'])) {
            $status = $redis->get('LinkMic:roomid:'. $params['room_id']);
            if ($status == '0') {
                Gateway::sendToCurrentClient(self::genMsg('tipMsg', '主播未开启连麦', [], 1));
                return;
            }
        }

        if ($users['level'] < MIKE_LEVEL) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '等级未达' . MIKE_LEVEL . '级，无法开启连麦功能~', [], 1));
            return;
        }


        if (self::checkIsPking($params['room_id'])) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', 'PK中不能发起连麦请求~', [], 1));
            return;
        }

        $link_min_count = self::linkMicCount($params['room_id']);

        if ($link_min_count >= 2) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '最多支持2方在线连麦~', [], 1));
            return;
        }

        //查看本人当前是否已申请
        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=0 AND room_id = ' . $params['room_id'].' AND user_id='.$params['user_id'].' limit 1';
        $res = $db->query($sql);

        if (!empty($res)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '正在等待主播接受~', [], 1));
            return;
        }

        //写入申请表
        $reply_id = $db->insert(TABLE_PREFIX . 'link_mic_log')->cols([
            'user_id' => $params['user_id'],
            'room_id' => $params['room_id'],
            'create_time' => time(),
            'type' => $params['type']

        ])->query();

        self::refreshReply($params['room_id'], $link_min_count, true);

        //超时拒绝(用户端session内)
        $_SESSION['reply_over_time'] = Timer::add(60, [__CLASS__, 'overTime'], [[
            'link_mic_id' => $reply_id,
            'room_id'=>$params['room_id'],
            'target_id' => $params['user_id'],
            'content' => '主播未响应你的连麦请求~'
        ]], false);

        Gateway::sendToCurrentClient(self::genMsg('tipMsg', '连麦请求已发送，等待主播处理~', [], 1));
    }


    //主播邀请连麦
    public static function invite(array $params)
    {
        global $db;

        $users = self::getUserBasicInfo($params['invite_uid']);

        if ($users['level'] < MIKE_LEVEL) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '对方等级未达' . MIKE_LEVEL . '级，未开启连麦功能~', [], 1));
            return;
        }

        if (self::checkIsPking($params['room_id'])) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', 'PK中不能发起连麦邀请~', [], 1));
            return;
        }

        if (!Gateway::isUidOnline($params['invite_uid'])) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '对方不在线~', [], 1));
            return;
        }

        $link_min_count = self::linkMicCount($params['room_id']);

        if ($link_min_count >= 2) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '最多支持2方在线连麦~', [], 1));
            return;
        }

        //目标用户是否已申请中
        $sql = 'SELECT user_id FROM '. TABLE_PREFIX .'link_mic_log WHERE status=0 AND room_id=' . $params['room_id'] . ' AND user_id='.$params['invite_uid'];
        $res = $db->query($sql);

        if (!empty($res)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '对方正在申请连麦中~', [], 1));
            return;
        }

        //目标用户是否已连麦中
        $sql2 = 'SELECT user_id FROM '. TABLE_PREFIX .'link_mic_log WHERE status=1 AND room_id=' . $params['room_id'] . ' AND user_id='.$params['invite_uid'];

        $res2 = $db->query($sql2);

        if (!empty($res2)) {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '对方正在连麦中~', [], 1));
            return;
        }

        $anchor_id = MilletTools::getAnchorIdByRoomId($params['room_id']);

        //写入申请表
        $invite_id = $db->insert(TABLE_PREFIX . 'link_mic_log')->cols([
            'user_id' => $params['invite_uid'],
            'room_id' => $params['room_id'],
            'create_time' => time(),
            'is_invite' => 1,
            'type' => $params['type']

        ])->query();

        //向用户发送邀请
        Gateway::sendToUid($params['invite_uid'], self::genMsg('invite_link_mic', '', ['content' => '主播邀请你连麦~', 'link_mic_data' => ['link_mic_id' =>  $invite_id]]));

        //超时拒绝(主播端session内)
        $_SESSION['invite_over_time'] = Timer::add(60, [__CLASS__, 'overTime'], [[
            'link_mic_id' => $invite_id,
            'room_id'=>$params['room_id'],
            'target_id' => $anchor_id,
            'content' => '用户未响应你的连麦邀请~'
        ]], false);
    }


    //同意
    public static function allow(array $params)
    {
        global $db, $config;

        $link_min_count = self::linkMicCount($params['room_id']);

        if ($link_min_count >= 2)
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '最多支持2方在线连麦~', [], 1));

            return;
        }

        //连麦id
        //房间号
        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=0 AND room_id=' . $params['room_id'] . ' AND id='.$params['link_mic_id'] .' limit 1';

        $res = $db->query($sql);

        if (empty($res))
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '连麦出错，请确认参数~', [], 1));

            return;
        }

        if (!Gateway::isUidOnline($res[0]['user_id']))
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '对方已下线~', [], 1));

            $db->update(TABLE_PREFIX . 'link_mic_log')
                ->cols(['status'=>-1, 'end_time' => time(), 'audience' => 0])
                ->where('room_id='.$params['room_id'].' and id='.$params['link_mic_id'])
                ->query();

            return;
        }

        /*$anchor_id = MilletTools::getAnchorIdByRoomId($params['room_id']);

        //删除超时定时器
        if ($anchor_id == $params['user_id'])
        {
            //是主播同意，说明是用户申请则删除用户端超时定时器
            $clients = Gateway::getClientIdByUid($res[0]['user_id']);

            $session =& Gateway::getSession($clients[0]);

            isset($session['reply_over_time']) && Timer::del($session['reply_over_time']);
        }
        else{
            //是用户同意，说明是主播邀请连麦则删除主播端超时定时器
            $clients = Gateway::getClientIdByUid($anchor_id);

            $session =& Gateway::getSession($clients[0]);

            isset($session['invite_over_time']) && Timer::del($session['invite_over_time']);
        }*/

        //获取各方推播流地址
        $drive_config = $config['live'];

        $class = '\\app\\service\\liveDrive\\'.ucfirst($config['live']['platform']);

        if(!class_exists($class))
        {
            Gateway::sendToCurrentClient(self::genMsg('tipMsg', '连麦出错[1]', [], 1));

            return;
        }

        $LiveDrive = new $class($drive_config);

        $stream_suffix = 'linkMic-' . $res[0]['user_id'] . '_' . $res[0]['create_time'];

        $users = self::getUserBasicInfo($res[0]['user_id']);

        //组装连麦数据
        $link_mic_data = [
            'link_mic_id' => $params['link_mic_id'],
            'user_id' => $res[0]['user_id'],
            //用户端播流地址
            'user_pull' => call_user_func_array([$LiveDrive, 'buildPullUrl'], [$drive_config['pull_protocol'], $stream_suffix]),
            //用户端的推流地址
            'user_push' => call_user_func_array([$LiveDrive, 'buildPushUrl'], [$stream_suffix]), //生成主播端推流地址
            'avatar' => $users['avatar']
        ];

        $LiveDrive = null;

        //变更状态
        $db->update(TABLE_PREFIX . 'link_mic_log')
            ->cols([
                'status'=>1,
                'push'=>$link_mic_data['user_push'],
                'pull'=>$link_mic_data['user_pull'],
                'stream' => $drive_config['stream_prefix'].$stream_suffix
            ])
            ->where('room_id='.$params['room_id'].' and id='.$params['link_mic_id'])
            ->query();

        Gateway::sendToGroup($params['room_id'], self::genMsg('build_link_mic', '', ['link_mic_data' => $link_mic_data]));

        self::refreshReply($params['room_id'], $link_min_count);
    }


    //拒绝
    public static function deny(array $params)
    {
        global $db;

        //连麦id
        //房间号
        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=0 AND room_id=' . $params['room_id'] . ' AND id='.$params['link_mic_id'] .' limit 1';

        $res = $db->query($sql);

        if (empty($res))
        {
            Gateway::sendToUid($params['user_id'], self::genMsg('tipMsg', '操作出错，请确认参数~', [], 1));

            return;
        }

        $db->update(TABLE_PREFIX . 'link_mic_log')
            ->cols(['status'=>-1, 'end_time' => time(), 'audience' => 0])
            ->where('room_id='.$params['room_id'].' and id='.$params['link_mic_id'])
            ->query();

        //用户拒绝发给主播，主播拒绝发给用户
        $target_id = $params['user_id'] == $res[0]['user_id']
            ? MilletTools::getAnchorIdByRoomId($res[0]['room_id'])
            : $res[0]['user_id'];

        Gateway::sendToUid($target_id, self::genMsg('deny_link_mic', '', ['content' => '对方拒绝了你的连麦请求~']));

        self::refreshReply($params['room_id']);
    }


    //超时处理
    public static function overTime(array $params)
    {
        global $db;

        //连麦id
        //房间号
        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=0 AND room_id=' . $params['room_id'] . ' AND id='.$params['link_mic_id'] .' limit 1';

        $res = $db->query($sql);

        if (empty($res)) return;

        $db->update(TABLE_PREFIX . 'link_mic_log')
            ->cols(['status'=>-1, 'end_time' => time(), 'audience' => 0])
            ->where('room_id='.$params['room_id'].' and id='.$params['link_mic_id'])
            ->query();

        Gateway::sendToUid($params['target_id'], self::genMsg('deny_link_mic', '', ['content' => $params['content']]));

        self::refreshReply($params['room_id']);
    }


    //结束
    public static function finish(array $params)
    {
        global $db;
        //连麦id
        //房间号
        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=1 AND room_id=' . $params['room_id'] . ' AND id='.$params['link_mic_id'] .' limit 1';

        $res = $db->query($sql);

        if (empty($res))
        {
            Gateway::sendToUid($params['user_id'], self::genMsg('tipMsg', '操作出错，请确认参数~', [], 1));

            return;
        }

        $finish_update = [
            'status'=>2,
            'end_time'=>time(),
            'audience' => self::getLinkMicAudience($params['room_id'], $params['link_mic_id']),
        ];

        $db->update(TABLE_PREFIX . 'link_mic_log')
            ->cols($finish_update)
            ->where('room_id='.$params['room_id'].' and id='.$params['link_mic_id'])
            ->query();

        $finish_data = [
            'link_mic_id' => $params['link_mic_id'],
            'link_mic_duration' => duration_format($finish_update['end_time']-$res[0]['create_time']),
            'link_audience' => $finish_update['audience'],
            'link_fans' => self::getLinkMicFans($res[0]['create_time'], $res[0]['user_id']),
        ];

        Gateway::sendToGroup($params['room_id'], self::genMsg('finish_link_mic', '', ['content' => '已结束连麦~', 'link_mic_data' => $finish_data]));

        self::refreshReply($params['room_id']);
    }


    //主播关播
    public static function endLinkMicByAnchor(array $params)
    {
        global $db, $redis;
        $redis->del('LinkMic:roomid:'. $params['room_id']);

        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=1 AND room_id=' . $params['room_id'];

        $build_link_mic = $db->query($sql);

        if (!empty($build_link_mic))
        {
            //处理每组未结束的连麦
            foreach ($build_link_mic as $link_mic)
            {
                $finish_update = [
                    'status'=>2,
                    'end_time'=>time(),
                    'audience' => self::getLinkMicAudience($params['room_id'], $link_mic['id']),
                ];

                $db->update(TABLE_PREFIX . 'link_mic_log')
                    ->cols($finish_update)
                    ->where('room_id='.$params['room_id'].' and id='.$link_mic['id'])
                    ->query();
            }
        }

        //处理申请未拒绝的连麦
        $db->update(TABLE_PREFIX . 'link_mic_log')
            ->cols(['status' => 2, 'end_time'=>time()])
            ->where('room_id='.$params['room_id'].' and status=0')
            ->query();

        return true;
    }


    //用户退出
    public static function endLinkMicByUser(array $params)
    {
        global $db;

        $sql = 'SELECT * FROM '. TABLE_PREFIX .'link_mic_log WHERE status=1 AND room_id=' . $params['room_id'] . ' AND user_id='.$params['user_id'] .' limit 1';

        $build_link_mic = $db->query($sql);

        if (empty($build_link_mic)) return true;

        //处理未结束的连麦
        $finish_update = [
            'status'=>2,
            'end_time'=>time(),
            'audience' => self::getLinkMicAudience($params['room_id'], $build_link_mic[0]['id']),
        ];

        $db->update(TABLE_PREFIX . 'link_mic_log')
            ->cols($finish_update)
            ->where('room_id='.$params['room_id'].' and id='.$build_link_mic[0]['id'])
            ->query();

        $finish_data = [
            'link_mic_id' => $build_link_mic[0]['id'],
            'link_mic_duration' => duration_format($finish_update['end_time']-$build_link_mic[0]['create_time']),
            'link_audience' => $finish_update['audience'],
            'link_fans' => self::getLinkMicFans($build_link_mic[0]['create_time'], $params['user_id']),
        ];

        Gateway::sendToGroup($params['room_id'], self::genMsg('finish_link_mic', '', ['content' => '结束了连麦~', 'link_mic_data' => $finish_data]));

        self::refreshReply($params['room_id']);

        return true;
    }


    //获取连麦的观看人数
    protected static function getLinkMicAudience($room_id, $link_mic_id)
    {
        global $redis;

        $key = 'BG_LIVE:'.$room_id.':linkMic'.$link_mic_id.'audience';

        $num = $redis->get($key);

        return (string)((int)$num);
    }


    //获取连麦期间新增粉丝数
    protected static function getLinkMicFans($start_time, $user_id)
    {
        global $db;

        $sql = 'SELECT count(id) num FROM '. TABLE_PREFIX .'follow WHERE follow_id=' . $user_id . ' AND create_time >='.$start_time .' limit 1';

        $fans = $db->query($sql);

        if (empty($fans)) return '0';

        return $fans[0]['num'];
    }


    //统计正在连麦的数量
    protected static function linkMicCount($room_id)
    {
        global $db;

        //获取当前已建立连麦的数量
        $sql = 'SELECT count(id) num FROM '. TABLE_PREFIX .'link_mic_log WHERE status=1 AND room_id = ' . $room_id;

        $link_min_count = $db->query($sql);

        return $link_min_count[0]['num'];
    }


    //检查是否正在Pk中
    protected static function checkIsPking($room_id)
    {
        global $db;

        //获取当前已建立连麦的数量
        $sql = 'SELECT id FROM '. TABLE_PREFIX .'live_pk WHERE status=0 AND active_room_id = ' . $room_id;

        $active_pk = $db->query($sql);

        if (!empty($active_pk)) return true;

        $sql = 'SELECT id FROM '. TABLE_PREFIX .'live_pk WHERE status=0 AND target_room_id = ' . $room_id;

        $target_pk = $db->query($sql);

        if (!empty($target_pk)) return true;

        return false;
    }


}