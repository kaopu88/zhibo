<?php

namespace app\admin\service;

use bxkj_module\service\Bean;
use bxkj_module\service\ExpLevel;
use think\Db;

class RecommendContentUser extends \bxkj_module\service\User
{
    public function getTotal($get)
    {
        $this->db = Db::name('recommend_content');
        $this->setWhere($get)->setJoin();
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('recommend_content');
        $this->setWhere($get)->setOrder($get)->setJoin();
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            self::parseUser($item);
            $item['rec_num'] = Db::name('recommend_content')->where(['rel_type' => 'user', 'rel_id' => $item['user_id']])->count();
            $item['agent_num'] = Db::name('promotion_relation')->where(['user_id' => $item['user_id']])->count();
            if ($item['is_anchor'] == '1') {
                $item['anchor_info'] = Db::name('anchor')->alias('anchor')
                    ->field('anchor.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                    ->where(['anchor.user_id' => $item['user_id']])
                    ->join('__AGENT__ agent', 'agent.id=anchor.agent_id')->find();
            }
            if ($item['is_promoter'] == '1') {
                $item['promoter_info'] = Db::name('promoter')->alias('promoter')
                    ->field('promoter.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                    ->where(['promoter.user_id' => $item['user_id']])
                    ->join('__AGENT__ agent', 'agent.id=promoter.agent_id')->find();
            }
        }
        Bean::parseBeanAccForUsers($result);
        return $result;
    }

    private function setJoin()
    {
        $this->db->alias('rc')->join('__USER__ user', 'rc.rel_id=user.user_id', 'LEFT');
        $this->db->join('__RECOMMEND_SPACE__ rs', 'rc.rec_id=rs.id', 'LEFT');
        $this->db->field('rc.id rc_id,rc.rel_id,rc.sort,user.*,rs.name rs_name');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        $order['rc.sort'] = 'desc';
        $order['rc.create_time'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [['delete_time', 'null']];
        if ($get['rec_id'])
        {
            $where[] = ['rc.rec_id', '=', $get['rec_id']];
        }
        if ($get['rel_type'])
        {
            $where[] = ['rc.rel_type', '=', $get['rel_type']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,user.remark_name,number user.phone');
        $this->db->where($where);
        return $this;
    }

}