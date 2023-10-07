<?php

namespace app\admin\service;

use bxkj_common\RedisClient;
use bxkj_module\service\AnchorExpLevel;
use bxkj_module\service\Service;
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

    public function getIndexList($get, $offset = 0, $length = 20)
    {
        $fields = 'anchor.user_id,anchor.agent_id,anchor.total_millet,anchor.total_duration,anchor.create_time,
        user.nickname,user.username,user.phone,user.avatar,user.status,user.type,user.live_status,user.level';
        $this->db = Db::name('anchor');
        $this->setIndexWhere($get);
        $this->setIndexOrder($get);
        $index = $this->db->field($fields)->limit($offset, $length)->select();
        return $index ? $index : [];
    }

    protected function setIndexWhere($get)
    {
        $this->db->alias('anchor');
        $where = [];
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        return $this;
    }

    protected function setIndexOrder($get)
    {
        $this->db->order('anchor.create_time desc,anchor.user_id desc');
    }

    public function getTotal($get)
    {
        $this->db = Db::name('anchor');
        $this->setWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('anchor');
        $this->setWhere($get);
        $this->setOrder($get);
        $fields = 'user.user_id,user.nickname,user.remark_name,user.phone,user.isvirtual,user.avatar,user.sign,anchor.anchor_lv,user.gender,user.vip_expire,user.millet,anchor.create_time,user.fre_millet,user.millet_status,user.live_status,user.status,anchor.agent_id,anchor.total_millet,anchor.total_duration,anchor.cash_rate';
        $list = $this->db->field($fields)->limit($offset, $length)->select();
        $this->parseList($list);
        return $list ? $list : [];
    }

    protected function setWhere($get)
    {
        $this->db->alias('anchor');
        $where = [];
        if ($get['live_status'] != '') {
            $where[] = ['user.live_status', '=', $get['live_status']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        if ($get['join_live_film'] != '') {
            $where[] = ['anchor.join_live_film', '=', $get['join_live_film']];
        }
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $this->db->order('anchor.create_time desc,anchor.user_id desc');
    }

    public function parseList(&$list)
    {
        $agentService = new Agent();
        $agentIds = self::getIdsByList($list, 'agent_id');
        $agents = $agentService->getAgentsByIds($agentIds);
        foreach ($list as $i => &$item) {
            $item = array_merge($item, AnchorExpLevel::getAnchorLevelInfo($item['anchor_lv']));
            $item['total_duration_str'] = $item['total_duration'] > 0 ? time_str($item['total_duration']) : '无';
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agents, 'id');
            $item['location'] = $this->getLocation($item['user_id']);
        }
    }

    public function getInfo($userId)
    {
        $anchor = Db::name('anchor')->where(['user_id' => $userId])->find();
        if (empty($anchor)) return null;
        $userService = new User();
        $user = $userService->getInfo($userId);
        if (empty($user)) return null;
        $agentService = new Agent();
        $user['agent_info'] = $agentService->getAgentById($anchor['agent_id']);
        $user['promoter_info'] = $userService->getUserById($anchor['promoter_uid']);
        $anchor['total_duration_str'] = $anchor['total_duration'] > 0 ? time_str($anchor['total_duration']) : '无';
        $user['anchor'] = $anchor;
        return $user;
    }

    public function getGuards($userId, $offset = 0, $length = 20)
    {
        $key = "BG_GUARD:{$userId}";
        $redis = RedisClient::getInstance();
        $index = $redis->getSZList($key, 'asc', $offset, $length, ['member' => 'user_id', 'score' => 'guard_expire']);
        $userIds = self::getIdsByList($index, 'user_id');
        $userService = new User();
        $users = $userService->getUsersByIds($userIds);
        foreach ($index as &$item) {
            $item['agent_num'] = Db::name('promotion_relation')->where(['user_id' => $item['user_id']])->count();
            $user = self::getItemByList($item['user_id'], $users, 'user_id');
            if ($user) $item = array_merge($item, $user);
        }
        return $index;
    }

    public function getManagers($userId, $offset = 0, $length = 20)
    {
        $index = Db::name('live_manage')->where(['anchor_uid' => $userId])->limit($offset, $length)->select();
        $userIds = self::getIdsByList($index, 'manage_uid');
        $userService = new User();
        $users = $userService->getUsersByIds($userIds);
        foreach ($index as &$item) {
            $item['agent_num'] = Db::name('promotion_relation')->where(['user_id' => $item['manage_uid']])->count();
            $user = self::getItemByList($item['manage_uid'], $users, 'user_id');
            if ($user) $item = array_merge($item, $user);
        }
        return $index;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('anchor');
        $this->db->alias('anchor');
        $where = [];
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->setKeywords($keyword, 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $this->db->where($where);
        $this->db->field('anchor.user_id,user.nickname,user.username,user.phone');
        $this->db->order(['anchor.create_time' => 'desc']);
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

}