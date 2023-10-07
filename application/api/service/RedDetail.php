<?php
namespace app\api\service;
use app\common\service\Service;
use think\Db;
class RedDetail extends Service
{

    public function getRedPacket($room_id){
        $redList=Db::name('activity_red_detail')->field('red_id,sum(money) as total_money')->where(['room_id'=>$room_id])->group('red_id')->select();
        return $redList;
    }

    public function addRedPacket($data)
    {
        $insertid = Db::name('activity_red_detail')->insertGetId($data);
        return $insertid;
    }

    public function getTotal($get)
    {
        $this->db = Db::name('activity_red_detail');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $redList = [];
        $this->db = Db::name('activity_red_detail');
        $this->setWhere($get)->setOrder($get);
        $redList = $this->db->limit($offset, $length)->select();
        return $redList;
    }

    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        $where['room_id'] = $get['room_id'];
        $where['red_id'] = $get['red_id'];
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