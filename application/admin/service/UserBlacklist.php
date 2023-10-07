<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserBlacklist extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('user_blacklist');
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
        if (trim($get['to_uid']) != '')
        {
            $where['to_uid'] = trim($get['to_uid']);
        }
        if ($get['status'] != '')
        {
            $where['status'] = $get['status'];
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
        $this->db = Db::name('user_blacklist');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'to_uid');
        foreach ($result as &$item)
        {
            if (!empty($item['to_uid'])) {
                $item['to_user'] = self::getItemByList($item['to_uid'], $recAccounts_b, 'user_id');
            }
            if (!empty($item['user_id'])) {
                $item['user'] = self::getItemByList($item['user_id'], $recAccounts, 'user_id');
            }
        }
    }
}

