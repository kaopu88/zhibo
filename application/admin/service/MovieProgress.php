<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class MovieProgress extends Service
{
    protected $db;

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('movie_progress');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('movie_progress');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array('mid' => $get['mid']);
        $this->db->where($where);
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'title');
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['release_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@movie_progress', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('movie_progress')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@movie_progress', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('movie_progress')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function fillSummary($value, $rule, $data = null, $more = null)
    {
        $content = strip_tags($data['content']);
        return msubstr($content, 0, 45, 'utf-8', false);
    }


}