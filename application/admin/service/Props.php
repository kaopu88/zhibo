<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Props extends Service
{
    protected static $type = ['大礼物', '小礼物'];

    public function add($inputData)
    {
        $data = $this->df->process('add@props', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('props')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@props', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('props')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('props');
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
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('props');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        if (empty($result)) return [];
        foreach ($result as &$value)
        {
            $value['type_str'] = self::$type[$value['type']];
        }
        return $result;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('props')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }
}

