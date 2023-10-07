<?php
namespace app\agent\service;

use bxkj_module\service\Auth;
use think\Db;

class Agent extends \bxkj_module\service\Agent
{

    public function getTotal($get)
    {
        $this->db = Db::name('agent');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('agent');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $regionIds = self::getIdsByList($result, 'province_id,city_id');
        $regionList = Db::name('region')->whereIn('id', $regionIds)->field('id,name')->select();
        foreach ($result as &$value) {
            if (!empty($value['province_id'])) {
                $value['province_name'] = self::getItemByList($value['province_id'], $regionList, 'id', 'name');
            }
            if (!empty($value['city_id'])) {
                $value['city_name'] = self::getItemByList($value['city_id'], $regionList, 'id', 'name');
            }
            $this->parseExpire($value);
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        if ($get['query']=='child')
        {
            $where = [['pid', '=', AGENT_ID]];
        }else{
            $agentIds = self::getAgentIds(AGENT_ID);
        }

        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['level'] != '') {
            $where[] = ['level', '=', $get['level']];
        }
        if ($get['grade'] != '') {
            $where[] = ['grade', '=', $get['grade']];
        }
        if ($get['district'] != '') {
            $where[] = ['district_id', '=', $get['district']];
        } else if ($get['city'] != '') {
            $where[] = ['city_id', '=', $get['city']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone contact_phone', 'number id', 'number contact_phone,name');
        $this->db->where($where);

        if ($get['query']!='child')
        {
            $this->db->whereIn('id', $agentIds);
        }

        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add2@agent', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $agent = Db::name('agent')->where('id', AGENT_ID)->find();
        $data['add_promoter'] = isset($data['add_promoter']) ? $data['add_promoter'] : $agent['add_promoter'];
        $data['add_anchor'] = isset($data['add_anchor']) ? $data['add_anchor'] : $agent['add_anchor'];
        $data['max_promoter_num'] = isset($data['max_promoter_num']) ? $data['max_promoter_num'] : $agent['max_promoter_num'];
        $data['max_anchor_num'] = isset($data['max_anchor_num']) ? $data['max_anchor_num'] : $agent['max_anchor_num'];
        $data['add_sec'] = '0';
        $data['max_sec_num'] = 0;
        if (!$this->checkScope($data, $agent)) return false;
        $data['root_id'] = 0;
        $data['level'] = 1;
        $data['pid'] = AGENT_ID;
        $id = Db::name('agent')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        return $id;
    }

    //检查权限范围
    protected function checkScope($data, $agent = null)
    {
        if (!isset($agent)) {
            $agent = Db::name('agent')->where('id', AGENT_ID)->find();
        }
        if ($agent['add_promoter'] == '0' && $data['add_promoter'] == '1') {
            return $this->setError('您自身没有允许新增'.config('app.agent_setting.promoter_name').'的权限');
        }
        if ($agent['add_anchor'] == '0' && $data['add_anchor'] == '1') {
            return $this->setError('您自身没有允许新增主播的权限');
        }
        if ($data['max_promoter_num'] > $agent['max_promoter_num']) {
            return $this->setError(config('app.agent_setting.promoter_name').'限额超出自身最大限额');
        }
        if ($data['max_anchor_num'] > $agent['max_anchor_num']) {
            return $this->setError('主播限额超出自身最大限额');
        }
        if ($data['expire_time'] > $agent['expire_time']) {
            return $this->setError('到期时间超出自身到期时间');
        }
        return true;
    }

    public function update($inputData)
    {
        unset($inputData['pid'], $inputData['logo'], $inputData['remark']);
        $data = $this->df->process('update2@agent', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        unset($data['add_sec'], $data['max_sec_num']);
        $agent = Db::name('agent')->where('id', AGENT_ID)->find();
        if (!$this->checkScope($data, $agent)) return false;
        $where = array('id' => $data['id'], 'pid' => AGENT_ID);
        $num = Db::name('agent')->where($where)->update($data);
        if (!$num) return $this->setError('编辑失败');
        return $num;
    }

    public function checkLogin($agentId)
    {
        $agent = Db::name('agent')->where(['id' => $agentId])->find();
        if (empty($agent)) return $this->setError(config('app.agent_setting.agent_name').'不存在');
        if ($agent['status'] != '1') return $this->setError(config('app.agent_setting.agent_name').'已禁用');
        if (time() >= $agent['expire_time']) return $this->setError(config('app.agent_setting.agent_name').'已过期');
        return ['level' => 0, 'agent_id' => $agent['id'], 'agent' => $agent];
    }

    public function addAgent($data){
        $id = Db::name('agent')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        return $id;
    }

    public function find($where){
        return Db::name('agent')->where($where)->find();
    }

    public function updataAgetn($data){
        $where = array('id' => $data['id']);
        $num = Db::name('agent')->where($where)->update($data);
        if (!$num) return $this->setError('编辑失败');
        return $num;
    }
}