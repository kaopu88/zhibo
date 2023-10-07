<?php

namespace app\agent\service;

use think\Db;
use think\Exception;
use think\Model;

class AgentCashAccount extends Model
{
    protected $type = ['alipay', 'wxpay', 'bank'];
    protected $type_str = ['alipay'=>'支付宝', 'wxpay'=>'微信', 'bank'=>'银行卡'];
    protected $verify_status_str = ['0'=>'未知', '1'=>'有效', 'bank'=>'无效'];

    public function getTotal($get)
    {
        $where = $this->setWhere($get);
        $count = $this->where($where)->count();
        return (int)$count;
    }

    public function getList($get,$offset,$lenth)
    {
        $where = $this->setWhere($get);
        $result = $this->where($where)->limit($offset, $lenth)->order('id','desc')->select();
        if (empty($result)) return [];
        foreach ($result as $key => &$value) {
            $value['type_str'] = $this->type_str[$value['type']];
            $value['verify_status_str'] = $this->verify_status_str[$value['verify_status']];
        }
        return $result;
    }

    protected function setWhere($get)
    {
        if ($get['agent_id'] != '') {
            $where[] = ['agent_id', '=', $get['agent_id']];
        }
        if (isset($get['type'])) {
            $where[] = ['type', '=', $get['type']];
        }

        if (isset($get['verify_status'])) {
            $where[] = ['verify_status', '=', $get['verify_status']];
        }

        return $where;
    }

    public function add($params)
    {
        try {
            apiAsserts(isset($params['account_type']) && !$this->type[$params['account_type']], '账号类型不存在');
            Db::startTrans();
            if (!empty($params['is_default'])) {
                $this->where(['agent_id' => AGENT_ID, 'is_default' => 1])->update(['is_default' => 0]);
            }
            $data = [
                'agent_id' => AGENT_ID,
                'account_type' => $params['account_type'],
                'type' => $this->type[$params['account_type']],
                'card_name' => $params['card_name'],
                'account' => $params['account'],
                'open_id' => $params['open_id'] ?: '',
                'name' => $params['name'],
                'verify_status' => 1,
                'create_time' => time(),
                'is_default' => $params['is_default'],
            ];
            $id = $this->insert($data);
            apiAsserts(!$id, '新增失败');
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 101, 'msg' =>$e->getMessage()];
        }
        Db::commit();
        return ['code' => 200];
    }

    public function edit($params, $id)
    {
        try {
            Db::startTrans();
            if (!empty($params['is_default'])) {
                $this->where(['agent_id' => AGENT_ID, 'is_default' => 1])->update(['is_default' => 0]);
            }
            unset($params['id']);
            apiAsserts(isset($params['account_type']) && !$this->type[$params['account_type']], '账号类型不存在');
            $id = $this->where(['agent_id' => AGENT_ID, 'id' => $id])->update($params);
            apiAsserts(!$id, '更新失败');
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 101, 'msg' =>$e->getMessage()];
        }
        Db::commit();
        return ['code' => 200];
    }

    public function delete($ids = array())
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $num = $this->whereIn('id', $ids)->delete();
        return $num;
    }
}