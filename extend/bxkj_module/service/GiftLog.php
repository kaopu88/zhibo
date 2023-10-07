<?php

namespace bxkj_module\service;

use bxkj_common\ClientInfo;
use bxkj_common\DateTools;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use think\Db;

class GiftLog extends Service
{
    /**
     * 应用内赠送礼物
     *
     * @param $inputData
     * @return array|bool
     */
    public function give($inputData)
    {
        ClientInfo::refreshByParams($inputData);
        $userId = $inputData['user_id'];
        $toUserId = $inputData['to_uid'];
        $giftId = $inputData['gift_id'];
        $consumeOrders = [];
        $consumeTypes = str_to_fields($inputData['consume_order'] ? $inputData['consume_order'] : 'video_user_reward,user_package,bean');
        //默认直播
        $payScene = $inputData['pay_scene'] ? strtolower($inputData['pay_scene']) : 'live';
        $leaveMsg = $inputData['leave_msg'];//礼物留言
        $num = isset($inputData['num']) ? $inputData['num'] : 1;
        if (empty($userId) || empty($toUserId) || empty($giftId)) {
            return $this->setError('缺少必要参数');
        }
        //赠送场景
        if (!in_array($payScene, ['live', 'video'])) return $this->setError('赠送场景不支持');
        if ($payScene == 'video' && empty($inputData['video_id']) && in_array('video_user_reward', $consumeTypes)) return $this->setError('视频ID不能为空');
        if ((int)$num <= 0) return $this->setError('赠送数量不正确');
        $userModel = new User();
        self::startTrans();
        $user = $userModel->getBasicInfo($userId);
        if (empty($user)) {
            self::rollback();
            return $this->setError('用户不存在[01]');
        }
        $toUser = $userModel->getBasicInfo($toUserId);
        if (empty($toUser)) {
            self::rollback();
            return $this->setError('用户不存在[02]');
        }
        if ($user['user_id'] == $toUser['user_id']) {
            self::rollback();
            return $this->setError('不能给自己赠送礼物');
        }
        if (!isset($user['isvirtual'])) {
            self::rollback();
            return $this->setError('用户身份错误');
        }
        $where = array('status' => '1', 'delete_time' => null, 'id' => $giftId);
        $giftInfo = Db::name('gift')->where($where)->find();
        if (empty($giftInfo)) {
            self::rollback();
            return $this->setError('礼物不存在');
        }
        $privileges = $giftInfo['privileges'] ? str_to_fields($giftInfo['privileges']) : [];
        if (!empty($leaveMsg) && !in_array('leave_msg', $privileges)) {
            self::rollback();
            return $this->setError('不支持留言特权');
        }
        //处理消费(先宝箱或背包然后从余额中支付)
        foreach ($consumeTypes as $consumeType) {
            $className = '\\bxkj_module\\service\\' . parse_name($consumeType, 1, true) . "Consume";
            if (!class_exists($className)) {
                self::rollback();
                return $this->setError('不支持的消耗方式');
            }
            $consumeOrders[] = $className;
        }
        $log = [];
        //获得礼物订单号
        $no = get_order_no('gift');
        $log['gift_no'] = $no;
        $log['pay_type'] = '';
        $log['gift_id'] = $giftInfo['id'];
        $log['user_id'] = $user['user_id'];
        $log['to_uid'] = $toUser['user_id'];
        $log['price'] = $giftInfo['price'];
        $log['name'] = $giftInfo['name'];
        $log['type'] = $giftInfo['type'];
        $log['cid'] = $giftInfo['cid'];
        $log['conv_millet'] = $giftInfo['conv_millet'];
        $log['picture_url'] = $giftInfo['picture_url'];
        $log['num'] = $num;
        $log['isvirtual'] = $user['isvirtual'];
        $log['scene'] = $payScene;
        $log['leave_msg'] = $leaveMsg ? $leaveMsg : '';
        $log['msg_status'] = '0';//0未读 1 已读 2已回复
        $log['reply_msg'] = '';//回复消息
        $log['video_id'] = $inputData['video_id'] ? $inputData['video_id'] : 0;
        $log['create_time'] = time();
        $preLog = $log;
        $logNum = $log['num'];
        foreach ($consumeOrders as $className) {
            $consume = new $className($user);
            $consume->setOrder('gift', $preLog)->preConsume();
            if ($preLog['num'] < 0) {
                self::rollback();
                return $this->setError('赠送失败');
            }
            if ($preLog['num'] == 0) continue;
        }
        if ($preLog['num'] > 0) {
            self::rollback();
            return $this->setError(APP_BEAN_NAME . '不足');
        }
        foreach ($consumeOrders as $className) {
            $consume = new $className($user);
            $consume->setOrder('gift', $log)->consume();
            if ($log['num'] < 0) {
                self::rollback();
                return $this->setError('赠送失败');
            }
            if ($log['num'] == 0) continue;
        }
        if ($log['num'] > 0) {
            self::rollback();
            return $this->setError(APP_BEAN_NAME . '不足');
        }
        $totalMillet = $giftInfo['conv_millet'] * $num;
        $millet = new Millet();
        $milletTradeType = "{$payScene}_gift";
        //增加米粒
        $incRes = $millet->inc(array(
            'cont_uid' => &$user,
            'user_id' => &$toUser,
            'isvirtual' => $user['isvirtual'],//虚拟的赠送
            'trade_type' => $milletTradeType,
            'trade_no' => $no,
            'total' => $totalMillet,
            'exchange_type' => 'gift',
            'exchange_id' => $giftInfo['id'],
            'exchange_total' => (int)$num
        ));
        if (!$incRes) {
            self::rollback();
            return $this->setError($millet->getError());
        }

        //是周星礼物
       /*if($giftInfo['is_week_star'] == 1){
            $key = "week_star:gift_id:".$giftId;
            $redis = RedisClient::getInstance();
            if($redis->zcard($key) == 0){//没有数据，创建对应空间，并设置一天有效期
                $redis->expire($key, 86400);
            }
            if($recNum = $redis->zscore($key, $toUserId)){
                $num = $recNum + $num;
            }
            $redis->zAdd($key, $num, $toUserId);
        }*/

        //我的米粒贡献榜只统计直播间的
        if ($milletTradeType == 'live_gift')
        {
            $this->updateContrRank($user, $toUser['user_id'], $totalMillet);
        }
        $log['num'] = $logNum;
        $id = Db::name('gift_log')->insertGetId($log);
        if (!$id) {
            self::rollback();
            return $this->setError('赠送失败');
        }
        $log['id'] = $id;
        //$this->stat($giftInfo['id'], $num, $user['user_id'], 'give');
        //$this->stat($giftInfo['id'], $num, $toUser['user_id'], 'received');
        //Db::name('gift')->where(array('id' => $giftInfo['id']))->update(array('sales' => $giftInfo['sales'] + $num));
        self::commit();
        if ($payScene == 'video' && !empty($log['video_id'])) $this->updateVideoRewardRank($log);
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.gift', ['behavior' => 'gift', 'data' => $log]);
        $log['user'] = $user;
        $log['to_user'] = $toUser;
        return $log;
    }

    public function getList($inputData)
    {
        $fansList = Db::name('gift_log')->where(['to_uid' => $inputData['user_id']])->limit($inputData['offset'], $inputData['length'])->order('id desc')->select();
        if (empty($fansList)) return [];
        return $fansList;
    }

    /**
     * 礼物使用统计
     * @param $giftId
     * @param $num
     * @param $userId
     * @param $type
     * @return int|string
     */
    protected function stat($giftId, $num, $userId, $type)
    {
        $consumeInfo = Db::name('user_giftsta')->where(array('gift_id' => $giftId, 'user_id' => $userId, 'type' => $type))->find();
        $consume = array('last_time' => time());
        if (empty($consumeInfo)) {
            $consume['type'] = $type;
            $consume['user_id'] = $userId;
            $consume['gift_id'] = $giftId;
            $consume['total'] = $num;
            $res = Db::name('user_giftsta')->insert($consume);
        } else {
            $consume['total'] = $consumeInfo['total'] + $num;
            $res = Db::name('user_giftsta')->where(array('id' => $consumeInfo['id']))->update($consume);
        }
        return $res;
    }



    /**
     * 主播的贡献榜
     *
     * @param $user
     * @param $anchorId
     * @param $total
     */
    protected function updateContrRank($user, $anchorId, $total)
    {
        $prefix = $user['isvirtual'] == 0 ? 'rank:contr:real' : 'rank:contr:isvirtual';//虚拟号
        $redis = RedisClient::getInstance();
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


    /**
     * 更新视频奖励榜
     * @param $log
     */
    protected function updateVideoRewardRank($log)
    {
        $data = [
            'gift_id' => $log['gift_id'],
            'gift_price' => $log['price'],
            'message' => $log['leave_msg'],
            'to_uid' => $log['to_uid'],
            'user_id' => $log['user_id'],
            'video_id' => $log['video_id'],
            'create_time' => time(),
        ];

        //打赏记录入库
        Db::name('video_reward_rank')->insert($data);
    }


    /**
     * 封面女神投票
     *
     */
    public function coverStartVote($params)
    {
        ClientInfo::refreshByParams($params);
        $userId = $params['user_id'];
        $toUserId = $params['to_uid'];
        $trade_type = 'cover_star_vote';
        $trade_no = $params['trade_no'];
        $total = $params['total'];

        $userModel = new User();

        self::startTrans();

        $user = $userModel->getBasicInfo($userId);
        if (empty($user)) {
            self::rollback();
            return $this->setError('用户不存在[01]');
        }
        $toUser = $userModel->getBasicInfo($toUserId);
        if (empty($toUser)) {
            self::rollback();
            return $this->setError('用户不存在[02]');
        }

        if (!isset($user['isvirtual'])) {
            self::rollback();
            return $this->setError('用户身份错误');
        }

        $bean = new Bean();
        $payRes = $bean->exp(array(
            'user_id' => &$user,
            'trade_type' => $trade_type,
            'trade_no' => $trade_no,
            'total' => $total,
            'to_uid' => $toUserId
        ));

        if ($payRes === false) {
            self::rollback();
            return $this->setError($bean->getError());
        }

        $millet = new Millet();
        //增加米粒
        $incRes = $millet->inc(array(
            'cont_uid' => &$user,
            'user_id' => &$toUser,
            'isvirtual' => $user['isvirtual'],//虚拟的赠送
            'trade_type' => $trade_type,
            'trade_no' => $trade_no,
            'total' => $total
        ));
        if (!$incRes) {
            self::rollback();
            return $this->setError($millet->getError());
        }

        self::commit();

        $this->updateContrRank($user, $toUser['user_id'], $total);

        return true;


    }

}