<?php


namespace app\api\service\live;


use app\api\service\LiveBase2;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use think\Db;

class Payment extends LiveBase2
{
    //房间付费
    public function livePay($room_id)
    {
        $room = $this->getRoomOne($room_id);//$this->getTableORM('live')->findOne('id='.$room_id, 'room_model, title, type, type_val, user_id');

        if (empty($room)) return make_error('直播间已关闭');

        if (!in_array($room['type'], [self::CHARGE_TYPE, self::TIME_CHARGE_TYPE])) return make_error('非付费直播间');

        if ($room['type'] != self::TIME_CHARGE_TYPE) {
            $isPay = $this->verifyPay($room_id);

            if ($isPay) return make_error('此直播间您己付费,无需再次付费');
        }

        $trade_no = get_order_no('live');

        $coreSdk = new CoreSdk();

        $pay = $coreSdk->post('bean/conversion', [
            'user_id' => USERID,
            'to_uid' => $room['user_id'],
            'trade_type' => 'live',
            'trade_no' => $trade_no,
            'total' => $room['type_val'],
            'client_seri' => ClientInfo::encode()
        ]);

        if (empty($pay)) return $coreSdk->getError();

        $data = [
            'room_id' => $room_id,
            'room_bean' => $room['type_val'],
            'user_id' => USERID,
            'anchor_id' => $room['user_id'],
            'pay_bean' => $room['type_val'],
            'trade_no' => $trade_no,
            'room_title' => $room['title'],
            'room_model' => $room['room_model'],
            'create_time' => time(),
        ];

        $res = Db::name('live_pay_order')->insert($data);

        if ($res === false) return make_error('记录付费记录错误');

        $this->redis->zadd(self::$livePrefix . $room_id . self::$livePayKey, $data['create_time'], USERID);

        return $res !== false;
    }
}