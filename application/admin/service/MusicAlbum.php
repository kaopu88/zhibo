<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class MusicAlbum extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@music_album', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('music_album')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@music_album', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('music_album')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('music_album');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['singer_id'] != '') {
            $where['singer_id'] = $get['singer_id'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number ma.id','ma.title,number ma.id');
        $this->db->setKeywords(trim($get['user_keyword']), '', 'number ms.id', 'ms.name,number ms.id');
        $this->db->where($where);
        return $this;
    }

    private function setJoin()
    {
        $this->db->alias('ma')->join('__MUSIC_SINGER__ ms', 'ms.id=ma.singer_id', 'LEFT');
        $this->db->field('ma.*,ms.name singer_name,ms.avatar');
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['ma.create_time'] = 'desc';
        $order['ma.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('music_album');
        $this->setWhere($get)->setOrder($get)->setJoin();
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('music_album');
        $this->db->setKeywords($keyword, '', '', 'title');
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['id'],
                'name' => $item['title']
            ];
        }
        return $arr;
    }
}

