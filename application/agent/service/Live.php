<?php

namespace app\agent\service;


use bxkj_common\CoreSdk;
use bxkj_module\service\Service;
use think\Db;

class Live extends Service
{
    protected static $model = ['直播', '录播', '电影', '游戏', '语聊', '电台'];
    protected static $type = ['普通', '私密', '收费', '计费', 'VIP', '等级'];

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getOne($where)
    {
        $res = Db::name('live')->where($where)->find();
        return $res;
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();

        if (empty($result)) return [];

        $CoreSdk = new CoreSdk();

        $room_ids = array_column($result, 'id');

        $roomAudiences = $CoreSdk->post('zombie/getRoomAudiences', ['room_id' => $room_ids]);

        $roomRobots = $CoreSdk->post('zombie/getRoomRobots', ['room_id' => $room_ids]);

        foreach ($result as &$item) {
            $item['model_str'] = self::$model[$item['room_model']];
            $item['type_str'] = self::$type[$item['type']];
            $item['audience'] = empty($roomAudiences[$item['id']]) ? 0 : $roomAudiences[$item['id']];
            $item['robot'] = empty($roomRobots[$item['id']]) ? 0 : $roomRobots[$item['id']];
            $item['live_duration'] = !empty($item['create_time']) ? duration_format(time() - $item['create_time']) : '00:00:00';
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $where[] = ['status', '=', 1];
        $where[] = ['agent_id', '=', AGENT_ID];

        if ($get['room_model'] != '') {
            $where[] = ['room_model', '=', $get['room_model']];
        } else {
            $where[] = ['room_model', '<', 4];
        }
        if ($get['type'] != '') {
            $where[] = ['type', '=', $get['type']];
        }
        if (trim($get['room_id']) != '') {
            $where[] = ['id', '=', trim($get['room_id'])];
        }
        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
        }

        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'title');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

}