<?php

namespace app\recharge\service;

use app\common\service\Dredge;
use bxkj_module\service\RechargeOrder;
use bxkj_module\service\Service;

class SyncHandler extends Service
{

    protected function getData($trade)
    {
        $data['rel_no'] = $trade['rel_no'];
        $data['rel_type'] = $trade['rel_type'];
        $data['trade_no'] = $trade['trade_no'];
        $data['user_id'] = $trade['user_id'];
        $data['third_trade_no'] = $trade['third_trade_no'];
        $data['third_user_id'] = $trade['third_user_id'];
        $data['third_app_key'] = $trade['third_app_key'];
        $data['pay_method'] = $trade['pay_method'];
        $data['total_fee'] = $trade['total_fee'];
        $data['create_time'] = $trade['create_time'];
        $data['extra_data'] = $trade['extra_data'] ? $trade['extra_data'] : '';
        $PAY_VERIFY_TOKEN = config('payment.pay_verify_token');
        $data['sign_time'] = time();
        $data['nonce_str'] = get_ucode(6, '1a');
        $data['sign'] = generate_sign($data, $PAY_VERIFY_TOKEN);
        return $data;
    }

    /**
     * @param $trade
     * @return bool
     */
    public function rechargeHandler($trade)
    {
        $data = $this->getData($trade);
        $rec = new RechargeOrder();
        $result = $rec->paySuccess($data);
        if (!$result) return $this->setError($rec->getError());
        return true;
    }

    /**
     * @param $trade
     * @return bool
     * @throws \bxkj_module\exception\ApiException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function taokeShopHandler($trade)
    {
        $data = $this->getData($trade);
        $rec = new Dredge();
        $result = $rec->paySuccess($data);
        if (!$result) return $this->setError($rec->getError());
        return true;
    }


    /**
     * @param $trade
     * @return bool
     */
    public function shopGoodsHandler($trade)
    {
        $data = $this->getData($trade);
        $url = "http://shopb2b.com/index.php?s=/liveapi/Pay/onlinePay";
        if($data['pay_method']=='wxpay_app'){
            $pay_method=1;
        } elseif ($data['pay_method']=='alipay_app'){
            $pay_method=2;
        } else{
            $pay_method=1;
        }
        $backData=array('out_trade_no' => $data['rel_no'],'pay_type'=>$pay_method,'third_trade_no'=>$data['third_trade_no']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $backData);
        $backData = curl_exec($ch);
        curl_close($ch);
        return $backData;
    }

}