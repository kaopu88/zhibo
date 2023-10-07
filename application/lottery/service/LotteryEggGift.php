<?php

namespace app\lottery\service;

use bxkj_module\service\Service;
use think\Db;

class LotteryEggGift extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('lottery_egg_gift');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    public function getOne($get = '')
    {
        $this->db = Db::name('lottery_egg_gift');
        $this->setJoin();
        $result = $this->db->where($get)->find();
        if (empty($result)) return false;
        return $result;
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('lottery_egg_gift');
        $this->setWhere($get)->setJoin()->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();

        if (empty($result)) return [];
        return $result;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@lottery_egg_gift', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('lottery_egg_gift')->insert($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function edit($inputData)
    {
        $data = $this->df->process('update@lottery_egg_gift', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('lottery_egg_gift')->where(array('id' => $inputData['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $this->db->setKeywords(trim($get['keyword']), '', 'number leg.id', 'number leg.id');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['leg.id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    private function setJoin()
    {
        $this->db->alias('leg')->join('__GIFT__ gift', 'leg.gift_id=gift.id', 'LEFT');
        $this->db->field('leg.*, gift.picture_url as image, gift.name');
        return $this;
    }
}