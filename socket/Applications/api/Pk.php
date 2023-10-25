<?php

namespace app\api;

use app\service\Logger;
use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
use app\Common;



class Pk extends Common
{
    //pk默认时长, 交流惩罚时间, 动画展示延迟时间, 房间内观众延迟发送时间
    protected static $delay_time = 9, $audience_delay_time = 3;

    //pk请求回复超时时间
    protected static $match_over_time = 30, $random_over_time = 30;

    protected static $active = 'active:', $target = 'target:';

    protected static $pk_prefix = 'BG_PK:', $pk_lock = 'pking:', $match_lock = 'match:', $timer = 'timer:', $random = 'random', $pk_option = 'config:pk_option';

    protected static $pk_rank_activity = 'pk_rank';


    //pk排位赛-入口
    protected static function pkRank(array $params)
    {
        global $redis;

        //当前用户已在池内则不做回应
        if ($redis->sismember(self::$pk_prefix.self::$pk_rank_activity, $params['user_id']) == 1) return;

        //随机从池内提取一个用户匹配
        $target_id = $redis->spop(self::$pk_prefix.self::$pk_rank_activity);

        if (empty($target_id))
        {
            //将自已加入匹配池内
            $redis->sadd(self::$pk_prefix.self::$pk_rank_activity, $params['user_id']);

            //开启随机超时计时器
            Timer::add(self::$random_over_time, [__CLASS__, 'pkRankOverTime'], [$params['user_id']], false);

            Logger::info('pk随机匹配超时定时器(一次性)', 'addTimer');
        }
        else if ($target_id != $params['user_id'])
        {
            //将对方加匹配锁
            self::matchAddLock($target_id);

            //开启pk
            self::startPk(['active_id'=>$params['user_id'], 'target_id'=>$target_id, 'pk_type' => $params['pk_type']]);
        }
    }


    //正常随机pk-入口
    protected static function systemRandom(array $params)
    {
        global $redis;

        //当前用户已在池内则不做回应
        if ($redis->sismember(self::$pk_prefix.self::$random, $params['user_id']) == 1) return;

        //随机从池内提取一个用户匹配
        $target_id = $redis->spop(self::$pk_prefix.self::$random);

        if (empty($target_id))
        {
            //将自已加入匹配池内
            $redis->sadd(self::$pk_prefix.self::$random, $params['user_id']);

            //开启随机超时计时器
            Timer::add(self::$random_over_time, [__CLASS__, 'systemMatchOverTime'], [$params['user_id']], false);

            Logger::info('pk随机匹配超时定时器(一次性)', 'addTimer');
        }
        else if ($target_id != $params['user_id'])
        {
            //将对方加匹配锁
            self::matchAddLock($target_id);

            //开启pk
            self::startPk(['active_id'=>$params['user_id'], 'target_id'=>$target_id, 'pk_type' => $params['pk_type']]);
        }
    }


    //验证当前Pk是否有效
    public static function verifyPk(array $params)
    {
        if (empty($params['pk_id'])) return;
        $pk_info = self::getPkAnchorInfo($params['pk_id']);
        empty($pk_info) && Gateway::sendToCurrentClient(self::genMsg('endPk', 'ok'));
    }


    //随机匹配超时回应
    public static function systemMatchOverTime($user_id)
    {
        global $redis;

        //当前计时器用户在池内则认为过期条件成立
        if ($redis->sismember(self::$pk_prefix.self::$random, $user_id) == 1)
        {
            //将当前用户踢出匹配池内
            $redis->srem(self::$pk_prefix.self::$random, $user_id);

            Gateway::sendToUid($user_id, self::genMsg('pkMsg', '匹配超时，暂无其它主播发起~', [], 1));
        }
    }


    //pk排位赛匹配超时回应
    public static function pkRankOverTime($user_id)
    {
        global $redis;

        //当前计时器用户在池内则认为过期条件成立
        if ($redis->sismember(self::$pk_prefix.self::$pk_rank_activity, $user_id) == 1)
        {
            //将当前用户踢出匹配池内
            $redis->srem(self::$pk_prefix.self::$pk_rank_activity, $user_id);

            Gateway::sendToUid($user_id, self::genMsg('pkMsg', '匹配超时，暂无其它主播参赛~', [], 1));
        }
    }



    //随机匹配
    public static function systemMatch(array $params)
    {
        global $redis;

        if (empty($params['pk_type']) || $params['pk_type'] == 'friend')
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', 'PK类型错误~', [], 1));
            return;
        }

        //当前正在pk中则不可发起随机匹配
        if ($redis->exists(self::$pk_prefix.self::$pk_lock.self::$active.$params['user_id']) ||
            $redis->exists(self::$pk_prefix.self::$pk_lock.self::$target.$params['user_id']))
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', '当前正在pk中~', [], 1));
            return;
        }

        if(self::checkLinkMic($params['user_id']))
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', '当前正在连麦中~', [], 1));
            return;
        }

        switch ($params['pk_type'])
        {
            case 'pk_rank' :
                self::pkRank($params);
                break;

            default:
                self::systemRandom($params);
                break;
        }
    }


    //发起Pk请求
    public static function requestPk(array $params)
    {
        global $redis, $config;

        if (empty($params['pk_type']) || $params['pk_type'] != 'friend')
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', 'PK类型错误~', [], 1));
            return;
        }

        $active_info = self::getUserBasicInfo($params['user_id']);

        switch (true)
        {
            case !self::checkLive($params['user_id']) :
                $active_message = self::genMsg('pkMsg', '当前不在直播中~', [], 1);
                break;

            case !self::checkLive($params['target_id']) :
                $active_message = self::genMsg('pkMsg', '对方不在直播中~', [], 1);
                break;

            case self::checkLinkMic($params['user_id']) :
                $active_message = self::genMsg('pkMsg', '连麦中,不能发起Pk~', [], 1);
                break;

            case self::checkLinkMic($params['target_id']) :
                $active_message = self::genMsg('pkMsg', '对方正在连麦中~', [], 1);
                $target_message = self::genMsg('tipMsg', $active_info['nickname'].' 向你发起Pk请求,正在连麦中系统已自动拦截~', [], 1);
                Gateway::sendToUid($params['target_id'], $target_message);
                break;

            case $redis->exists(self::$pk_prefix.self::$pk_lock.self::$active.$params['user_id']) || $redis->exists(self::$pk_prefix.self::$pk_lock.self::$target.$params['user_id']) :
                $active_message = self::genMsg('pkMsg', '当前正在pk中~', [], 1);
                break;

            case $redis->exists(self::$pk_prefix.self::$pk_lock.self::$target.$params['target_id']) || $redis->exists(self::$pk_prefix.self::$pk_lock.self::$active.$params['target_id']) :
                $active_message = self::genMsg('pkMsg', '对方正在PK中~', [], 1);
                break;
        }

        if (isset($active_message))
        {
            Gateway::sendToCurrentClient($active_message);
            return;
        }

        //拦截向被动方发起的其它pk请求
        if (!self::matchAddLock($params['target_id']))
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', '对方正在发起pk，请重新发起~', [], 1));

            return;
        }

        //验证pk时长
        if (isset($params['pk_duration']) && !empty($params['pk_duration']) && $params['pk_duration'] > 3600)
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', 'pk时间设置过长，请重新设置~', [], 1));

            return;
        }

        //验证pk主题
        if (isset($params['pk_topic']) && !empty($params['pk_topic']) && mb_strlen($params['pk_topic']) > 20)
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', 'pk主题设置过长，请重新设置~', [], 1));

            return;
        }

        //验证ac主题
        if (isset($params['ac_topic']) && !empty($params['ac_topic']) && mb_strlen($params['ac_topic']) > 6)
        {
            Gateway::sendToCurrentClient(self::genMsg('pkMsg', '惩罚主题设置过长，请重新设置~', [], 1));

            return;
        }

        $data = [
            'pk_duration' => isset($params['pk_duration']) && !empty($params['pk_duration']) ? $params['pk_duration'] : $config['pk']['pk_duration'],
            'pk_topic' => isset($params['pk_topic']) && !empty($params['pk_topic']) ? $params['pk_topic'] : self::$pk_topic,
            'ac_topic' => isset($params['ac_topic']) && !empty($params['ac_topic']) ? $params['ac_topic'] : self::$pk_topic,
            'pk_type' => $params['pk_type'],
            'active_info' => [
                'user_id' => $params['user_id'],
                'avatar' => $active_info['avatar'],
                'nickname' => $active_info['nickname'],
                'level' => $active_info['level'],
                'gender' => $active_info['gender'],
                'vip_status' => $active_info['vip_status'],
            ],
            'target_info' => [
                'user_id' => $params['target_id'],
            ]
        ];

        //开启匹配计时器
        Timer::add(self::$match_over_time, [__CLASS__, 'refusePk'], [['active_id'=>$params['user_id'], 'user_id'=>$params['target_id']]], false);

        Logger::info('pk主动发起超时定时器(一次性)', 'addTimer');

        //添加匹配计时器标识
        $redis->sadd(self::$pk_prefix.self::$timer.$params['user_id'], 'defaultRefuse');

        //储存发起方所设置的pk参数
        $redis->hset(self::$pk_option, $params['user_id'], json_encode(['pk_duration' => $data['pk_duration'], 'pk_topic' => $data['pk_topic'], 'ac_topic'=> $data['ac_topic']]));

        Gateway::sendToUid($params['target_id'], self::genMsg('askPk', 'ok', $data));
    }


    //开启pk(同意触发)
    public static function startPk(array $params)
    {
        global $db, $redis, $config;

        //检查请求是否已超时
        if (!self::matchAddLock($params['target_id'], true))
        {
            Gateway::sendToUid($params['target_id'],self::genMsg('pkMsg', '请求超时, 系统已结束您与对方的匹配~', [], 1));
            return;
        }

        //删除被动方匹配锁
        $redis->del(self::$pk_prefix.self::$match_lock.$params['target_id']);

        //删除匹配超时计时器标识
        $redis->srem(self::$pk_prefix.self::$timer.$params['active_id'], 'defaultRefuse');

        //防止互相同时发起
        if ($redis->exists(self::$pk_prefix.self::$pk_lock.self::$active.$params['active_id']) || $redis->exists(self::$pk_prefix.self::$pk_lock.self::$target.$params['active_id'])) return;

        //防止互相同时发起
        if ($redis->exists(self::$pk_prefix.self::$pk_lock.self::$target.$params['target_id']) || $redis->exists(self::$pk_prefix.self::$pk_lock.self::$active.$params['target_id'])) return;

        $active_room = self::genLivePull($params['active_id']);

        $unActive_room = self::genLivePull($params['target_id']);

        if (empty($active_room))
        {
            Gateway::sendToUid($params['target_id'],self::genMsg('pkMsg', '对方已关播~', [], 1));
            return;
        }

        if (empty($unActive_room))
        {
            Gateway::sendToUid($params['active_id'], self::genMsg('pkMsg', '对方已关播~', [], 1));
            return;
        }


        switch ($params['pk_type'])
        {
            case 'pk_rank' :
                $active_pk_params = ['pk_duration' => $config['pk']['pk_duration'], 'pk_topic' => '排位赛', 'ac_topic' => self::$pk_topic];
                break;

            case 'friend' :
                $active_pk_params = $redis->hget(self::$pk_option, $params['active_id']);
                $active_pk_params = json_decode($active_pk_params, true);
                break;

            default:
                $active_pk_params = ['pk_duration' => $config['pk']['pk_duration'], 'pk_topic' => self::$pk_topic, 'ac_topic' => self::$pk_topic];
                break;
        }

        $insert = [
            'active_id' => $params['active_id'],
            'target_id' => $params['target_id'],
            'pk_type' => isset($params['pk_type']) ? $params['pk_type'] : '',
            'active_room_id' => $active_room['id'],
            'target_room_id' => $unActive_room['id'],
            'pk_start_time' => time()+self::$delay_time,
            'pk_duration' => $active_pk_params['pk_duration'],
            'pk_topic' => $active_pk_params['pk_topic'],
            'ac_topic' => $active_pk_params['ac_topic'],
        ];

        $pk_id = $db->insert(TABLE_PREFIX.'live_pk')->cols($insert)->query();

        if (!$pk_id)
        {
            Gateway::sendToUid($params['target_id'],self::genMsg('pkMsg', '系统错误，请重试', [], 1));
            return;
        }

        $active_info = self::getUserBasicInfo($params['active_id']);

        $unActive_info = self::getUserBasicInfo($params['target_id']);

        $unActive_client = Gateway::getClientIdByUid($params['target_id']);

        $active_client = Gateway::getClientIdByUid($params['active_id']);

        $data = [
            'pk_id'=> $pk_id,
            'pk_type' => $insert['pk_type'],
            'energy' => '0.50',
            'active_info' => [
                'user_id'=> $params['active_id'],
                'room_id'=> $active_room['id'],
                'nickname' => $active_info['nickname'],
                'avatar' => $active_info['avatar'],
                'pull_url' => $active_room['pull'],
                'guard_list' => self::getGuard($params['active_id']),
            ],
            'target_info' => [
                'user_id'=> $params['target_id'],
                'room_id'=> $unActive_room['id'],
                'avatar' => $unActive_info['avatar'],
                'nickname' => $unActive_info['nickname'],
                'pull_url' => $unActive_room['pull'],
                'guard_list' => self::getGuard($params['target_id']),
            ],
        ];

        $active_info['client_id'] = $active_client[0];

        $unActive_info['client_id'] = $unActive_client[0];

        $active_info['room_id'] = $active_room['id'];

        $unActive_info['room_id'] = $unActive_room['id'];

        //添加主动方pk锁
        self::pkAddLock($params['active_id'], self::$active, $active_pk_params['pk_duration']);

        //添加被动方pk锁
        self::pkAddLock($params['target_id'], self::$target, $active_pk_params['pk_duration']);

        Gateway::sendToUid($params['active_id'], self::genMsg('systemMsg', 'ok', ['content' => '你即将与 '.$unActive_info['nickname'].' 进行PK，战斗起来吧~']));

        Gateway::sendToUid($params['target_id'],self::genMsg('systemMsg', 'ok', ['content' => '你即将与 '.$active_info['nickname'].' 进行PK，战斗起来吧~']));

        Gateway::sendToUid($params['active_id'], self::genMsg('beginPk', 'ok', $data));

        Gateway::sendToUid($params['target_id'],self::genMsg('beginPk', 'ok', $data));

        $active_pk_params['pk_id'] = $pk_id;

        //开启Pk计时器（动画效果后）
        Timer::add(self::$delay_time, [__CLASS__, 'startDelayTimer'], [$active_info, $unActive_info, $active_pk_params], false);
        Logger::info('pk开始主播端延时定时器(一次性)', 'addTimer');

        //延迟3秒给房间内用户发数据
        Timer::add(self::$audience_delay_time, [__CLASS__, 'sendBeginPkToAudience'], [$active_info, $unActive_info, $active_pk_params, $data], false);
        Logger::info('pk开始用户端延时定时器(一次性)', 'addTimer');
    }


    //开启pk动画时间过后触发
    public static function startDelayTimer($active_info, $unActive_info, $pk_info)
    {
        global $redis;

        $now = time();
        if (empty($active_info['room_id']) || empty($unActive_info['room_id'])) return;

        $pk_info_user = self::getPkAnchorInfo($pk_info['pk_id']);
        if (empty($pk_info_user)) return;

        $pk_timer_id = Timer::add(1, [__CLASS__, 'pkTime'], [$active_info, $unActive_info, $pk_info, $now, &$pk_timer_id]);
        Logger::info('pk开始计时定时器,timer_id =>'.$pk_timer_id, 'addTimer');

        $redis->sadd(self::$pk_prefix.self::$timer.$active_info['user_id'], 'pking'); //添加pk中的计时器标识
    }


    //延迟观众发数据
    public static function sendBeginPkToAudience($active_info, $unActive_info, $pk_info, $data)
    {
        if (empty($active_info['room_id']) || empty($unActive_info['room_id'])) return;
        Gateway::sendToGroup($active_info['room_id'], self::genMsg('systemMsg', 'ok', ['content' => '主播发起了与 '.$unActive_info['nickname'].' 连线PK，快来给ta加油捧场吧!~']), [$active_info['client_id']]);

        Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'主播接受了与 '.$active_info['nickname'].' 连线PK，快来给ta加油捧场吧!~']), [$unActive_info['client_id']]);

        if (strcasecmp($pk_info['pk_topic'], self::$pk_topic) != 0)
        {
            $pk_topic_str = self::genMsg('systemMsg', 'ok', ['content' => '本场PK主题 【'.$pk_info['pk_topic'].'】']);

            Gateway::sendToGroup($active_info['room_id'], $pk_topic_str, [$active_info['client_id']]);

            Gateway::sendToGroup($unActive_info['room_id'], $pk_topic_str);
        }

        Gateway::sendToGroup($active_info['room_id'], self::genMsg('beginPk', 'ok', $data), [$active_info['client_id']]);

        Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('beginPk', 'ok', $data), [$unActive_info['client_id']]);
    }


    //主动结束pk
    public static function endPk(array $params)
    {
        global $redis;

        if (empty($params['pk_id'])) return;

        $pk_info = self::getPkAnchorInfo($params['pk_id']);

        if (empty($pk_info)) return;

        if ($params['user_id'] != $pk_info['active_id'] && $params['user_id'] != $pk_info['target_id']) return;

        //删除pk锁
        $redis->del(self::$pk_prefix.self::$pk_lock.self::$active.$pk_info['active_id']);

        $redis->del(self::$pk_prefix.self::$pk_lock.self::$target.$pk_info['target_id']);

        $active_client = Gateway::getClientIdByUid($pk_info['active_id']);

        $unActive_client = Gateway::getClientIdByUid($pk_info['target_id']);

        //在pk中主动结束
        if (time() <= $pk_info['pk_start_time']+$pk_info['pk_duration'])
        {
            $pk_status_res = $params['user_id'] == $pk_info['active_id'] ? '-1' : '1';

            //删除正在pk中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$pk_info['active_id'], 'pking');

            //删除正在ac中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$pk_info['active_id'], 'acing');

            $redis->del('BG_LIVE:pk_rank_' . $params['pk_id']. '_' . $pk_info['active_room_id']);
            $redis->del('BG_LIVE:pk_rank_' . $params['pk_id']. '_' . $pk_info['target_room_id']);
        }
        else{
            //在ac中主动结束则根据已有的结果来确定
            $pk_status_res = $pk_info['active_income'] > $pk_info['target_income'] ? '1' : ($pk_info['active_income'] < $pk_info['target_income'] ? '-1' : '0');

            //删除正在ac中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$pk_info['active_id'], 'acing');

            $redis->del('BG_LIVE:pk_rank_' . $params['pk_id']. '_' . $pk_info['active_room_id']);
            $redis->del('BG_LIVE:pk_rank_' . $params['pk_id']. '_' . $pk_info['target_room_id']);
        }

        self::completePk($params['pk_id'], $pk_status_res, 1, $params['user_id']);

        //被动方用户信息
        $unActive_info = self::getUserBasicInfo($pk_info['target_id']);

        //主动方用户信息
        $active_info = self::getUserBasicInfo($pk_info['active_id']);

        $active_end_str = '已结束与主播 '.$unActive_info['nickname'].' 的PK';

        $unActive_end_str = '已结束与主播 '.$active_info['nickname'].' 的PK';

        if ($pk_status_res == -1)
        {
            $active_win_str = '对方获得胜利，本次PK收益 '.$pk_info['active_income'].' '.APP_MILLET_NAME;
            $unActive_win_str = '我方获得胜利，本次PK收益 '.$pk_info['target_income'].' '.APP_MILLET_NAME;
            $active_client_str = '对方主播 '.$unActive_info['nickname'].' 获得PK胜利';
            $unActive_client_str = '我方主播 '.$active_info['nickname'].' 获得PK胜利';
        }
        else if ($pk_status_res == 1){
            $active_win_str = '我方获得胜利，本次PK收益 '.$pk_info['active_income'].' '.APP_MILLET_NAME;
            $unActive_win_str = '对方获得胜利，本次PK收益 '.$pk_info['target_income'].' '.APP_MILLET_NAME;
            $active_client_str = '我方主播 '.$unActive_info['nickname'].' 获得PK胜利';
            $unActive_client_str = '对方主播 '.$active_info['nickname'].' 获得PK胜利';
        }
        else {
            $active_win_str = '本次PK平局，PK收益 '.$pk_info['active_income'].' '.APP_MILLET_NAME;
            $unActive_win_str = '本次PK平局，PK收益 '.$pk_info['target_income'].' '.APP_MILLET_NAME;
            $active_client_str = '主播已结束与 '.$unActive_info['nickname'].' 的PK, 本次PK平局';
            $unActive_client_str = '主播已结束与 '.$active_info['nickname'].' 的PK, 本次PK平局';
        }

        Gateway::sendToUid($pk_info['active_id'], self::genMsg('systemMsg', 'ok', ['content'=>$active_end_str]));

        Gateway::sendToUid($pk_info['target_id'], self::genMsg('systemMsg', 'ok', ['content'=>$unActive_end_str]));

        Gateway::sendToUid($pk_info['active_id'], self::genMsg('systemMsg', 'ok', ['content'=>$active_win_str]));

        Gateway::sendToUid($pk_info['target_id'], self::genMsg('systemMsg', 'ok', ['content'=>$unActive_win_str]));

        isset($active_client[0]) && Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>$active_client_str]), [$active_client[0]]);

        isset($unActive_client[0]) && Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>$unActive_client_str]), [$unActive_client[0]]);

        isset($active_client[0]) && Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场~']), [$active_client['0']]);

        isset($unActive_client[0]) && Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场~']), [$unActive_client['0']]);

        Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('endPk', 'ok'));

        Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('endPk', 'ok'));
    }



    //非正常关播结束Pk, 正常关播关闭pk
    public static function abnormalEndPk(array $params)
    {
        global $redis;

        if (empty($params['pk_id'])) return;

        $pk_info = self::getPkAnchorInfo($params['pk_id']);

        if (empty($pk_info)) return;

        if ($params['user_id'] != $pk_info['active_id'] && $params['user_id'] != $pk_info['target_id']) return;

        //删除pk锁
        $redis->del(self::$pk_prefix.self::$pk_lock.self::$active.$pk_info['active_id']);

        $redis->del(self::$pk_prefix.self::$pk_lock.self::$target.$pk_info['target_id']);

        $active_client = Gateway::getClientIdByUid($pk_info['active_id']);

        $unActive_client = Gateway::getClientIdByUid($pk_info['target_id']);

        $active_client_id = $active_client ? [$active_client[0]] : [];
        $unActive_client_id = $unActive_client ? [$unActive_client[0]] : [];

        //在pk中主动结束
        if (time() <= $pk_info['pk_start_time']+$pk_info['pk_duration'])
        {
            $pk_status_res = $params['user_id'] == $pk_info['active_id'] ? '-1' : '1';

            //删除正在pk中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$pk_info['active_id'], 'pking');

            //删除正在ac中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$pk_info['active_id'], 'acing');
        }
        else{
            //在ac中主动结束则根据已有的结果来确定
            $pk_status_res = $pk_info['active_income'] > $pk_info['target_income'] ? '1' : ($pk_info['active_income'] < $pk_info['target_income'] ? '-1' : '0');

            //删除正在ac中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$pk_info['active_id'], 'acing');
        }
        $redis->del('BG_LIVE:pk_rank_' . $params['pk_id']. '_' . $pk_info['active_room_id']);
        $redis->del('BG_LIVE:pk_rank_' . $params['pk_id']. '_' . $pk_info['target_room_id']);

        self::completePk($params['pk_id'], $pk_status_res);

        $unActive_info = self::getUserBasicInfo($pk_info['target_id']);

        $active_info = self::getUserBasicInfo($pk_info['active_id']);

        $active_end_str = '已结束与主播 '.$unActive_info['nickname'].' 的PK';

        $unActive_end_str = '已结束与主播 '.$active_info['nickname'].' 的PK';

        if ($pk_status_res == -1)
        {
            $active_win_str = '对方获得胜利，本次PK收益 '.$pk_info['active_income'].' '.APP_MILLET_NAME;
            $unActive_win_str = '我方获得胜利，本次PK收益 '.$pk_info['target_income'].' '.APP_MILLET_NAME;
            $active_client_str = '对方主播 '.$unActive_info['nickname'].' 获得PK胜利';
            $unActive_client_str = '我方主播 '.$active_info['nickname'].' 获得PK胜利';
        }
        else if ($pk_status_res == 1){
            $active_win_str = '我方获得胜利，本次PK收益 '.$pk_info['active_income'].' '.APP_MILLET_NAME;
            $unActive_win_str = '对方获得胜利，本次PK收益 '.$pk_info['target_income'].' '.APP_MILLET_NAME;
            $active_client_str = '我方主播 '.$unActive_info['nickname'].' 获得PK胜利';
            $unActive_client_str = '对方主播 '.$active_info['nickname'].' 获得PK胜利';
        }
        else {
            $active_win_str = '本次PK平局，PK收益 '.$pk_info['active_income'].' '.APP_MILLET_NAME;
            $unActive_win_str = '本次PK平局，PK收益 '.$pk_info['target_income'].' '.APP_MILLET_NAME;
            $active_client_str = '主播已结束与 '.$unActive_info['nickname'].' 的PK, 本次PK平局';
            $unActive_client_str = '主播已结束与 '.$active_info['nickname'].' 的PK, 本次PK平局';
        }

        $active_role = $params['user_id'] == $pk_info['active_id'] ? true : false;

        $active_role === false && Gateway::sendToUid($pk_info['active_id'], self::genMsg('systemMsg', 'ok', ['content'=>$active_end_str]));

        $active_role === true && Gateway::sendToUid($pk_info['target_id'], self::genMsg('systemMsg', 'ok', ['content'=>$unActive_end_str]));

        $active_role === false && Gateway::sendToUid($pk_info['active_id'], self::genMsg('systemMsg', 'ok', ['content'=>$active_win_str]));

        $active_role === true && Gateway::sendToUid($pk_info['target_id'], self::genMsg('systemMsg', 'ok', ['content'=>$unActive_win_str]));

        $active_role === false && Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>$active_client_str]), $active_client_id);

        $active_role === true && Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>$unActive_client_str]), $unActive_client_id);

        $active_role === false && Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场~']), $active_client_id);

        $active_role === true && Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场~']), $unActive_client_id);
        $active_role === false && Gateway::sendToGroup($pk_info['active_room_id'], self::genMsg('endPk', 'ok'));

        $active_role === true && Gateway::sendToGroup($pk_info['target_room_id'], self::genMsg('endPk', 'ok'));

    }


    //pk计时器
    public static function pkTime($active_info, $unActive_info, $pk_info, $pking_time, $pk_timer_id)
    {
        global $redis;

        if ($redis->sismember(self::$pk_prefix.self::$timer.$active_info['user_id'], 'pking') != 1)
        {
            $rs = Timer::del($pk_timer_id);
            $rs && Logger::info('销毁pk进行中定时器(主动结束),timer_id =>'.$pk_timer_id, 'destroyTimer');
            return;
        }

        $now = time();

        if ($now < $pk_info['pk_duration']+$pking_time+1)
        {
            $data = [
                'time'=> ($pk_info['pk_duration']+$pking_time)-$now,
                'text' => 'pk时间',
            ];

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('pking', 'ok', $data));

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('pking', 'ok', $data));
        }
        else{
            $rs = Timer::del($pk_timer_id);
            $rs && Logger::info('销毁pk进行中定时器(正常流程),timer_id =>'.$pk_timer_id, 'destroyTimer');

            $redis->srem(self::$pk_prefix.self::$timer.$active_info['user_id'], 'pking'); //删除正在pk中的计时器标识

            //获取最新pk数据
            $new_pk_info = self::getPkAnchorInfo($pk_info['pk_id']);

            $new_pk_info['pk_status_res'] = $new_pk_info['active_income'] > $new_pk_info['target_income'] ? '1' : ($new_pk_info['active_income'] < $new_pk_info['target_income'] ? '-1' : '0');

            if ($new_pk_info['pk_status_res'] != 0)
            {
                if ($new_pk_info['pk_status_res'] == 1)
                {
                    $loser = $unActive_info['room_id'];

                    $loser_name = $unActive_info['nickname'];

                    $win_name = $active_info['nickname'];

                    $loser_clients = $unActive_info['client_id'];
                }
                else{
                    $loser = $active_info['room_id'];

                    $loser_name = $active_info['nickname'];

                    $win_name = $unActive_info['nickname'];

                    $loser_clients = $active_info['client_id'];
                }

                //发送给失败方观众信息
                Gateway::sendToGroup($loser, self::genMsg('systemMsg', 'ok', ['content'=>"很遗憾，我们的主播 {$loser_name} PK失利~我们一起安慰下ta吧~"]), [$loser_clients]);

                $win_str = self::genMsg('systemMsg', 'ok', ['content'=>"恭喜主播 {$win_name} 成为本场胜利者，获得惩罚对方的权利，快来围观吧!"]);

                //发送胜利方信息
                Gateway::sendToGroup($active_info['room_id'], $win_str, [$active_info['client_id']]);

                //发送胜利信息
                Gateway::sendToGroup($unActive_info['room_id'], $win_str, [$unActive_info['client_id']]);
            }

            $new_pk_info['ac_text'] = $new_pk_info['pk_status_res'] == 0 ? '交流时间' : '惩罚时间';

            if (strcasecmp($pk_info['ac_topic'], self::$pk_topic) != 0)
            {
                $ac_topic_str = self::genMsg('systemMsg', 'ok', ['content' => '进入'.$new_pk_info['ac_text'].'，本场PK惩罚主题 【'.$pk_info['ac_topic'].'】']);

                Gateway::sendToGroup($active_info['room_id'], $ac_topic_str);

                Gateway::sendToGroup($unActive_info['room_id'], $ac_topic_str);
            }

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('pkResult', 'ok', ['win_status' => $new_pk_info['pk_status_res']]));

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('pkResult', 'ok', ['win_status' => $new_pk_info['pk_status_res']]));

            $ac_timer_id = Timer::add(1, [__CLASS__, 'acTime'], [$active_info, $unActive_info, $new_pk_info, $now, &$ac_timer_id]);
            Logger::info('添加pk惩罚计时定时器,timer_id =>'.$ac_timer_id, 'addTimer');

            $redis->sadd(self::$pk_prefix.self::$timer.$active_info['user_id'], 'acing'); //添加交流时间计时器标识
        }
    }


    //交流计时器
    public static function acTime($active_info, $unActive_info, $pk_info, $ac_time, $ac_timer_id)
    {
        global $redis, $config;

        if ($redis->sismember(self::$pk_prefix.self::$timer.$active_info['user_id'], 'acing') != 1  || empty($pk_info['id']))
        {
            $rs = Timer::del($ac_timer_id);
            $rs && Logger::info('销毁pk惩罚计时定时器(主动结束),timer_id =>'.$ac_timer_id, 'destroyTimer');
            return;
        }

        $now = time();

        if ($now < $config['pk']['ac_time'] + $ac_time+1)
        {
            $data = [
                'time'=> ($config['pk']['ac_time'] + $ac_time)-$now,
                'text' => $pk_info['ac_text'],
            ];

            //每3秒更新本场Pk结果数据
            if ($now % 3 == 1)
            {
                Gateway::sendToGroup($active_info['room_id'], self::genMsg('pkResult', 'ok', ['win_status' => $pk_info['pk_status_res']]));

                Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('pkResult', 'ok', ['win_status' => $pk_info['pk_status_res']]));
            }

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('acing', 'ok', $data));

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('acing', 'ok', $data));
        }
        else{
            $rs = Timer::del($ac_timer_id);
            $rs && Logger::info('销毁pk惩罚计时定时器(正常流程),timer_id =>'.$ac_timer_id, 'destroyTimer');

            //删除pk锁
            $redis->del(self::$pk_prefix.self::$pk_lock.$pk_info['active_id']);

            $redis->del(self::$pk_prefix.self::$pk_lock.$pk_info['target_id']);

            //删除正在ac中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$active_info['user_id'], 'acing');

            self::completePk($pk_info['id'], $pk_info['pk_status_res']);

            $redis->del('BG_LIVE:pk_rank_' . $pk_info['id']. '_' . $pk_info['active_room_id']);
            $redis->del('BG_LIVE:pk_rank_' . $pk_info['id']. '_' . $pk_info['target_room_id']);

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('endPk', 'ok'));

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('endPk', 'ok'));

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场哦~']), [$active_info['client_id']]);

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场哦~']), [$unActive_info['client_id']]);
        }
    }



    //拒绝Pk请求的息消转发
    public static function refusePk(array $params)
    {
        global $redis;

        if (empty($params['active_id'])) return;

        //验证是否在超时期间内发期拒绝，若已超时则不作反馈
        if ($redis->sismember(self::$pk_prefix.self::$timer.$params['active_id'], 'defaultRefuse') == 1)
        {
            $redis->del(self::$pk_prefix.self::$match_lock.$params['user_id']); //删除匹配锁，已免主动方快速发起相同的请求导致过期时间不及时

            $redis->srem(self::$pk_prefix.self::$timer.$params['active_id'], 'defaultRefuse'); //删除超时计时器标识

            $targets = self::getUserBasicInfo($params['user_id']);

            Gateway::sendToUid($params['active_id'], self::genMsg('pkMsg', $targets['nickname'].' 拒绝了你的PK请求~', [], 1));
        }
    }


    //pk加锁
    protected static function pkAddLock($user_id, $falg, $pk_duration, $value=1)
    {
        global $redis, $config;

        $exists = $redis->setnx(self::$pk_prefix.self::$pk_lock.$falg.$user_id, $value);

        $exists == 1 && $redis->expire(self::$pk_prefix.self::$pk_lock.$falg.$user_id, $pk_duration + $config['pk']['ac_time'] + 3);

        return (bool)$exists;
    }


    //匹配加锁
    protected static function matchAddLock($user_id, $check=false)
    {
        global $redis;

        if ($check)
        {
            $exists = $redis->exists(self::$pk_prefix.self::$match_lock.$user_id);
        }
        else{
            $exists = $redis->setnx(self::$pk_prefix.self::$match_lock.$user_id, 1);

            $exists == 1 && $redis->expire(self::$pk_prefix.self::$match_lock.$user_id, self::$match_over_time); //默认请求超时时间
        }

        return (bool)$exists;
    }


    //检查是否在直播
    protected static function checkLive($user_id)
    {
        global $db;

        $sql = 'SELECT id FROM '.TABLE_PREFIX.'live WHERE user_id='.$user_id;

        $rs = $db->query($sql);

        return !empty($rs);
    }


    //检查是否在连麦中
    protected static function checkLinkMic($user_id)
    {
        global $db;

        $sql = 'SELECT id FROM '.TABLE_PREFIX.'live WHERE user_id='.$user_id.' limit 1';

        $room = $db->query($sql);

        $sql = 'SELECT id FROM '.TABLE_PREFIX.'link_mic_log WHERE status=1 AND room_id = '.$room[0]['id'];

        $rs = $db->query($sql);

        return !empty($rs);
    }


    //获取守护列表
    protected static function getGuard($user_id)
    {
        global $redis;

        $users = [];

        $guardTotals = $redis->zrevrange('BG_GUARD:'.$user_id, 0, -1, true);

        if (empty($guardTotals)) return $users;

        $now = time();

        foreach ($guardTotals as $guard_user_id => $score)
        {
            $userInfo = self::getUserBasicInfo($guard_user_id);

            $users[] = [
                'nickname' => $userInfo['nickname'],
                'avatar' => $userInfo['avatar'],
                'sign' => $userInfo['sign'],
                'user_id' => $guard_user_id,
                'level' => $userInfo['level'],
                'vip_status' => $userInfo['vip_expire'] < $now ? 0 : 1,
                'is_creation' => $userInfo['is_creation'],
                'verified' => $userInfo['verified'],
                'gender' => $userInfo['gender']
            ];
        }

        return array_slice($users, 0, 3);

    }


    //双方播流地址处理
    protected static function genLivePull($user_id)
    {
        global $db, $config;

        $sql = 'SELECT * FROM '.TABLE_PREFIX.'live WHERE user_id='.$user_id;

        $live_info = $db->query($sql);
        if (empty($live_info)) return  false;
        if (strcasecmp($config['live']['platform'], 'tencent') === 0)
        {
            $live_info[0]['pull'] = self::ACCRTMPPlayUrl($live_info[0]['stream']);
        }

        return $live_info[0];
    }


    protected static function ACCRTMPPlayUrl($stream)
    {
        global $config;

        list(, ,$time) = explode('_', $stream);

        $secret_key = $config['live']['secret_key'];

        $pull = $config['live']['pull'];

        $stream_prefix = $config['live']['stream_prefix'];

        $ext_time = $config['live']['ext'];

        $ext = strtoupper(dechex($time+$ext_time));

        $secret = md5($secret_key . $stream . $ext);
        
        return sprintf('rtmp://%s/live/%s', 'lala.dangjunwei.top', $stream);
        // return sprintf('rtmp://%s/live/%s', 'wsla.eusmile.cn', $stream);
        return sprintf('rtmp://%s/live/%s?bizid=%s&txSecret=%s&txTime=%s', $pull, $stream, $stream_prefix, $secret, $ext);
        //return sprintf('rtmp://%s/live/%s', $pull, $stream);
    }


}