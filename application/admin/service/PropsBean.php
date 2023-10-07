<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class PropsBean extends Service
{
    protected static $unit = ['d'=>'日', 'w'=>'周', 'm'=>'月', 'y'=>'年'];

    public function add($inputData)
    {
        $data = $this->df->process('add@props_bean', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('props_bean')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@props_bean', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('props_bean')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('props_bean');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['props_id'] != '') {
            $where['props_id'] = $get['props_id'];
        }
        if ($get['unit'] != '') {
            $where['unit'] = $get['unit'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('props_bean');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        if (empty($result)) return [];
        foreach ($result as &$value)
        {
            $db = Db::name('props')->where('id', $value['props_id']);
            $value['unit_str'] = self::$unit[$value['unit']];
            $value['name'] = $db->value('name');
            $value['cover_icon'] = $db->value('cover_icon');
        }
        return $result;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('props_bean')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }
}


