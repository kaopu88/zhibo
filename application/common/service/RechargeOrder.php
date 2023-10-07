<?php

namespace app\common\service;


use think\Db;

class RechargeOrder extends Service
{

    public function getList($get = array(), $offset = 0, $length = 20)
    {
        $db = Db::name('recharge_order');
        $where['user_id'] = $get['user_id'];
        $where['pay_status'] = '1';
        $list = $db->where($where)->limit($offset, $length)->order('create_time desc')
            ->field('id,order_no,bean_id,bean_num,quantity,total_fee,apple_id,price,name,user_id,pay_method,pay_status,pay_time,create_time')->select();
        foreach ($list as &$item) {
            $item['user_id'] = (string)$item['user_id'];
            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $item['pay_time'] = date('Y-m-d', $item['pay_time']);
            $item['pay_platform'] = enum_attr('pay_methods', $item['pay_method'], 'platform');
            $beanNum = $item['bean_num'] * $item['quantity'];
            $item['title'] = "充值{$beanNum}" . APP_BEAN_NAME;
            $item['descr'] = "【{$item['order_no']}】 消费{$item['total_fee']}元";
        }
        return $list;
    }

}