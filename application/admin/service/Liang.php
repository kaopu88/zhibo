<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Liang extends Service
{
    public function add($inputData)
    {   
       
        $data = $this->df->process('add@liang', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $data['length'] = strlen($data['name']);
        $id = Db::name('liang')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@liang', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('liang')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('liang');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['addtime'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('liang');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        if (empty($result)) return [];
        return $result;
    }

    public function changeStatus($ids, $status)
    {   
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('liang')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }
}

