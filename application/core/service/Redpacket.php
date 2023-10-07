<?php
namespace app\core\service;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;

class Redpacket extends Service
{
    //红包自动退还--关闭直播间的时候调用
    public function backRedPacket($room_id){
        $RedList=Db::name('activity_red_detail')->field('red_id,sum(money) as total_money')->where(['room_id'=>$room_id])->group('red_id')->select();
        foreach ($RedList as $value) {
            $RedPacket = Db::name('activity_red_packet')->where(['id'=>$value['red_id']])->find();
            if($RedPacket['price']>$value['total_money']){
                $tradeNo = get_order_no('red_packet');
                $coreSdk = new CoreSdk();
                $pay = $coreSdk->incBean([
                    'user_id' => $RedPacket['user_id'],
                    'trade_type' => 'red_packet',
                    'trade_no' => $tradeNo,
                    'total' => ($RedPacket['price']-$value['total_money']),
                    'client_seri' => ClientInfo::encode()
                ]);
                if (empty($pay)) return false;
            }
        }
        return true;
    }
}