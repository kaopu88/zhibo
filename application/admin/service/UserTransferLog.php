<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserTransferLog extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('user_transfer_log');
        $this->setWhere($get)->setOrder();
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = [];
        if (trim($get['user_id']) != '')
        {
            $where['user_id'] = trim($get['user_id']);
        }
        if ($get['transfer_type'] != '')
        {
            $where['transfer_type'] = $get['transfer_type'];
        }
        $this->db->where($where);
        return $this;
    }

    public function setOrder()
    {
        $order = [];
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth)
    {
        $this->db = Db::name('user_transfer_log');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $auditAdmins = [];
        if (empty($get['aid'])) {
            $auditAids = self::getIdsByList($result, 'aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }
        foreach ($result as &$item)
        {
            if (!empty($item['user_id'])) {
                $item['user'] = self::getItemByList($item['user_id'], $recAccounts, 'user_id');
            }
            if (empty($get['aid']) && !empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $auditAdmins);
            }
        }
    }
}