<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class SmsTemplate extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@sms_template', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('sms_template')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@sms_template', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('sms_template')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('sms_template');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,code,number id');
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
        $this->db = Db::name('sms_template');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }
}
