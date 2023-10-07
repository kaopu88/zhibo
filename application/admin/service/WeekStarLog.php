<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class WeekStarLog extends Service
{
    
	public function getTotal($get)
    {
		$this->db = Db::name('activity_week_star_log');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
	}

    private function setJoin()
    {
        $this->db->alias('wsl')->join('__USER__ user', 'user.user_id=wsl.user_id', 'LEFT')
            ->join('__USER__ guser', 'guser.user_id=wsl.gold_user_id', 'LEFT')->join('__GIFT__ g', 'g.id=wsl.gift_id', 'LEFT');
        $this->db->field('wsl.*,user.nickname,user.avatar,user.phone,guser.nickname as g_nickname,guser.avatar as g_avatar,g.name,g.picture_url');
        return $this;
    }

	public function setWhere($get){
        $where = array();
        if (!empty($get['gift_id'])) {
            $where['wsl.gift_id'] = $get['gift_id'];
        }
        if (!empty($get['activity_start_time'])) {
            $where['wsl.activity_start_time'] = $get['activity_start_time'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','','g.name');
		$this->db->where($where);
		return $this;
	}

	public function setOrder($get)
    {
		$order = array();
        if ($get['sort'] && $get['sort_by']) {
            $order['wsl.'.$get['sort']] = $get['sort_by'];
        }else{
            $order['wsl.add_time'] = 'desc';
        }
        
		$this->db->order($order);
		return $this;
	}

	public function getList($get, $offset, $lenth)
    {
		$this->db = Db::name('activity_week_star_log');
		$this->setWhere($get)->setOrder($get)->setJoin();
		$result = $this->db->limit($offset, $lenth)->select();
		$result = $result ? $result : [];
		return $result;
	}

	public function addLog($data)
    {
        $where['gift_id'] = $data['gift_id'];
        $where['activity_start_time'] = $data['activity_start_time'];
        $where['user_id'] = $data['user_id'];
        $where['type'] = $data['type'];
        $count = Db::name('activity_week_star_log')->where($where)->count();
        if($count == 0) {
            $id = Db::name('activity_week_star_log')->insertGetId($data);
        }
        if(!$id){
            return false;
        }
        return $id;
    }

}