<?php

namespace app\service\payment;

use app\api\Voice;
use app\service\DateTools;
use app\service\Db;
use app\service\MilletTools;
use app\service\User;
use app\service\Distribute;

class GiveGift extends MilletTools
{

    /**
     * 赠送礼物
     *
     * @param $inputData
     * @return bool
     */
    public static function payment($inputData)
    {
        global $db, $redis;
        $trade_type = 'live';
        $userId = $inputData['user_id'];
        $tobxid = $inputData['to_uid'];
        $giftId = $inputData['gift_id'];
        $num = isset($inputData['num']) ? $inputData['num'] : 1;
        if (empty($userId) || empty($tobxid) || empty($giftId)) return self::setError('缺少必要参数111');
        if ((int)$num <= 0) return self::setError('赠送数量不正确222');
        Db::startTrans();
        $user = User::getUser($userId);
        if (empty($user)) {
            Db::rollback();
            return self::setError('用户不存在[01]333');
        }
        $isvirtual = $user['isvirtual'];
        if (!isset($isvirtual)) {
            Db::rollback();
            return self::setError('用户身份错误');
        }
        $toUser = User::getUser($tobxid);
        if (empty($toUser)) {
            Db::rollback();
            return self::setError('用户不存在[02]444');
        }
        if ($user['user_id'] == $toUser['user_id']) {
            Db::rollback();
            return self::setError('不能给自己赠送礼物555');
        }
        $giftInfo = self::getGift($giftId);
        if (empty($giftInfo)) {
            Db::rollback();
            return self::setError('礼物不存在666');
        }
        $totalFee = $giftInfo['price'] * $num;//单价*数量

        if ($totalFee > $user['bean']) {
            Db::rollback();
            return self::setError(APP_BEAN_NAME . '不足', 1005);//1005
        }

        try {
            $no = get_order_no($trade_type);//赠送单号
        } catch (\Exception $exception) {
            Db::rollback();
            return self::setError('timeout');
        }

        //1、支出赠送者的钻石
        $payRes = self::changeBean('exp', array(
            'user_id' => &$user,
            'trade_type' => $trade_type . "_gift",
            'trade_no' => $no,
            'total' => $totalFee,
            'to_uid' => $toUser['user_id']
        ));
        if (!$payRes) {
            Db::rollback();
            return $payRes;
        }

        //2、接收者收入的收益
        $totalMillet = $giftInfo['conv_millet'] * $num;


        $incRes = self::changeMillet('inc', array(
            'user_id' => &$toUser,
            'cont_uid' => &$user,
            'trade_type' => $trade_type . '_gift',
            'isvirtual' => $isvirtual,
            'trade_no' => $no,
            'total' => $totalMillet,//等值谷子*数量
            'exchange_type' => $trade_type,
            'exchange_id' => $giftInfo['id'],
            'exchange_total' => (int)$num,
            'pay_type' => '',
            'leave_msg' => '',
            'reply_msg' => '',
            'msg_status' => '0',
            'pay_scene' => 'live',
            'video_id' => '0'
        ));

        if (!$incRes) {
            Db::rollback();
            return $incRes;
        }

        //TODO 视频礼物分销加的地方
        //---------------------视频礼物分销----------------start-----------------------------------------------------------//

        $res = Distribute::distributionCommission($userId, $totalMillet, $giftId);

        //---------------------视频礼物分销----------------end-----------------------------------------------------------//

        //3、生成赠送订单记录
        $log['pay_type'] = 'gift';
        $log['isvirtual'] = $isvirtual;
        $log['gift_no'] = $no;
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
        $log['scene'] = 'live';
        $log['create_time'] = time();

        $id = $db->insert(TABLE_PREFIX . 'gift_log')->cols($log)->query();
        if (!$id) {
            Db::rollback();
            return self::setError('赠送失败888');
        }

        Db::commit();

        if ($giftInfo['cid'] == 10) {
            //说明插队礼物 进行插队操作
            Voice::jumpQueue(['room_id' => $inputData['room_id'], 'user_id' => $userId]);
        }

        //主播贡献榜以谷子为单位
        self::updateContrRank($user, $toUser['user_id'], $totalMillet);

        //主播魅力榜 收到礼物转化而来的谷子计入
        self::updateCharmRank(['user_id' => $tobxid], $totalMillet);

        //更新直播间用户获得礼物的收益
        self::updateRoomRank($user, $tobxid, $totalMillet, $inputData['room_id']);

        $redis->zIncrBy('live_gift_' . $giftId . ':' . $tobxid, $num, 'gift_live_num');
        
        //增加计算粉丝牌缓存
        $dengLevelKey = 'DengLevel:'.$toUser['user_id'].'_'.$user['user_id'];
        $count = $db->query("SELECT SUM(millet) as count FROM bx_kpi_millet WHERE get_uid = $toUser[user_id] AND cont_uid = $user[user_id]");
        $mcount = $count[0]['count'];
        $dengLevel = 0;
        if($mcount){
            $arr = $db->query("SELECT * FROM bx_deng_level WHERE level_up <= $mcount ORDER BY level_up DESC");
            $dengLevel = $arr[0]['name'];
        }
        $redis->set($dengLevelKey,$dengLevel);
        return true;
    }

}