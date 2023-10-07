<?php

namespace app\agent\service;

use think\Model;

class AgentPriceLog extends Model
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
        return $result ?: [];
    }

    protected function setWhere($get)
    {
        if ($get['agent_id'] != '') {
            $where[] = ['agent_id', '=', $get['agent_id']];
        }
        if (isset($get['type'])) {
            $where[] = ['type', '=', $get['type']];
        }

        if (isset($get['trade_type']))  {
            $where[] = ['trade_type', '=', $get['trade_type']];
        }
        return $where;
    }
}