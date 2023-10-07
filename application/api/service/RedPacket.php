<?php
namespace app\api\service;
use app\common\service\Service;
use think\Db;
class RedPacket extends Service
{

    public function addRedPacket($data)
    {
        $insertid = Db::name('activity_red_packet')->insertGetId($data);
        return $insertid;
    }

    public function getRedPacket($redid)
    {
        $red_packet = Db::name('activity_red_packet')->where(['id'=>$redid])->find();
        return $red_packet;
    }


    public function getTotal($get)
    {
        $this->db = Db::name('activity_red_packet');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $redList = [];
        $this->db = Db::name('activity_red_packet');
        $this->setWhere($get)->setOrder($get);
        $redList = $this->db->limit($offset, $length)->select();
        return $redList;
    }

    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        if (isset($get['room_id']) && $get['room_id'] != '') {
            $where['room_id'] = $get['room_id'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'id DESC';
        }
        $this->db->order($order);
        return $this;
    }


}