<?php

namespace app\agent\service;

use bxkj_module\service\Service;
use think\Db;

class LiveHistory extends Service
{
    protected static $room_model = ['直播', '录像', '电影', '游戏'];
    protected static $type = ['普通', '私密', '收费', '计费', 'VIP', '等级'];

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_history');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_history');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (!$result) return [];
        $this->parseList($get, $result);
        return $result;
    }

    public function parseList($get, &$result)
    {
        list($channels) = self::getIdsByList($result, 'room_channel', true);
        $channelList = $this->getChannelsByIds($channels);
        foreach ($result as &$item) {
            $item['room_model_txt'] = self::$room_model[$item['room_model']];
            $item['type_txt'] = self::$type[$item['type']];
            $item['channel_info'] = self::getItemByList($item['room_channel'], $channelList, 'id');
        }
    }

    public function getChannelsByIds($channels)
    {
        if (empty($channels)) return [];
        $result = Db::name('live_channel')->whereIn('id', $channels)->field('id,name')->limit(count($channels))->select();
        return $result ? $result : [];
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $this->db->alias('ls');
        $where = [];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'anchor.');
        $this->db->join('__ANCHOR__ anchor', 'ls.user_id=anchor.user_id', 'LEFT');

        if (isset($get['room_id']) && trim($get['room_id']) != '') {
            $where[] = ['ls.room_id', '=', trim($get['room_id'])];
        }
        if (isset($get['room_model'])) {
            $where[] = ['ls.room_model', '=', $get['room_model']];
        }
        if (isset($get['type'])){
            $where[] = ['ls.type', '=', $get['type']];
        }

        $this->db->where($where);

        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['id'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }
}


