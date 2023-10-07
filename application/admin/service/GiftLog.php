<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class GiftLog extends Service
{
    public function getTotal($get){
        $this->db = Db::name('gift_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['gift_no'])) {
            $where['gift_no'] = $get['gift_no'];
        }
        if ($get['msg_status'] != '') {
            $where['msg_status'] = $get['msg_status'];
        }
        if ($get['isvirtual'] != '') {
            $where['isvirtual'] = $get['isvirtual'];
        }
        if (trim($get['user_id']) != '') {
            $where['user_id'] = trim($get['user_id']);
        }
        if (trim($get['to_uid']) != '') {
            $where['to_uid'] = trim($get['to_uid']);
        }
        //$this->db->setKeywords(trim($get['keyword']),'','number id','gift_no,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
       // $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function setUnion($get) {

        if (!empty(trim($get['keyword']))) {
            $this->db->where(['id' => $get['keyword']])
                ->union('select * from '. config('database.prefix') .'gift_log where gift_no ="' . trim($get['keyword']) . '"');
        }
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('gift_log');
        $this->setWhere($get)->setOrder($get)->setUnion($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'to_uid');
        foreach ($result as &$item) {
            if (!empty($item['to_uid'])) {
                $item['to_user'] = self::getItemByList($item['to_uid'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if (!empty($item['video_id'])) {
                $video = Db::name('video')->field('animate_url,cover_url')->where('id', $item['video_id'])->find();
                $item['animate_url'] = $video['animate_url'];
                $item['cover_url'] = $video['cover_url'];
            }
        }
    }
}