<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserProps extends Service
{
    protected static $status = ['失效','有效','已使用'];
    protected static $use_status = ['未使用','使用中'];

    public function getTotal($get)
    {
        $this->db = Db::name('user_props');
        $this->setWhere($get)->setOrder();
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = [];
        if ($get['props_id'] != '')
        {
            $where['props_id'] = $get['props_id'];
        }
        if ($get['user_id'] != '')
        {
            $where['user_id'] = $get['user_id'];
        }
        if ($get['use_status'] != '')
        {
            $where['use_status'] = $get['use_status'];
        }
        if ($get['status'] != '')
        {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
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

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('user_props');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset, $lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            $item['status_str'] = self::$status[$item['status']];
            $item['use_status_str'] = self::$use_status[$item['use_status']];
        }
    }
}