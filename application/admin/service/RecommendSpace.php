<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class RecommendSpace extends Service
{
    protected $db;

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('recommend_space');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('recommend_space');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['count'] = Db::name('recommend_content')->where([
                ['rec_id', '=', $item['id']]
            ])->count();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
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
            $order['create_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@recommend_space', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('recommend_space')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@recommend_space', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('recommend_space')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    //获取广告位选项
    public function getSpaces($status = '1', $length = 100)
    {
        $where = [];
        if (isset($status)) $where[] = ['status', 'eq', $status];
        $spaces = Db::name('recommend_space')->where($where)->field('id,mark,name,img_config,platform')->limit($length)->select();
        foreach ($spaces as &$space) {
        }
        return $spaces;
    }

    public function delete($ids)
    {
        $num = Db::name('recommend_space')->whereIn('id', $ids)->delete();
        if ($num) {
            Db::name('recommend_content')->whereIn('rec_id', $ids)->delete();
        }
        return $num;
    }


}