<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Robot extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@robot', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('robot')->insertGetId($data);
        return true;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@robot', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('robot')->where(array('user_id' => $data['user_id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('robot');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        $this->db->setKeywords(trim($get['keyword']),'','number user_id','username,nickname,number user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['create_time'] = 'desc';
        $order['user_id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('robot');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $regionIds = self::getIdsByList($result, 'province_id,city_id,district_id');
        $regionList = Db::name('region')->whereIn('id', $regionIds)->field('id,name')->select();
        if ($result)
        {
            foreach ($result as &$value) {
                if (!empty($value['province_id'])) {
                    $value['province_name'] = self::getItemByList($value['province_id'], $regionList, 'id', 'name');
                }
                if (!empty($value['city_id'])) {
                    $value['city_name'] = self::getItemByList($value['city_id'], $regionList, 'id', 'name');
                }
                if (!empty($value['district_id'])) {
                    $value['district_name'] = self::getItemByList($value['district_id'], $regionList, 'id', 'name');
                }
            }
        }
        return $result;
    }
}

