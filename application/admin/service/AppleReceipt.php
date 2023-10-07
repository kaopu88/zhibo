<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class AppleReceipt extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('apple_receipt');
        $this->setWhere($get)->setOrder();
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = [];
        if ($get['user_id'] != '')
        {
            $where['user_id'] = $get['user_id'];
        }
        $this->db->where($where);
        return $this;
    }

    public function setOrder()
    {
        $order = [];
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('apple_receipt');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset, $lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $relkey = 'user_id';
        $outkey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], $fieldList = 'user_id');
        foreach ($result as &$item)
        {
            $item[$outkey] = self::getItemByList($item['user_id'], $recAccounts, $relkey);
        }
    }
}