<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class DengLevel extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@anchor_exp_level', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $check = $this->check_name($data['levelname'],'');
        if ($check) return $this->setError($check);
        $id = Db::name('deng_level')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@anchor_exp_level', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $check = $this->check_name($data['levelname'],$data['levelid']);
        if ($check) return $this->setError($check);
        $num = Db::name('deng_level')->where(array('levelid' => $data['levelid']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function check_name($name,$levelid)
    {
        if (!$name) {
            return '请填写标题';
        }
        if (!preg_match('#^LV#i', $name, $m)){
            return '标题格式不正确';
        }
        $find = Db::name('deng_level')->where('levelname',$name)->find();
        if (($find && !$levelid) || ($levelid && $find['levelid'] != $levelid)) {
            return '标题已存在';
        }
        return '';
    }

    public function getTotal($get){
        $this->db = Db::name('deng_level');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        $this->db->setKeywords(trim($get['keyword']),'','number levelid','levelname,name,number levelid');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['levelid'] = 'desc';
        $order['addtime'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('deng_level');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }

    public function getAll()
    {
        $this->db = Db::name('deng_level');
        $result = $this->db->select();
        $result = $result ? $result : [];
        return $result;
    }
}

