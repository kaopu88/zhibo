<?php

namespace app\agent\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\AnchorExpLevel;
use think\Db;

class Anchor extends \bxkj_module\service\Anchor
{
    public function getIndexTotal($get)
    {
        $this->db = Db::name('anchor');
        $this->setIndexWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getIndex($get, $offset = 0, $length = 20)
    {
        $fields = 'anchor.user_id,anchor.agent_id,anchor.total_millet,anchor.total_duration,anchor.create_time,
        user.nickname,user.username,user.phone,user.avatar,user.status,user.type,user.live_status,user.level,anchor.anchor_lv,anchor.cash_rate';
        $this->db = Db::name('anchor');
        $this->setIndexWhere($get);
        $this->setIndexOrder($get);
        $index = $this->db->field($fields)->limit($offset, $length)->select();
        $agentService = new Agent();
        $agentIds = self::getIdsByList($index, 'agent_id');
        $agents = $agentService->getAgentsByIds($agentIds);
        foreach ($index as &$item) {
            $item = array_merge($item, AnchorExpLevel::getAnchorLevelInfo($item['anchor_lv']));
            $item['total_duration_str'] = $item['total_duration'] > 0 ? time_str($item['total_duration'], 's') : '无';
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agents, 'id');
            $item['location'] = $this->getLocation($item['user_id']);
            $item['agent_num'] = Db::name('promotion_relation')->where(['user_id' => $item['user_id']])->count();
        }
        return $index ? $index : [];
    }

    protected function parseList(&$index)
    {
        foreach ($index as $i => &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            $item['duration_str'] = $item['duration'] > 0 ? time_str($item['duration'], 'i') : '无';
            $item['total_duration_str'] = $item['total_duration'] > 0 ? time_str($item['total_duration'], 's') : '无';
        }
    }

    protected function setIndexWhere($get)
    {
        $this->db->alias('anchor');
        $where = [];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'anchor.');
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        if ($get['live_status'] != '') {
            $where[] = ['user.live_status', '=', $get['live_status']];
        }
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        return $this;
    }

    protected function setIndexOrder($get)
    {
        $this->db->order('anchor.create_time desc,anchor.user_id desc');
    }

    public function getSuggests($keyword, $length = 10)
    {
        $where = array();
        $this->db = Db::name('anchor');
        $this->db->alias('anchor');
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'anchor.');
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->setKeywords($keyword, 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        $this->db->field('anchor.user_id,user.nickname,user.username,user.phone');
        $this->db->order(['anchor.create_time' => 'desc', 'anchor.id' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['user_id'],
                'name' => $item['nickname'] . ($item['phone'] ? "({$item['phone']})" : '')
            ];
        }
        return $arr;
    }

    public function getInfo($userId)
    {
        if (empty($userId)) return $this->setError('主播不存在');
        $where = [['user_id', '=', $userId]];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID]);
        $anchor = Db::name('anchor')->where($where)->find();
        return $anchor;
    }

}