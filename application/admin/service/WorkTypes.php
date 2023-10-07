<?php


namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class WorkTypes extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('work_types');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('work_types');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');
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
        $data = $this->df->process('add@work_types', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('work_types')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@work_types', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('work_types')->where('id', $data['id'])->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }


    public function getInfo($id)
    {
        $info = Db::name('work_types')->where(['id' => $id])->find();
        if ($info) {
        }
        return $info;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择任务类型');
        $num = Db::name('work_types')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除失败');
        return $num;
    }
}