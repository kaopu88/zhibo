<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Movie extends Service
{
    protected $db;

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('movie');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('movie');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['progress_num']=Db::name('movie_progress')->where(['mid'=>$item['id']])->count();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['mv_status'] != '') {
            $where[] = ['mv_status', '=', $get['mv_status']];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','title');
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
        $data = $this->df->process('add@movie', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('movie')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@movie', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('movie')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function raise($inputData)
    {
        if (empty($inputData['id'])) return $this->setError('请选择电影');
        if (!validate_regex($inputData['total'], 'integer') || $inputData['total'] <= 0) {
            return $this->setError('总份额不正确');
        }
        if (!validate_regex($inputData['price'], 'currency') || $inputData['price'] <= 0) {
            return $this->setError('每份单价不正确');
        }
        if ($inputData['start_num'] < 0) return $this->setError('起步份数不正确');
        if ($inputData['v_sales'] != -1 && $inputData['v_sales'] > $inputData['total']) {
            return $this->setError('虚拟销售量不能超过总份额');
        }
        $data['rec_status'] = $inputData['rec_status'] ? $inputData['rec_status'] : '0';
        $data['total'] = (int)$inputData['total'];
        $data['price'] = $inputData['price'];
        $data['start_num'] = (int)$inputData['start_num'];
        $data['v_sales'] = isset($inputData['v_sales']) ? $inputData['v_sales'] : '-1';
        if ($inputData['deadline'] != '') {
            $data['deadline'] = strtotime($inputData['deadline']);
        }
        $data['welfare'] = $inputData['welfare'] ? nl2br($inputData['welfare']) : '';
        $num = Db::name('movie')->where(['id' => $inputData['id']])->update($data);
        if(!$num) return $this->setError('设置失败');
        return $num;
    }

    public function fillLength($value, $rule, $data = null, $more = null)
    {
        if (empty($value)) return 0;
        return $value * 60;
    }

}