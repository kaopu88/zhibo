<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class VideoFavorites extends Service
{
    public function getTotal($get){
        $this->db = Db::name('video_favorites');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    private function setJoin()
    {
        $this->db->alias('vf')->join('__USER__ user', 'user.user_id=vf.user_id', 'LEFT');
        $this->db->join('__VIDEO__ v', 'v.id=vf.video_id', 'LEFT');
        $this->db->field('vf.id,vf.user_id,vf.video_id,vf.create_time,v.animate_url,v.cover_url');
        return $this;
    }

    public function setWhere($get){
        $where = array();
        $this->db->setKeywords(trim($get['keyword']),'','number v.id','v.describe,number v.id');
        $this->db->setKeywords(trim($get['user_keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['vf.create_time'] = 'desc';
        $order['vf.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('video_favorites');
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

