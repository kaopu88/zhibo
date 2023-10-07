<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class CreditLog extends Service
{
    
	public function getTotal($get)
    {
		$this->db = Db::name('user_credit_log');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
	}

    private function setJoin()
    {
        $this->db->alias('uc')->join('__USER__ user', 'user.user_id=uc.user_id', 'LEFT');
        $this->db->alias('uc')->join('__USER_CREDIT_RULE__ ucr', 'ucr.type=uc.type', 'LEFT');
        $this->db->field('uc.*,ucr.name');
        return $this;
    }

	public function setWhere($get){
        $where = array();
        if (!empty($get['user_id'])) {
            $where[] = ['uc.user_id', '=', $get['user_id']];
        }   
        if (!empty($get['type'])) {
            $where[] = ['uc.type', '=', $get['type']];
        }   
        if (!empty($get['change_type'])) {
            $where[] = ['uc.change_type', '=', $get['change_type']];
        }    
        $this->db->setKeywords(trim($get['keyword']),'','number uc.id','uc.subject,number uc.id');
		$this->db->where($where);
		return $this;
	}

	public function setOrder($get)
    {
		$order = array();

        if ($get['sort'] && $get['sort_by']) {
            $order['uc.'.$get['sort']] = $get['sort_by'];
        }else{
            $order['uc.create_time'] = 'desc';
        }
        
		$this->db->order($order);
		return $this;
	}

	public function getList($get,$offset,$lenth)
    {
		$this->db = Db::name('user_credit_log');
		$this->setWhere($get)->setOrder($get)->setJoin();
		$result = $this->db->limit($offset,$lenth)->select();
		$result = $result ? $result : [];
        $this->parseList($get,$result);
		return $result;
	}

    public function parseList($get,&$result)
    {
        $auditAdmins = [];
        if (empty($get['aid'])) {
            $auditAids = self::getIdsByList($result, 'aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }
        foreach ($result as &$item) {
            if (empty($get['aid']) && !empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $auditAdmins);
            }
        }
    }
}