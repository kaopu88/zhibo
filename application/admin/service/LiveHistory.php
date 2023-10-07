<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class LiveHistory extends Service
{
    protected static $room_model = ['直播','录像','电影','游戏'];
    protected static $type = ['普通','私密','收费','计费','VIP','等级'];
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
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        list($channels) = self::getIdsByList($result, 'room_channel', true);
        $channelService = new LiveChannel();
        $channelList = $channelService->getChannelsByIds($channels);
        foreach ($result as &$item) {
            $item['room_model_txt'] = self::$room_model[$item['room_model']];
            $item['type_txt'] = self::$type[$item['type']];
            $item['channel_info'] = self::getItemByList($item['room_channel'], $channelList, 'id');
        }
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if (trim($get['room_id']) != '')
        {
            $where['room_id'] = trim($get['room_id']);
        }
        if ($get['room_model'] != '')
        {
            $where['room_model'] = $get['room_model'];
        }
        if ($get['type'] != '')
        {
            $where['type'] = $get['type'];
        }

        $this->db->setKeywords(trim($get['keyword']),'','number user_id','nickname,number user_id');
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


