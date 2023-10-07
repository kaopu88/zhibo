<?php

namespace app\common\service;

use think\Db;

class RechargeBean extends Service
{

    public function getList($get = array(), $offset = 0, $length = 20)
    {
        $db = Db::name('recharge_bean');
        $where = [
            ['status', 'eq', '1']
        ];
        if ($get['recharge_channel'] == 'ios') {
            $where[] = ['apple_id', 'neq', ''];
            $db->field('apple_id');
        } else {
            $where[] = ['apple_id', 'eq', ''];
        }
        $list = $db->where($where)
            ->field('id,name,bean_num,status,sort,price,create_time')
            ->order('sort desc,create_time desc')->limit($offset, $length)->select();
        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $item['id'] = (string)$item['id'];
            $item['name'] = APP_BEAN_NAME;
        }
        return $list ? $list : [];
    }

}