<?php

namespace app\admin\service;

use bxkj_module\service\Tree;
use think\Db;

class FilmTags extends Tree
{
    public function __construct()
    {
        parent::__construct('video_tags', 'pid', 'id');
    }

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name($this->tabName);
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name($this->tabName);
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['child_num'] = Db::name($this->tabName)->where(array('pid' => $item['id']))->count();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['pid'] != "") {
            $where[] = ['pid', '=', $get['pid']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['sort'] = 'desc';
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        } else if ($get['sort'] == 'film_num' && $get['sort_by']) {
            $order['film_num'] = $get['sort_by'];
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@video_tags', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('video_tags')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@video_tags', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('video_tags')->where('id', $data['id'])->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }


    public function getInfo($id)
    {
        $info = Db::name('video_tags')->where(['id' => $id])->find();
        if ($info) {
        }
        return $info;
    }

}