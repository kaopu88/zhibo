<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/9/28 0028
 * Time: 下午 6:05
 */

namespace app\giftdistribute\service;

use bxkj_module\service\Service;
use bxkj_module\service\User;
use think\Db;
use think\Model;

class GiftCommissionLog extends Service
{
    public function getTotal($get){
        $this->db = Db::name('gift_commission_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getTotalAmount($get){
        $this->db = Db::name('gift_commission_log');
        $this->setWhere($get);
        return $this->db->sum('total');
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['type'])) {
            $where['type'] = $get['type'];
        }
        if (!empty(trim($get['user_id']))) {
            $where['to_uid'] = trim($get['user_id']);
        }
        if ($get['trade_type'] != '') {
            $where['trade_type'] = $get['trade_type'];
        }
        if ($get['start_time'] != '' &&  $get['end_time'] != '') {
            $this->db->whereTime('create_time', 'between', [$get['start_time'] . ' 0:0:0', $get['end_time'] . ' 23:59:59']);
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','trade_no');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth, $type = 0){
        $this->db = Db::name('gift_commission_log');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();

        $result = $result ? $result : [];
        if (empty($type)) $this->parseList($result);

        return $result;
    }

    public function parseList(&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'cont_uid');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'to_uid');

        foreach ($result as &$item) {
            if (!empty($item['to_uid'])) {
                $item[$outKey] = self::getItemByList($item['to_uid'], $recAccounts_b, $relKey);
            }
            if (!empty($item['cont_uid'])) {
                $item['cont_user'] = self::getItemByList($item['cont_uid'], $recAccounts, $relKey);
            }
            if (!empty($item['video_id'])) {
                $video = Db::name('video')->field('animate_url,cover_url')->where('id', $item['video_id'])->find();
                $item['animate_url'] = $video['animate_url'];
                $item['cover_url'] = $video['cover_url'];
            }
        }
    }

    public function getIncSum($user_id, $tradeType = null, $startTime = null, $endTime = null)
    {
        $db = Db::name('gift_commission_log');
        $where['to_uid'] = $user_id;
        $where['type'] = 'inc';
        if (isset($tradeType)) {
            if (is_string($tradeType)) {
                $pos = strpos($tradeType, ',');
                $where['trade_type'] = $pos === false ? $tradeType : explode(',', $tradeType);
            } else {
                $where['trade_type'] = $tradeType;
            }
        }

        if (isset($startTime)) {
            $db->where('create_time >= ' . $startTime);
        }
        if (isset($endTime)) {
            $db->where('create_time < ' . $endTime);
        }
        $sum = $db->where($where)->sum('commission_money');
        return $sum ? $sum : 0;
    }
}