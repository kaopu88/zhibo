<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Impression extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@impression', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        if (!preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/i', $data['color'], $match)) return $this->setError('颜色格式错误');
        $id = Db::name('impression')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@impression', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        if (!preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/i', $data['color'], $match)) return $this->setError('颜色格式错误');
        $num = Db::name('impression')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('impression');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['type'] != '') {
            $where['type'] = $get['type'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('impression');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('impression')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }
}
