<?php


namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class FriendCode extends Service
{
    public function getTotal($get){
        $this->db = Db::name('user_friend_code');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user_friend_code');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $user = new \bxkj_module\service\User();
        foreach ($result as &$value) {
            if( !empty($value['friend_id']) ){
                $friend_info = $user->getUser($value['friend_id'], 0, 'user_id,nickname,avatar,phone');
                $value['friend_info'] = $friend_info;
            }
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['f.id'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function setWhere($get){
        $where = [];
        $this->db->alias('f');
        $this->db->join('__USER__ user', 'f.user_id=user.user_id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.remark_name,user.phone');
        $this->db->field('f.*');
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        return $this;
    }
}