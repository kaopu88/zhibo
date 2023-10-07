<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class BeanReward extends Service
{
    public function getTotal($get){
        $this->db = Db::name('bean_reward');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    private function setJoin()
    {
        $this->db->alias('br')->join('__USER__ user', 'user.user_id=br.user_id', 'LEFT');
        $this->db->field('br.*');
        return $this;
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['type'])) {
            $where['br.type'] = $get['type'];
        }
        if ($get['start_time'] != '' &&  $get['end_time'] != '') {
            $this->db->whereTime('br.create_time', 'between', [$get['start_time'] . ' 0:0:0', $get['end_time'] . ' 23:59:59']);
        }
        $this->db->setKeywords($get['user_keyword'], 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['br.create_time'] = 'desc';
        $order['br.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('bean_reward');
        $this->setWhere($get)->setOrder($get)->setJoin();
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
        }
    }
}