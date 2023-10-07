<?php


namespace app\promoter\service;

use bxkj_module\service\Service;
use think\Db;

class PromoterApply extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('promotion_relation_apply');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('promotion_relation_apply');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $user = new \bxkj_module\service\User();
        foreach ($result as &$item) {
            $user_info = $user->getUser($item['user_id']);
            $promoter_info = $user->getUser($item['promoter_uid']);
            $item['user_nickname'] = $user_info['nickname'];
            $item['user_avatar'] = img_url($user_info['avatar'],'','avatar');

            $item['promoter_nickname'] = $promoter_info['nickname'];
            $item['promoter_avatar'] = img_url($promoter_info['avatar'],'','avatar');
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        $order['create_time'] = 'DESC';
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [['agent_id', '=', AGENT_ID]];
        if ($get['user_id'] != '') {
            $where[] = ['user_id', '=', $get['user_id']];
        }
        if ($get['promoter_uid'] != '') {
            $where[] = ['promoter_uid', '=', $get['promoter_uid']];
        }
        $this->db->where($where);
        return $this;
    }
}