<?php

namespace app\timer;

use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;


//未使用
class Pk extends \app\api\Pk
{

    //随机则匹配超时回应
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


    //pk计时器
    public static function pkTime($active_info, $unActive_info, $pk_info, $pking_time, $pk_timer_id)
    {
        global $redis;

        if ($redis->sismember(self::$pk_prefix.self::$timer.$active_info['user_id'], 'pking') != 1)
        {
            Timer::del($pk_timer_id);
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
            Timer::del($pk_timer_id);

            $redis->srem(self::$pk_prefix.self::$timer.$active_info['user_id'], 'pking'); //删除正在pk中的计时器标识

            //获取最新pk数据
            $new_pk_info = self::getPkInfo($pk_info['pk_id']);

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
                Gateway::sendToGroup($loser, self::genMsg('systemMsg', 'ok', ['content'=>"很遗憾，我们的主播 {$loser_name} PK矢利~我们一起安慰下ta吧~"]), [$loser_clients]);

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

            $redis->sadd(self::$pk_prefix.self::$timer.$active_info['user_id'], 'acing'); //添加交流时间计时器标识
        }
    }


    //交流计时器
    public static function acTime($active_info, $unActive_info, $pk_info, $ac_time, $ac_timer_id)
    {
        global $redis, $config;

        if ($redis->sismember(self::$pk_prefix.self::$timer.$active_info['user_id'], 'acing') != 1)
        {
            Timer::del($ac_timer_id);
            return;
        }

        $now = time();

        if ($now < $config['pk_config']['ac_time'] + $ac_time+1)
        {
            $data = [
                'time'=> ($config['pk_config']['ac_time'] + $ac_time)-$now,
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
            Timer::del($ac_timer_id);

            //删除pk锁
            $redis->del(self::$pk_prefix.self::$pk_lock.$pk_info['active_id']);

            $redis->del(self::$pk_prefix.self::$pk_lock.$pk_info['target_id']);

            //删除正在ac中的计时器标识
            $redis->srem(self::$pk_prefix.self::$timer.$active_info['user_id'], 'acing');

            self::updatePkEnd($pk_info['id'], $pk_info['pk_status_res']);

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('endPk', 'ok'));

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('endPk', 'ok'));

            Gateway::sendToGroup($active_info['room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场哦~']), [$active_info['client_id']]);

            Gateway::sendToGroup($unActive_info['room_id'], self::genMsg('systemMsg', 'ok', ['content'=>'本场PK已结束，敬请期待下一场哦~']), [$unActive_info['client_id']]);
        }
    }



    //延迟观众发数据
    public static function sendBeginPkToAudience($active_info, $unActive_info, $pk_info, $data)
    {
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



    //开启pk动画时间过后触发
    public static function startDelayTimer($active_info, $unActive_info, $pk_info)
    {
        global $redis;

        $now = time();

        $pk_timer_id = Timer::add(1, [__CLASS__, 'pkTime'], [$active_info, $unActive_info, $pk_info, $now, &$pk_timer_id]);

        $redis->sadd(self::$pk_prefix.self::$timer.$active_info['user_id'], 'pking');; //添加pk中的计时器标识
    }
}