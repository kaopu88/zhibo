<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class LivePk extends Service
{
    protected static $status = ['进行中','已完成'];
    protected static $pk_type = ['rand'=>'全民PK','friend'=>'好友PK','pk_rank'=>'PK排位赛'];
    protected static $pk_res = ['平局','我方胜','-1' => '对方胜'];
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_pk');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_pk');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (!$result) return [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'active_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'target_id');
        foreach ($result as &$item) {
            if (!empty($item['target_id'])) {
                $item['to_user'] = self::getItemByList($item['target_id'], $recAccounts_b, $relKey);
            }
            if (!empty($item['active_id'])) {
                $item[$outKey] = self::getItemByList($item['active_id'], $recAccounts, $relKey);
            }
            $item['status_txt'] = self::$status[$item['status']];
            $item['pk_type_txt'] = self::$pk_type[$item['pk_type']];
            $item['pk_res_txt'] = self::$pk_res[$item['pk_res']];
        }
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if ($get['pk_type'] != '')
        {
            $where['pk_type'] = $get['pk_type'];
        }
        if ($get['pk_res'] != '')
        {
            $where['pk_res'] = $get['pk_res'];
        }
        if ($get['status'] != '')
        {
            $where['status'] = $get['status'];
        }
        if (trim($get['active_id']) != '')
        {
            $where['active_id'] = trim($get['active_id']);
        }
        if (trim($get['target_id']) != '')
        {
            $where['target_id'] = trim($get['target_id']);
        }

        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['id'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }
}

