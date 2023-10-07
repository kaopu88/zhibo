<?php

namespace app\service\payment;


use app\service\Db;
use app\service\MilletTools;
use app\service\User;

class Barrage extends MilletTools
{

    /**
     * 直播间飘瓶
     *
     * @param $inputData
     * @return bool
     */
    public static function payment($inputData)
    {
        $tradeType = "barrage";
        $userId = $inputData['user_id'];
        $todoushuid = $inputData['to_uid'];
        $totalFee = $inputData['total'];
        if (empty($userId) || empty($todoushuid)) return self::setError('缺少必要参数111');

        $user = User::getUser($userId);

        if (empty($user)) return self::setError('用户不存在[01]333');

        if (!isset($user['isvirtual'])) return self::setError('用户身份错误');

        $toUser = User::getUser($todoushuid);

        if (empty($toUser)) return self::setError('用户不存在[02]444');

        if ($totalFee > $user['bean']) return self::setError(APP_BEAN_NAME . '不足', 1005);//1005

        try {
            //赠送单号
            $no = get_order_no($tradeType);
        } catch (\Exception $exception) {
            return self::setError('timeout');
        }

        Db::startTrans();

        //1、支出赠送者的金币
        $payRes = self::changeBean('exp', array(
            'user_id' => &$user,
            'trade_type' => $tradeType,
            'trade_no' => $no,
            'total' => $totalFee,
            'to_uid' => $toUser['user_id']
        ));

        if (!$payRes) {
            Db::rollback();
            return $payRes;
        }

        //2、接收者收入谷子
        $incRes = self::changeMillet('inc', array(
            'user_id' => &$toUser,
            'cont_uid' => &$user,
            'trade_type' => $tradeType,
            'isvirtual' => $user['isvirtual'],
            'trade_no' => $no,
            'total' => $totalFee,//等值谷子*数量
            'exchange_type' => $tradeType,
            'exchange_id' => '',
            'exchange_total' => (int)$totalFee,
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
        Db::commit();

        //主播贡献榜以谷子为单位
        //self::updateContrRank($user, $toUser['user_id'], $totalFee);

        //主播魅力榜 收到礼物转化而来的谷子计入
        //self::updateCharmRank(['user_id' => $todoushuid], $totalFee);

        return true;
    }



}