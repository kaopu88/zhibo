<?php

namespace app\service\payment;

use app\service\DateTools;
use app\service\Db;
use app\service\Kpi;
use app\service\MilletTools;
use app\service\User;

/**
 * 赠送背包礼物
 *
 * Class BagGift
 * @package app\service\payment
 */
class BagGift extends MilletTools
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
        $trade_type = 'user_package';
        $userId = $inputData['user_id'];
        $tobxid = $inputData['to_uid'];
        $giftId = $inputData['gift_id'];
        $num = isset($inputData['num']) ? $inputData['num'] : 1;
        if (empty($userId) || empty($tobxid) || empty($giftId))
            return self::setError('缺少必要参数111');
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

        try {
            $no = get_order_no('gift');//赠送单号
            $log_no = get_order_no('log');
        } catch (\Exception $exception) {
            Db::rollback();
            return self::setError('timeout');
        }

        if (in_array($trade_type, self::$achievement) && $user['isvirtual'] == 0)
        {
            $kpi_log = [
                'log_no' => $log_no,
                'user_id' => $userId,
                'total' => $giftInfo['price']*$giftInfo['discount'],
                'trade_type' => $trade_type,
                'trade_no' => $no,
                'last_total_bean' => $user['total_bean'],
                'last_fre_bean' => $user['fre_bean'],
                'last_bean' => $user['bean'],
                'create_time' => time(),
                'is_prifit' => $inputData['is_prifit'],
            ];

            if ($user['loss_bean'] > 0) {
                $kpi_log['loss_total'] = $kpi_log['total'] > $user['loss_bean'] ? $user['loss_bean'] : $kpi_log['total'];
                $kpi_log['total'] = $kpi_log['total'] - $kpi_log['loss_total'];//扣除不参与统计的
            }

            $kpi = new Kpi(time());
            $kpi_res = $kpi->cons($tobxid, $user, $kpi_log);
            if ($kpi_res !== true) {
                Db::rollback();
                return self::setError('业绩统计错误~');
            }
        }

        //2、接收者收入谷子
        $totalMillet = $giftInfo['conv_millet'] * $num;

        $data = array(
            'user_id' => &$toUser,
            'cont_uid' => &$user,
            'trade_type' => $trade_type,
            'isvirtual' => $isvirtual,
            'trade_no' => $no,
            'total' => $totalMillet,//等值谷子*数量
            'exchange_type' => 'gift',
            'exchange_id' => $giftInfo['id'],
            'exchange_total' => (int)$num,
            'pay_type' => '',
            'leave_msg' => '',
            'reply_msg' => '',
            'msg_status' => '0',
            'pay_scene' => 'live',
            'video_id' => '0',
        );

        if ($inputData['is_prifit'] == 0) {
            $incRes = self::changeMillet('inc', $data);
        } else {
            $kpi = new Kpi(time());
            $data['is_prifit'] = $inputData['is_prifit'];
            $data['log_no'] = $log_no;
            $incRes = $kpi->millet($user,$toUser,$data);
        }


        if (!$incRes) {
            Db::rollback();
            return $incRes;
        }

        //3、生成赠送订单记录
        $log = [
            'pay_type' => $trade_type,
            'isvirtual' => $isvirtual,
            'gift_no' => $no,
            'gift_id' => $giftInfo['id'],
            'user_id' => $user['user_id'],
            'to_uid' => $toUser['user_id'],
            'price' => $giftInfo['price'],
            'name' => $giftInfo['name'],
            'type' => $giftInfo['type'],
            'cid' => $giftInfo['cid'],
            'conv_millet' => $giftInfo['conv_millet'],
            'picture_url' => $giftInfo['picture_url'],
            'num' => $num,
            'scene' => 'live',
            'create_time' => time(),
        ];

        $id = $db->insert(TABLE_PREFIX . 'gift_log')->cols($log)->query();

        if (!$id) {
            Db::rollback();
            return self::setError('赠送失败888');
        }

        Db::commit();

        //主播贡献榜以谷子为单位
        self::updateContrRank($user, $toUser['user_id'], $totalMillet);

        //主播魅力榜 收到礼物转化而来的谷子计入
        self::updateCharmRank(['user_id' => $tobxid], $totalMillet);

        $redis->zIncrBy('live_gift_' . $giftId . ':'. $tobxid , $num, 'gift_live_num');

        return true;
    }

}