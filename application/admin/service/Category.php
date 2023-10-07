<?php

namespace app\admin\service;

use bxkj_module\service\Tree;
use think\Db;

class Category extends Tree
{
    protected $db;

    public function __construct()
    {
        parent::__construct('category', 'pid', 'id');
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@category', $inputData)->output();
        if (!$data) return $this->setError($this->df->getError());
        $id = Db::name('category')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        $this->clearCache($id);
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@category', $inputData)->output();
        if (!$data) return $this->setError($this->df->getError());
        $num = Db::name('category')->update($data);
        if (!$num) return $this->setError('更新失败');
        $this->clearCache($data['id']);
        return $num;
    }

    public function validatePid($value, $rule, $data = null, $more = null)
    {
        if ($value == '0') return true;
        return Db::name($this->tabName)->where(array('id' => $value))->count() > 0;
    }

    public function validatePid2($value, $rule, $data = null, $more = null)
    {
        return $data['id'] != $value;
    }

    public function validateMark($value, $rule, $data = null, $more = null)
    {
        $pid = $data['pid'];
        if (!isset($pid)) {
            $result = Db::name($this->tabName)->where(array('id' => $data['id']))->field('pid')->find();
            $pid = $result['pid'];
        }
        $where = array(['pid', '=', $pid], ['mark', '=', $value]);
        if (isset($data['id'])) $where[] = ['id', '<>', $data['id']];
        $num = Db::name($this->tabName)->where($where)->count();
        return $num <= 0;
    }

    public function autoLevel($value, $rule, $data = null, $more = null)
    {
        if ($value == '0') return array('level' => 0);
        $result = Db::name($this->tabName)->where(array('id' => $value))->field('level')->find();
        return array('level' => ((int)$result['level'] + 1));
    }

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name($this->tabName);
        $this->setWhere($get);
        $num = $this->db->count();
        return $num;
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name($this->tabName);
        $this->db->limit($offset, $length);
        $this->setWhere($get);
        $this->setOrder($get);
        $result = $this->db->select();
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['child_num'] = Db::name($this->tabName)->where(array('pid' => $result[$i]['id']))->count();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['pid'] != "") {
            $where[] = ['pid', '=', $get['pid']];
        }
        if ($get['status'] != "") {
            $where[] = ['status', '=', $get['status']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');
        $this->db->where($where);
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['sort'] = 'desc';
            $order['create_time'] = 'asc';
        }
        return $this->db->order($order);
    }

}