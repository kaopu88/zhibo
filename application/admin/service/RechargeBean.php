<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class RechargeBean extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('recharge_bean');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('recharge_bean');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['name'] = APP_BEAN_NAME;
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '')
        {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@recharge_bean', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('recharge_bean')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@recharge_bean', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('recharge_bean')->where('id', $data['id'])->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getInfo($id)
    {
        $info = Db::name('recharge_bean')->where(['id' => $id])->find();
        if ($info) {
        }
        return $info;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择套餐');
        $num = Db::name('recharge_bean')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除失败');
        return $num;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('recharge_bean')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }


}