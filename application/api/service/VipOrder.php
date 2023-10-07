<?php

namespace app\api\service;
use think\Db;
use app\common\service\Service;

class VipOrder extends Service
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getList($get = array(), $offset = 0, $length = 10)
    {
        $db = Db::name('vip_order');
        $this->setWhere($db, $get)->setOrder($db, $get);
        $list = $db->field('order_no,user_id,name,thumb,length,unit,price,pay_status,pay_time,new_vip_expire,settlement,rmb,create_time')
            ->limit($offset, $length)->select();
        $arr = [];
        foreach ($list as &$item) {
            $tmp['user_id'] = (string)$item['user_id'];
            $tmp['order_no'] = $item['order_no'];
            $tmp['price'] = $item['price'];
            $tmp['length'] = $item['length'];
            $tmp['unit'] = $item['unit'];
            $tmp['thumb'] = $item['thumb'];
            $tmp['title'] = '购买' . $item['name'];
            $tmp['descr'] = "【{$item['order_no']}】 消耗" . ($item['settlement'] == 'rmb' ? ($item['rmb'] . '元') : ($item['price'] . APP_BEAN_NAME));
            $tmp['pay_status'] = $item['pay_status'];
            $tmp['create_time'] = date('Y-m-d', $item['create_time']);
            $tmp['pay_time'] = $item['pay_time'] ? date('Y-m-d', $item['pay_time']) : 0;
            $arr[] = $tmp;
        }
        return $arr;
    }

    protected function setWhere(&$db, $get)
    {
        $where = array('pay_status' => '1', 'user_id' => $get['user_id']);
        $db->where($where);
        return $this;
    }

    protected function setOrder(&$db, $get)
    {
        if (empty($get['sort'])) {
            $db->order('create_time desc');
        }
        return $this;
    }
}