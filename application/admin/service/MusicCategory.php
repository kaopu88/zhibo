<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class MusicCategory extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@music_category', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('music_category')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@music_category', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('music_category')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('music_category');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['is_recommend'] != '') {
            $where['is_recommend'] = $get['is_recommend'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
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
        $this->db = Db::name('music_category');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }

    public function changeStatus($ids, $is_recommend)
    {
        if (!in_array($is_recommend, ['0', '1'])) return false;
        $num = Db::name('music_category')->whereIn('id', $ids)->update(['is_recommend' => $is_recommend]);
        return $num;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('music_category');
        $this->db->setKeywords($keyword, '', '', 'name');
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['id'],
                'name' => $item['name']
            ];
        }
        return $arr;
    }
}
