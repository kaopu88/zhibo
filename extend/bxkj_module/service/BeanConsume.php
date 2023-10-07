<?php

namespace bxkj_module\service;

use bxkj_module\exception\Exception;
use think\Db;

class BeanConsume extends BaseConsume
{
    public function __construct(&$user)
    {
        parent::__construct($user);
    }

    public function preConsume()
    {
        $num = $this->order['num'];
        $total = floor(bcdiv($this->user['bean'], $this->order['price']));
        $exp = min($num, $total);
        $this->order['num'] -= $exp;
    }

    public function consume()
    {
        $num = $this->order['num'];
        $total = floor(bcdiv($this->user['bean'], $this->order['price']));
        $exp = min($num, $total);
        if ($exp > 0) {
            $bean = new Bean();
            $trade_type = '';
            $trade_no = '';
            $expTotalFee = bcmul($this->order['price'], $exp);
            if ($this->orderType == 'gift') {
                $trade_type = "{$this->order['scene']}_gift";
                $trade_no = $this->order['gift_no'];
            }
            $payRes = $bean->exp(array(
                'user_id' => &$this->user,
                'trade_type' => $trade_type,
                'trade_no' => $trade_no,
                'total' => $expTotalFee,
                'to_uid' => $this->order['to_uid']
            ));
            $this->order['num'] -= $exp;
        }
    }
}