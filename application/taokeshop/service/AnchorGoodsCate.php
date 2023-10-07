<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/24
 * Time: 16:23
 */
namespace app\taokeshop\service;

use bxkj_module\service\Service;
use think\Db;

class AnchorGoodsCate extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('anchor_goods_cate');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $result = [];
        $this->db = Db::name('anchor_goods_cate');
        $this->setWhere($get)->setOrder($get);
        $fields = 'user.user_id,user.nickname';
        $this->db->field('agc.*');
        $result = $this->db->field($fields)->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $this->db->alias('agc');
        $where = array();
        if ($get['cate_id'] != 0) {
            $where['agc.cate_id'] = $get['cate_id'];
        }
        if ($get['user_id'] != 0) {
            $where['agc.user_id'] = $get['user_id'];
        }
        if ($get['status'] != '') {
            $where['agc.status'] = $get['status'];
        }
        $this->db->where($where);
        $this->db->setKeywords(trim($get['keyword']), 'nickname user.nickname', 'name agc.cate_name');
        $this->db->join('__USER__ user', 'user.user_id=agc.user_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['agc.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function getCateInfo($get)
    {
        $this->db = Db::name('anchor_goods_cate');
        $this->setWhere($get)->setOrder($get);
        $fields = 'user.user_id,user.nickname';
        $this->db->field('agc.*');
        $result = $this->db->field($fields)->find();
        return $result;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@anchor_goods_cate', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('anchor_goods_cate')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@anchor_goods_cate', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('anchor_goods_cate')->where(array('cate_id' => $data['cate_id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }
}