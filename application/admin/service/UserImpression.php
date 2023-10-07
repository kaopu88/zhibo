<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserImpression extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('user_impression');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['impression_id'] != '') {
            $where['impression_id'] = $get['impression_id'];
        }
        if (trim($get['user_id']) != '') {
            $where['user_id'] = trim($get['user_id']);
        }
        if (trim($get['anchor_uid']) != '') {
            $where['anchor_uid'] = trim($get['anchor_uid']);
        }
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('user_impression');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'anchor_uid');
        foreach ($result as &$item) {
            if (!empty($item['anchor_uid'])) {
                $item['to_user'] = self::getItemByList($item['anchor_uid'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            $item['impression_str'] = Db::name('impression')->where('id', $item['impression_id'])->value('name');
        }
    }
}

