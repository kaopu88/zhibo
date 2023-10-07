<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class MilletLog extends Service
{
    public function getTotal($get){
        $this->db = Db::name('millet_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['type'])) {
            $where['type'] = $get['type'];
        }
        if ($get['trade_type'] != '') {
            $where['trade_type'] = $get['trade_type'];
        }
        if ($get['isvirtual'] != '') {
            $where['isvirtual'] = $get['isvirtual'];
        }
        if (trim($get['user_id']) != '') {
            $where['user_id'] = trim($get['user_id']);
        }
        if (trim($get['cont_uid']) != '') {
            $where['cont_uid'] = trim($get['cont_uid']);
        }
        if ($get['exchange_type'] != '') {
            $where['exchange_type'] = $get['exchange_type'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','trade_no,log_no,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        //$order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('millet_log');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'cont_uid');
        list($giftIds) = self::getIdsByList($result, 'exchange_id', true);
        $giftService = new Gift();
        $giftList = $giftService->getGiftsByIds($giftIds);
        foreach ($result as &$item) {
            if (!empty($item['cont_uid'])) {
                $item['to_user'] = self::getItemByList($item['cont_uid'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if ($item['exchange_id'] != '0' && $item['exchange_type']=='gift') {
                $item['gift_info'] = self::getItemByList($item['exchange_id'], $giftList, 'id');
            }
        }
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('millet_log');
        $count    = $this->db->where($condition)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->limit($start_row . "," . $page_size)
                ->select();
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int)($count / $page_size) + 1;
            }
        }
        return array(
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }

}
