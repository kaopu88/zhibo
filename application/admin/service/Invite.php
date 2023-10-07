<?php


namespace app\admin\service;


use bxkj_module\service\Service;
use think\Db;

class Invite extends Service
{
    public function getTotal($get){
        $this->db = Db::name('user_invite_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user_invite_log');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $user = new \bxkj_module\service\User();
        foreach ($result as &$value) {
            if( !empty($value['user_id']) ){
                $user_info = $user->getUser($value['user_id'], 0, 'user_id,nickname,avatar,phone');
                $value['user_info'] = $user_info;
            }
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['i.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function setWhere($get){
        $where = [];
        $this->db->alias('i');
        $this->db->join('__USER__ user', 'i.promoter_uid=user.user_id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.remark_name,user.phone');
        $this->db->field('i.*');
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        return $this;
    }

}