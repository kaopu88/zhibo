<?php

namespace app\service\payment;


use app\service\Db;
use app\service\MilletTools;
use app\service\User;

class LivePay extends MilletTools
{

    /**
     * 付费直播间
     *
     * @param $inputData
     * @return bool|mixed
     */
    public static function payment($inputData)
    {
        global $db;
        $trade_type = 'live';
        $userId = $inputData['user_id'];
        $todoushuid = $inputData['to_uid'];
        if (empty($userId) || empty($todoushuid)) return self::setError('缺少必要参数111');
        Db::startTrans();
        $user = User::getUser($userId);
        if (empty($user)) {
            Db::rollback();
            return self::setError('用户不存在[01]');
        }
        $toUser = User::getUser($todoushuid);

        if (empty($toUser)) {
            Db::rollback();
            return self::setError('用户不存在[02]');
        }
        if ($inputData['totalFee'] > $user['bean']) {
            Db::rollback();
            return self::setError(APP_BEAN_NAME . '不足', 1005);//1005
        }

        try {
            $no = get_order_no('live');//支付单号
        } catch (\Exception $exception) {
            Db::rollback();
            return self::setError('timeout');
        }

        //1、支出赠送者的金币
        $payRes = self::changeBean('exp', array(
            'user_id' => &$user,
            'trade_type' => $trade_type.'_payment',
            'trade_no' => $no,
            'total' => $inputData['totalFee'],
            'to_uid' => $todoushuid
        ));
        if (!$payRes) {
            Db::rollback();
            return $payRes;
        }
        //2、接收者收入谷子

        $incRes = self::changeMillet('inc', array(
            'user_id' => &$toUser,
            'cont_uid' => &$user,
            'trade_type' => $trade_type.'_payment',
            'trade_no' => $no,
            'total' => $inputData['totalFee'],//等值谷子*数量
            'exchange_type' => $trade_type,
        ));

        if (!$incRes) {
            Db::rollback();
            return $incRes;
        }
        //3、生成赠送订单记录
        $data = [
            'room_id' => $inputData['room_id'],
            'room_bean' => $inputData['totalFee'],
            'user_id' => $user['user_id'],
            'anchor_id' => $inputData['to_uid'],
            'pay_bean' => $inputData['totalFee'],
            'trade_no' => $no,
            'room_title' => isset($inputData['title']) ? $inputData['title'] : '计费直播',
            'room_model' => $inputData['room_model'],
            'create_time' => time(),
        ];
        $res = $db->insert(TABLE_PREFIX . 'live_pay_order')->cols($data)->query(); //写入支付流水
        if (!$res) {
            Db::rollback();
            return self::setError('支付失败', 1008);
        }
        Db::commit();

        //主播贡献榜以谷子为单位
//        self::updateContrRank($user, $toUser['user_id'], $inputData['totalFee']);

        //主播魅力榜 收到礼物转化而来的谷子计入
//        self::updateCharmRank(['user_id' => $todoushuid], $inputData['totalFee']);

        return true;
    }

}