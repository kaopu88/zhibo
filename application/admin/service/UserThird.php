<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserThird extends Service
{
    protected static $status = ['bind'=>'已绑定', 'unbind'=>'已取消'];
    protected static $type = ['weixin'=>'微信', 'qq'=>'QQ', 'weixin_code'=>'微信授权'];

    public function getTotal($get){
        $this->db = Db::name('user_third');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['user_id'] != '') {
            $where[] = ['user_id', '=', $get['user_id']];
        }
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
        $this->db = Db::name('user_third');
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
            $item['status_str'] = self::$status[$item['status']];
            $item['type_str'] = self::$type[$item['type']];
        }
    }
}

