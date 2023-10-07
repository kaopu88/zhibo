<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class WeekStarGift extends Service
{
    
	public function getTotal($get)
    {
		$this->db = Db::name('week_star_gift');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
	}

    private function setJoin()
    {
        $this->db->alias('wsg')->join('__GIFT__ g', 'g.id=wsg.gift_id', 'LEFT');
        $this->db->field('wsg.*,g.name,g.picture_url');
        return $this;
    }

	public function setWhere($get){
        $where = array();
        if (!empty($get['gift_id'])) {
            $where['wsg.gift_id'] = $get['gift_id'];
        }
        if (!empty($get['start_time'])) {
            $where['wsg.start_time'] = $get['start_time'];
        }
        if (!empty($get['end_time'])) {
            $where['wsg.end_time'] = $get['end_time'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','','g.name');
		$this->db->where($where);
		return $this;
	}

	public function setOrder($get)
    {
		$order = array();
        if ($get['sort'] && $get['sort_by']) {
            $order['wsg.'.$get['sort']] = $get['sort_by'];
        }else{
            $order['wsg.add_time'] = 'desc';
        }
        
		$this->db->order($order);
		return $this;
	}

	public function getList($get, $offset, $lenth)
    {
		$this->db = Db::name('week_star_gift');
		$this->setWhere($get)->setOrder($get)->setJoin();
		$result = $this->db->limit($offset, $lenth)->select();
		$result = $result ? $result : [];
		return $result;
	}

	public function add($data)
    {
        $count = Db::name('week_star_gift')->where(["gift_id"=>$data['gift_id'],"start_time"=>$data['start_time']])->count();
        if($count == 0) {
            $data['add_time'] = time();
            $id = Db::name('week_star_gift')->insertGetId($data);
        }
        if(!$id){
            return false;
        }
        return $id;
    }

    public function getInfo($where)
    {
        $info = Db::name('week_star_gift')->where($where)->find();
        return $info;
    }

    public function updateInfo($where, $data)
    {
        $status = Db::name('week_star_gift')->where($where)->update($data);
        return $status;
    }


}