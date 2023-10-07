<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Viewback extends Service
{
	public function getTotal($get){
		$this->db = Db::name('viewback');
        $this->setWhere($get);
        return $this->db->count();
	}

	public function setWhere($get){
        $where = array();
        if (!empty($get['aid'])) {
            $where['aid'] = $get['aid'];
        }
        if ($get['audit_status'] != '') {
            $where['audit_status'] = $get['audit_status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number user_id','');
		$this->db->where($where);
		return $this;
	}

	public function setOrder($get){
		$order = array();
		if (empty($get['sort'])) {
			if ($get['audit_status'] == '0') {
                $order['create_time'] = 'asc';
                $order['id'] = 'asc';
            } else if (empty($get['audit_status'])) {
                $order['create_time'] = 'desc';
                $order['id'] = 'desc';
            } else {
                $order['handle_time'] = 'desc';
                $order['id'] = 'desc';
            }
		}
		$this->db->order($order);
		return $this;
	}

	public function getList($get,$offset,$lenth){
		$this->db = Db::name('viewback');
		$this->setWhere($get)->setOrder($get);
		$result = $this->db->limit($offset,$lenth)->select();
		$result = $result ? $result : [];
		$this->parseList($get,$result);
		return $result;
	}

	public function parseList($get,&$result){
		$relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');

        $auditAdmins = [];
        if (empty($get['aid'])) {
            $auditAids = self::getIdsByList($result, 'aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if (empty($get['aid']) && !empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $auditAdmins);
            }
        }
	}

    public function handler($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('viewback')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update = ['handle_time' => time(), 'audit_status' => $status];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('viewback')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        Service::commit();
        return array_merge($order, $update);
    }
}