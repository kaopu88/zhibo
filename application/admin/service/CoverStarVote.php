<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class CoverStarVote extends Service
{
    public function getTotal($get){
        $this->db = Db::name('cover_star_vote_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
        }
        if (trim($get['to_user_id']) != '') {
            $where[] = ['to_user_id', '=', trim($get['to_user_id'])];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','trade_no,number id');
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
        $this->db = Db::name('cover_star_vote_log');
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
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'to_user_id');
        foreach ($result as &$item) {
            if (!empty($item['to_user_id'])) {
                $item['to_user'] = self::getItemByList($item['to_user_id'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
        }
    }
}