<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_common\CoreSdk;
use think\Db;

class PromotionRelation extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('promotion_relation');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('promotion_relation');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('rel.id,rel.user_id,rel.promoter_uid,rel.agent_id,rel.create_time,agent.name agent_name');
        $result = $this->db->limit($offset, $length)->select();
        $result = $result ? $result : [];
        $this->parseList($get, $result);
        return $result;
    }

    protected function parseList($get, &$result)
    {
        $userId = $get['user_id'];
        $userPromoter = Db::name('promoter')->where(['user_id' => $userId])->find();
        $userAnchor = Db::name('anchor')->where(['user_id' => $userId])->find();
        $promoterUids = self::getIdsByList($result, 'promoter_uid');
        $users = $promoterUids ? Db::name('user')->whereIn('user_id', $promoterUids)->field('avatar,nickname,user_id')->select() : [];
        foreach ($result as &$item) {
            if ($item['promoter_uid']) {
                $item['promoter'] = self::getItemByList($item['promoter_uid'], $users, 'user_id');
            }
            $item['anchor_current'] = '0';
            $item['promoter_current'] = '0';
            if ($userAnchor && $userAnchor['agent_id'] == $item['agent_id']) {
                $item['anchor_current'] = '1';
            }
            if ($userAnchor && $userAnchor['agent_id'] != $item['agent_id']) {
                $item['anchor_current'] = '2';
            }
            if ($userPromoter && $userPromoter['agent_id'] == $item['agent_id']) {
                $item['promoter_current'] = '1';
            }
            if ($userPromoter && $userPromoter['agent_id'] != $item['agent_id']) {
                $item['promoter_current'] = '2';
            }
        }
    }

    private function setWhere($get)
    {
        $this->db->alias('rel');
        $this->db->join('__AGENT__ agent', 'rel.agent_id=agent.id');
        $where = [['rel.user_id', '=', $get['user_id']]];
        $this->db->where($where);
        //$this->db->setKeywords(trim($get['keyword']), '', 'number rec_account', 'pay_account,number order_no');
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['rel.create_time'] = 'desc';
            $order['rel.user_id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function tranfer($userId)
    {
        if (empty($userId)) return ['code' => 101, 'msg' => '用户uid不能为空'];
        $relation= Db::name('promotion_relation')->where(['user_id' => $userId])->find();
        if (empty($relation)) return ['code' => 102, 'msg' => '没有绑定关系'];
        return ['code' => 200, 'data' => $relation];
    }
}