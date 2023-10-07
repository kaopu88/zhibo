<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class VipOrder extends Service
{
    protected static $vip_status = ['未开通','服务中','已过期'];
    protected static $pay_status = ['待支付','已支付'];

    public function getTotal($get)
    {
        $this->db = Db::name('vip_order');
        $this->setWhere($get)->setOrder();
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = [];
        if ($get['vip_id'] != '')
        {
            $where['vip_id'] = $get['vip_id'];
        }
        if ($get['vip_status'] != '')
        {
            $where['vip_status'] = $get['vip_status'];
        }
        if ($get['unit'] != '')
        {
            $where['unit'] = $get['unit'];
        }
        if ($get['pay_method'] != '')
        {
            $where['pay_method'] = $get['pay_method'];
        }
        if ($get['pay_status'] != '')
        {
            $where['pay_status'] = $get['pay_status'];
        }
        if ($get['settlement'] != '')
        {
            $where['settlement'] = $get['settlement'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','order_no,third_trade_no,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder()
    {
        $order = [];
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('vip_order');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset, $lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            $settlement = ['rmb'=>'人民币','bean'=>APP_BEAN_NAME];
            $item['vip_status_str'] = self::$vip_status[$item['vip_status']];
            $item['settlement_str'] = $settlement[$item['settlement']];
            $item['pay_status_str'] = self::$pay_status[$item['pay_status']];
        }
    }
}