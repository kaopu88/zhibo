<?php

namespace app\agent\service;

use bxkj_module\service\Service;
use think\Db;
use think\Model;

class AgentSettlement extends Model
{
    public function getTotal($get)
    {
        $where = $this->setWhere($get);
        $count = $this->where($where)->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $where = $this->setWhere($get);
        $result = $this->where($where)->order('id', 'desc')->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        if ($get['agent_id'] != '') {
            $where[] = ['agent_id', '=', $get['agent_id']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['audit_status', '=', $get['audit_status']];
        }

        return $where;
    }
}