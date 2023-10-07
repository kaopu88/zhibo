<?php

namespace app\service;

use app\Common;
use GatewayWorker\Lib\Gateway;

class MilletTools
{
    protected static $message;//错误消息
    protected static $code = 0;//错误码

    //业绩类型
    protected static $achievement = ['live_gift', 'barrage', 'user_package', 'live_payment'];

    protected static $heroesRank = ['live_gift', 'cover_star_vote', 'liudanji'];

    protected static function setError($message, $code = 1)
    {
        self::$message = $message;
        self::$code = $code;
        return false;
    }

    //根据房间ID获取主播UID
    public static function getAnchorIdByRoomId($roomId)
    {
        global $db, $redis;
        $roomAnchorIdKey = 'BG_LIVE:' . $roomId . ':anchorInfo';
        $anchorId = $redis->get($roomAnchorIdKey);
        if (empty($anchorId)) {
            $res = $db->query("SELECT id,user_id FROM " . TABLE_PREFIX . "live WHERE id = " . $roomId);
            if (empty($res))
            {
                Gateway::sendToCurrentClient(Common::genMsg('tipMsg', '信息错误~', [], 1));
                return;
            }
            $anchorInfo = $res[0];
            $anchorId = $anchorInfo['user_id'];
            $redis->set($roomAnchorIdKey, $anchorId);
            $redis->expire($roomAnchorIdKey, 4 * 3600);
        }
        return $anchorId;
    }

    //查询礼物信息
    public static function getGift($giftId)
    {
        global $db;

        $res = $db->query('SELECT id, cid, name, price, discount, conv_millet, picture_url,is_vip, type, status, (CASE WHEN show_params IS NULL THEN "" ELSE show_params END) show_params FROM `' . TABLE_PREFIX . 'gift` WHERE  `status` = \'1\'  AND `delete_time` IS NULL  AND `id` = ' . $giftId . ' LIMIT 1');
        if (empty($res)) return false;
        return $res[0];
    }

    public static function getWeekStarGift($giftId, $endTime)
    {
        global $db;

        $res = $db->query('SELECT min_num, rename_uid, rename_expire_time, rename_profit_rate FROM `' . TABLE_PREFIX . 'week_star_gift` WHERE `end_time` = '. $endTime .'  AND `gift_id` = ' . $giftId . ' ORDER BY id desc LIMIT 1');
        if (empty($res)) return false;
        return $res[0];
    }

    public static function updatePkScore($pk_id, $update)
    {
        global $db;

        $db->update(TABLE_PREFIX . 'live_pk')->cols($update)->where('id=' . $pk_id)->query();
    }



    //变更金币值
    protected static function changeBean($type, $inputData)
    {
        global $db;
        $userId = $inputData['user_id'];
        $total = $inputData['total'];
        $tradeType = $inputData['trade_type'];//vip gift barrage
        $tradeNo = $inputData['trade_no'];
        $typeNames = array('inc' => '收入', 'exp' => '支出');
        if (!array_key_exists($type, $typeNames)) return self::setError('111变更类型不正确');
        if (empty($tradeType)) return self::setError('222交易类型不能为空');
        if (!preg_match('/^[0-9]+$/', $total) || $total <= 0) return self::setError(APP_BEAN_NAME . '数额不正确');
        Db::startTrans();
        if (!is_array($userId)) {
            $user = User::getUser($userId);
        } else {
            $user =& $inputData['user_id'];
            $userId = $user['user_id'];
        }
        if (empty($user)) {
            Db::rollback();
            return self::setError('444用户不存在');
        }
        //支出
        if ($type == 'exp') {
            if ($user['pay_status'] != '1') {
                Db::rollback();
                return self::setError('555账户支付功能已禁用');
            }
            if ($tradeType == 'cash' && $user['cash_status'] != '1') {
                Db::rollback();
                return self::setError('666账户提现功能已禁用');
            }
            if ($total > $user['bean']) {
                Db::rollback();
                return self::setError(APP_BEAN_NAME . '不足', 1005);//1005
            }
        }

        try {
            $log_no = get_order_no('log');
        } catch (\Exception $exception) {
            Db::rollback();
            return self::setError('timeout');
        }

        $log['log_no'] = $log_no;
        $log['user_id'] = $userId;
        $log['type'] = $type;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_bean'] = $user['total_bean'];
        $log['last_fre_bean'] = $user['fre_bean'];
        $log['last_bean'] = $user['bean'];
        $log['client_ip'] = isset($inputData['client_ip']) ? $inputData['client_ip'] : $_SERVER['REMOTE_ADDR'];
        $log['app_v'] = isset($inputData['app_v']) ? $inputData['app_v'] : '';
        $log['create_time'] = time();
        $loss_bean = $user['loss_bean'];//不参与业绩统计的额度
        $lossTotal = 0;
        //更新金币表
        $update['bean'] = $type == 'inc' ? ($user['bean'] + $total) : ($user['bean'] - $total);
        $update['fre_bean'] = $user['fre_bean'];
        $update['total_bean'] = $update['bean'] + $update['fre_bean'];
        $update['last_change_time'] = time();
        //统计
        if ($type == 'inc') {
            if ($tradeType == 'recharge') {
                //累计充值的
                $update['recharge_total'] = $user['recharge_total'] + $total;
            }
        } else {
            $update['last_pay_time'] = time();//最后一次支出时间
            if ($loss_bean > 0) {
                $lossTotal = $total > $loss_bean ? $loss_bean : $total;
                $update['loss_bean'] = $loss_bean - $lossTotal;
            }
            if ($tradeType == 'cash') {
                //累计提现的
                $update['cash_total'] = $user['cash_total'] + $total;
            } else {
                //累计消费的
                $update['pay_total'] = $user['pay_total'] + $total;
                //总消费转为经验值
                $rate = bg_get_config('exp_rate');
                $incExp = $rate * $total;
                $userUpdate['exp'] = $user['exp'] + $incExp;
                $nowLv = self::getFillLv($userUpdate['exp']);
                //升级
                if ($nowLv > $user['level']) {
                    $userUpdate['level'] = $nowLv;
                    $userUpdate['last_upgrade_time'] = time();//最近升级时间
                }
            }
        }
        $num = $db->update(TABLE_PREFIX . 'bean')->cols($update)->where('id=' . $user['bean_id'])->query();

        $typeName = $typeNames[$type];

        if (!$num) {
            Db::rollback();
            return self::setError("{$typeName}失败[01]");
        }
        $user = array_merge($user, $update);
        if (!empty($userUpdate)) {
            $userNum = $db->update(TABLE_PREFIX . 'user')->cols($userUpdate)->where('user_id = ' . $user['user_id'])->query();
            if (!$userNum) {
                Db::rollback();
                return self::setError("{$typeName}失败[01.1]");
            }
            $user = array_merge($user, $userUpdate);
        }
        User::updateRedis($user['user_id'], array_merge($update, $userUpdate ? $userUpdate : []));//同步Redis
        $log = array_merge($log, array(
            'fre_bean' => $update['fre_bean'],
            'bean' => $update['bean'],
            'total_bean' => $update['total_bean']
        ));
        $id = $db->insert(TABLE_PREFIX . 'bean_log')->cols($log)->query();
        if (!$id) {
            Db::rollback();
            return self::setError("{$typeName}失败[02]");
        }
        $update['log_no'] = $log['log_no'];
        Db::commit();

        if ($type == 'exp')
        {
            // 用户英雄榜
            if (in_array($tradeType, self::$heroesRank)) self::updateHeroesRank($user, 'gift', $total);

            //股权榜
            self::updateHeroesRank($user, 'all', $total);

            $kpiLog = $log;

            if ($loss_bean > 0) {
                $kpiLog['total'] = $kpiLog['total'] - $lossTotal;//扣除不参与统计的
                $kpiLog['loss_total'] = $lossTotal;
            }

            /**
             * 统计业绩
             *
             * 只统计预定类型和真实用户
             * 1、直播间赠送礼物
             * 2、弹幕
             * 3、付费直播
             * 4、直播间赠送道具(按道具价值计)
             *
             */
            if (in_array($tradeType, self::$achievement) && $user['isvirtual'] == 0)
            {
                $kpi = new Kpi($kpiLog['create_time']);

                $kpi->cons($inputData['to_uid'], $user, $kpiLog);
            }
        }

        return $update;
    }


    //变更谷子值
    protected static function changeMillet($type, $inputData)
    {
        global $db, $redis;
        $total = (int)$inputData['total'];
        $tradeType = $inputData['trade_type'];
        $tradeNo = $inputData['trade_no'];
        $typeNames = array('inc' => '收入', 'exp' => '支出');
        if (empty($tradeType)) return self::setError('交易类型222不能为空');
        if (!array_key_exists($type, $typeNames)) return self::setError('变更类型111不正确');
        if (!preg_match('/^[0-9]+$/', $total) || $total <= 0) return self::setError(APP_MILLET_NAME . '数额不正确');

        if (is_array($inputData['user_id'])) {
            $user =& $inputData['user_id'];
        } else {
            $user = User::getUser($inputData['user_id']);
        }
        if (empty($user) || !isset($user['isvirtual'])) {
            return self::setError('用户333不存在');
        }

        $redisKey = 'cont_uid_resume:'.$user['user_id'];
        $haskey =self::submit_verify($redisKey);
        while (!$haskey) {
            $haskey = $redis->get($redisKey) ? false : true;
        }
        $user = User::getUser($user['user_id']);

        if ($type == 'inc') {
            if (is_array($inputData['cont_uid'])) {
                $contributor =& $inputData['cont_uid'];
            } else {
                $contributor = User::getUser($inputData['cont_uid']);
            }
            if (empty($contributor) || !isset($contributor['isvirtual'])) return self::setError('用户身份信息缺失');
            $contIsvirtual = $contributor['isvirtual'];
        } else {
            $contributor = [];
            $contIsvirtual = 0;
        }
        //支出
        if ($type == 'exp') {
            if ($user['isvirtual'] == 1 && $tradeType == 'cash') {
                return self::setError('虚拟用户不允许结算' . APP_MILLET_NAME);
            }
            if ($user['millet_status'] != '1') {
                return self::setError(APP_MILLET_NAME . '已冻结');
            }
            if ($total > $user['millet']) {
                return self::setError(APP_MILLET_NAME . '不足');
            }
        }
        Db::startTrans();
        try {
            $log_no = get_order_no('log');
        } catch (\Exception $exception) {
            Db::rollback();
            return self::setError('timeout');
        }
        $log['log_no'] = $log_no;
        $log['user_id'] = $user['user_id'];
        $log['cont_uid'] = $contributor['user_id'];
        $log['type'] = $type;
        $log['isvirtual'] = $contIsvirtual;
        $log['total'] = $total;
        $log['trade_type'] = $tradeType;
        $log['trade_no'] = $tradeNo;
        $log['last_total_millet'] = $user['total_millet'];
        $log['last_fre_millet'] = $user['fre_millet'];
        $log['last_millet'] = $user['millet'];
        $log['last_isvirtual_millet'] = $user['isvirtual_millet'];
        $log['client_ip'] = isset($inputData['client_ip']) ? $inputData['client_ip'] : $_SERVER['REMOTE_ADDR'];
        $log['app_v'] = isset($inputData['app_v']) ? $inputData['app_v'] : '';
        $log['exchange_type'] = isset($inputData['exchange_type']) ? $inputData['exchange_type'] : '';
        $log['exchange_total'] = isset($inputData['exchange_total']) ? $inputData['exchange_total'] : '';
        $log['exchange_id'] = isset($inputData['exchange_id']) ? $inputData['exchange_id'] : 0;
        $log['create_time'] = time();
        $millet = $user['millet'];
        $fre_millet = $user['fre_millet'];
        $total_millet = $millet + $fre_millet;
        $isvirtual_millet = $user['isvirtual_millet'];
        if ($type == 'inc') {
            // $weekNum = "anchor_millet:w:".DateTools::getWeekNum();
            $dayNum = "anchor_millet:d:".date('Ymd');
            $redis->zIncrBy($dayNum, $total, $user['user_id']);
        }
        //更新用户表
        if ($contIsvirtual == 0) {
            $millet = $type == 'inc' ? ($user['millet'] + $total) : ($user['millet'] - $total);
            $total_millet = $millet + $fre_millet;
            $update['millet'] = $millet;
            $update['fre_millet'] = $fre_millet;
            $update['total_millet'] = $total_millet;
        } else {
            $isvirtual_millet = $type == 'inc' ? ($user['isvirtual_millet'] + $total) : ($user['isvirtual_millet'] - $total);
            $update['isvirtual_millet'] = $isvirtual_millet;
        }

        if ($type == 'inc') {
            //历史总获得谷子数量
            if ($contIsvirtual == 0) {
                $update['his_millet'] = $user['his_millet'] + $total;
            } else {
                $update['his_isvirtual_millet'] = $user['his_isvirtual_millet'] + $total;
            }
        } else {
            if ($tradeType == 'cash') {
                $update['millet_cash_total'] = $user['millet_cash_total'] + $total;
            }
        }
        $typeName = $typeNames[$type];

        if (!empty($update))
        {
            $num = $db->update(TABLE_PREFIX . 'user')->cols($update)->where('user_id = ' . $user['user_id'])->query();
            if (!$num) {
                Db::rollback();
                Logger::info(TABLE_PREFIX . 'user update error' . $user['user_id'], 'userMilletLog');
                return self::setError("{$typeName}失777败[01]");
            }
            $user = array_merge($user, $update);
            User::updateRedis($user['user_id'], $update);//同步Redis
        }
        $redis->del($redisKey);

        $log = array_merge($log, array(
            'fre_millet' => $fre_millet,
            'millet' => $millet,
            'total_millet' => $total_millet,
            'isvirtual_millet' => $isvirtual_millet
        ));

        $id = $db->insert(TABLE_PREFIX . 'millet_log')->cols($log)->query();

        if (!$id)
        {
            Db::rollback();
            Logger::info(TABLE_PREFIX . 'millet_log insert error' . $user['user_id'], 'userMilletLog');
            return self::setError("{$typeName}失888败[02]");
        }

        $update['log_no'] = $log['log_no'];
        Db::commit();
        //这个条件取消了$user['is_anchor'] == '1'

        if ($type == 'inc')
        {
            //获得米粒转为经验值
            $rate = bg_get_config('exp_rate');
            $incExp = $rate * $total;
            $TABLE_PREFIX = TABLE_PREFIX;
            $db->query("UPDATE {$TABLE_PREFIX}anchor set anchor_exp=anchor_exp+{$incExp} where user_id={$user['user_id']}");
            $anchor = Db::findItem('anchor', ['user_id' => $user['user_id']], '', false);
            if ($anchor) {
                $nowLv = self::getFillLv($anchor['anchor_exp'], 'anchor');
                //升级
                if ($nowLv > $anchor['anchor_lv']) {
                    $db->query("UPDATE {$TABLE_PREFIX}anchor set anchor_lv={$nowLv} where user_id={$user['user_id']}");
                }
            }

            /**
             * 统计主播业绩
             *
             * 只统计预定类型和真实用户
             * 1、直播间赠送礼物
             * 2、弹幕
             * 3、付费直播
             * 4、直播间赠送道具(按道具价值计)
             *
             */
            if (in_array($tradeType, self::$achievement) && $contIsvirtual == 0)
            {
                //如果贡献者为真实用户
                $kpi = new Kpi($log['create_time']);

                $kpi->millet($contributor, $user, $log);
            }
        }

        return $update;
    }



    //更新主播魅力榜(和收益相关)
    public static function updateCharmRank($user, $total)
    {
        global $redis;
        $hisk = 'rank:charm:history';//总历史榜
        $yk = 'rank:charm:y:' . date('Y');//年榜
        $mk = 'rank:charm:m:' . date('Ym');//月榜
        $wk = 'rank:charm:w:' . DateTools::getWeekNum();//周榜
        $dk = 'rank:charm:d:' . date('Ymd');//日榜
        $userId = $user['user_id'];
        //同步增长
        $redis->zIncrBy($hisk, $total, $userId);
        $redis->zIncrBy($yk, $total, $userId);
        $redis->zIncrBy($mk, $total, $userId);
        $redis->zIncrBy($wk, $total, $userId);
        $redis->zIncrBy($dk, $total, $userId);
    }

    /**
     * zack  当前直播间  用户收到的礼物总额
     * @param $user
     * @param $total
     * @param $roomId
     */
    public static function updateRoomRank($user, $anchorId, $total, $roomId)
    {
        global $redis;
        $userId = $user['user_id'];
        $total_key = 'rank:room:' . $roomId; //当前房间收到的总额
        $anchor_key = 'rank:anchor:'. $roomId; //当前直播间用户收到的礼物总额
        $user_rank_key = 'rank:user:anchor:rank:'. $roomId; //当前直播间贡献的用户
        $redis->zIncrBy($total_key, $total, $roomId);
        $redis->zIncrBy($anchor_key, $total, $anchorId);
        $redis->zIncrBy($user_rank_key, $total, $userId);
    }

    //从主播魅力榜中获取主播的谷子数量
    public static function getMilletFromCharmRank($userId, $range)
    {
        global $redis;
        $key = "rank:charm:{$range}";
        $score = $redis->zScore($key, $userId);
        return (int)$score;
    }


    //更新英雄榜(虚拟用户不算在内、和消费相关)
    public static function updateHeroesRank($user, $type, $total)
    {
        global $redis;
        $hisk = "rank:heroes:{$type}:history";//总历史榜
        $yk = "rank:heroes:{$type}:y:" . date('Y');//年榜
        $mk = "rank:heroes:{$type}:m:" . date('Ym');//月榜
        $wk = "rank:heroes:{$type}:w:" . DateTools::getWeekNum();//周榜
        $dk = "rank:heroes:{$type}:d:" . date('Ymd');//日榜
        $userId = $user['user_id'];
        //同步增长
        $redis->zIncrBy($hisk, $total, $userId);
        $redis->zIncrBy($yk, $total, $userId);
        $redis->zIncrBy($mk, $total, $userId);
        $redis->zIncrBy($wk, $total, $userId);
        $redis->zIncrBy($dk, $total, $userId);
    }


    //用户给主播的贡献榜(和收益相关)
    public static function updateContrRank($user, $anchorId, $total)
    {
        global $redis;
        $prefix = $user['isvirtual'] == 0 ? 'rank:contr:real' : 'rank:contr:isvirtual';//虚拟号
        $hisk = "{$prefix}:{$anchorId}:history";//总历史榜
        $yk = "{$prefix}:{$anchorId}:y:" . date('Y');//年榜
        $mk = "{$prefix}:{$anchorId}:m:" . date('Ym');//月榜
        $wk = "{$prefix}:{$anchorId}:w:" . DateTools::getWeekNum();//周榜
        $dk = "{$prefix}:{$anchorId}:d:" . date('Ymd');//日榜
        $userId = $user['user_id'];
        //同步增长
        $redis->zIncrBy($hisk, $total, $userId);
        $redis->zIncrBy($yk, $total, $userId);
        $redis->zIncrBy($mk, $total, $userId);
        $redis->zIncrBy($wk, $total, $userId);
        $redis->zIncrBy($dk, $total, $userId);
    }


    //统计礼物使用情况
    protected static function stat($giftId, $num, $userId, $type)
    {
        global $db;
        $consumeInfo = $db->select('*')->from(TABLE_PREFIX . 'user_giftsta')->where('gift_id = :gift_id AND user_id = :user_id AND type = :type')->bindValues(array('gift_id' => $giftId, 'user_id' => $userId, 'type' => $type))->row();
        $consume = array('last_time' => time());
        if (empty($consumeInfo)) {
            $consume['type'] = $type;
            $consume['user_id'] = $userId;
            $consume['gift_id'] = $giftId;
            $consume['total'] = $num;
            $res = $db->insert(TABLE_PREFIX . 'user_giftsta')->cols($consume)->query();
        } else {
            $consume['total'] = $consumeInfo['total'] + $num;
            $res = $db->update(TABLE_PREFIX . 'user_giftsta')->cols($consume)->where('id=' . $consumeInfo['id'])->query();
        }
        return $res;
    }

    //获取满足的等级
    public static function getFillLv($exp, $type = 'user')
    {
        global $db, $redis;
        $typeName = ($type == 'user') ? 'exp_level' : "{$type}_exp_level";
        if (!$redis->exists("config:{$typeName}")) {
            $levelArr = $db->query("SELECT levelid,level_up FROM " . TABLE_PREFIX . "{$typeName} ORDER BY level_up ASC");
            if (empty($levelArr)) return false;
            foreach ($levelArr as $item) {
                $redis->zAdd('config:' . $typeName, $item['level_up'], $item['levelid']);
            }
        }
        $lv = $redis->zRevRangeByScore('config:' . $typeName, $exp, 0, array('withscores' => TRUE, 'limit' => array(0, 1)));
        if ($lv === false) return false;
        foreach ($lv as $k => $v) {
            return $k;
        }
        return false;
    }


    public static function getCode()
    {
        return self::$code;
    }


    public static function getMessage()
    {
        return self::$message;
    }


    public static function getDisplayMillet($anchorId, $time = null)
    {
        global $redis;
        $time = isset($time) ? $time : time();
        $days = DateTools::getWeekNodes('d', $time, 'Ymd');
        $total = 0;
        foreach ($days as $day) {
            $key = "kpi:anchor:all:millet:d:{$day}";
            $score = $redis->zScore($key, $anchorId);
            $total += (int)$score;
        }
        return $total;
    }

    public static function submit_verify($redis_key, $time = 2)
    {
        global $db, $redis;
        $is_lock = $redis->setnx($redis_key, time());
        if ($is_lock == true) {
            $redis->expireAt($redis_key, time() + $time);
            return true;
        } else {
            if ($redis->get($redis_key) + $time < time()) {
                $redis->del($redis_key);
                return true;
            } else {
                return false;
            }
        }
    }
}