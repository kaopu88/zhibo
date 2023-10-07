<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Follow extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('follow');
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
        if (trim($get['follow_id']) != '')
        {
            $where['follow_id'] = trim($get['follow_id']);
        }
        if ($get['type'] != '')
        {
            $where['type'] = $get['type'];
        }
        if ($get['ismutual'] != '')
        {
            $where['ismutual'] = $get['ismutual'];
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
        $this->db = Db::name('follow');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'follow_id');
        foreach ($result as &$item)
        {
            if (!empty($item['follow_id'])) {
                $item['to_user'] = self::getItemByList($item['follow_id'], $recAccounts_b, 'user_id');
            }
            if (!empty($item['user_id'])) {
                $item['user'] = self::getItemByList($item['user_id'], $recAccounts, 'user_id');
            }
        }
    }
}
