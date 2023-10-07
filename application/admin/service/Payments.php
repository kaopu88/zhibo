<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\db;

class Payments extends Service
{
    protected static $unit = ['d'=>'日', 'w'=>'周', 'm'=>'月', 'y'=>'年'];

    public function add($inputData)
    {
        $data = $this->df->process('add@payments', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('payments')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {   
        $data = $this->df->process('update@payments', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('payments')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get)
    {
        $this->db = Db::name('payments');
        $this->setWhere($get)->setOrder();
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = [];
        if ($get['unit'] != '')
        {
            $where['unit'] = $get['unit'];
        }
        if ($get['status'] != '')
        {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder()
    {
        $order = [];
        $order['addtime'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth)
    {
        $this->db = Db::name('payments');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset,$lenth)->select();
        if (!$result) return [];
        foreach ($result as &$item)
        {
            $item['unit_str'] = self::$unit[$item['unit']];
        }
        unset($item);
        return $result;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status,[0,1])) return false;
        return Db::name('payments')->whereIn('id', $ids)->update(['status'=>$status]);
    }
}