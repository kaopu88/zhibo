<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class CashAccount extends Service
{
    protected static $type = ['alipay'=>'支付宝', 'wxpay'=>'微信', 'bank'=>'银行卡'];
    protected static $verify_status = ['未知', '有效', '无效'];

    public function getTotal($get){
        $this->db = Db::name('cash_account');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['type'] != '') {
            $where[] = ['type', '=', $get['type']];
        }
        if ($get['account_type'] != '') {
            $where[] = ['account_type', '=', $get['account_type']];
        }
        if ($get['verify_status'] != '') {
            $where[] = ['verify_status', '=', $get['verify_status']];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number user_id','name,account,number user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('cash_account');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            $item['verify_status_str'] = self::$verify_status[$item['verify_status']];
            $item['type_str'] = self::$type[$item['type']];
        }
    }

   public function find($where){
        return Db::name('cash_account')->where($where)->find();
   }
}


