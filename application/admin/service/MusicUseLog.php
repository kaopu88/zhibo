<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class MusicUseLog extends Service
{
    protected static $scene = ['video'=>'短视频', 'live'=>'直播间'];

    public function getTotal($get){
        $this->db = Db::name('music_use_log');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    private function setJoin()
    {
        $this->db->alias('mul')->join('__USER__ user', 'user.user_id=mul.user_id', 'LEFT');
        $this->db->join('__MUSIC__ m', 'm.id=mul.music_id', 'LEFT');
        $this->db->field('mul.*,m.link,m.image,m.title,m.is_original,m.lrc_link');
        return $this;
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['scene'])) {
            $where['mul.scene'] = $get['scene'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number m.id','m.title,number m.id');
        $this->db->setKeywords(trim($get['user_keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['mul.create_time'] = 'desc';
        $order['mul.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('music_use_log');
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
            $item['scene_str'] = self::$scene[$item['scene']];
        }
    }
}
