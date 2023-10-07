<?php

namespace bxkj_module\service;

use bxkj_module\exception\Exception;
use think\Db;

class VideoUserRewardConsume extends BaseConsume
{
    protected $user_reward_gift;

    public function __construct(&$user)
    {
        parent::__construct($user);
    }

    public function setOrder($orderType, &$order)
    {
        parent::setOrder($orderType, $order);
        $this->user_reward_gift = Db::name('video_user_reward')
            ->where([
                ['user_id', 'eq', $this->order['user_id']],
                ['gift_id', 'eq', $this->order['gift_id']],
                ['num' , 'gt', 0],
                ['type', 'eq', 'gift']
            ])
            ->order('id asc')
            ->find();
        return $this;
    }

    public function preConsume()
    {
        $num = $this->order['num'];
        $total = (int)$this->user_reward_gift['num'];//现在已有的
        $exp = min($total, $num);
        $this->order['num'] -= $exp;
    }

    public function consume()
    {
        $num = $this->order['num'];
        $exp = min($this->user_reward_gift['num'], $num);
        if ($exp > 0) {
            Db::name('video_user_reward')->where(['id' => $this->user_reward_gift['id']])->update([
                'num' => Db::raw('num-'.$exp),
                'last_update_time' => time(),
            ]);
            $this->order['num'] -= $exp;
        }
    }
}