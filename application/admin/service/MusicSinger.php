<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class MusicSinger extends Service
{
    public function add($inputData)
    {
        $data = $this->df->process('add@music_singer', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('music_singer')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@music_singer', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('music_singer')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('music_singer');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['gender'] != '') {
            $where['gender'] = $get['gender'];
        }
        if ($get['classify'] != '') {
            $where['classify'] = $get['classify'];
        }
        if ($get['languages'] != '') {
            $where['languages'] = $get['languages'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        if ($get['sort'] == 'songs_total') {
            $order['songs_total'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['create_time'] = 'desc';
        } else if ($get['sort'] == 'mv_total') {
            $order['mv_total'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['create_time'] = 'desc';
        } else if ($get['sort'] == 'albums_total') {
            $order['albums_total'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['create_time'] = 'desc';
        }  else if ($get['sort'] == 'time') {
            $order['create_time'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
        } else {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }

        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('music_singer');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('music_singer');
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

