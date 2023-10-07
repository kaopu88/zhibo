<?php

namespace app\lottery\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class LotteryGift extends Service
{


    //获取总记录数
    public function getTotal($get)
    {

        $this->db = Db::name('lottery_gift');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('lottery_gift');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }


    public function add($inputData)
    {
        $id = Db::name('lottery_gift')->insertGetId($inputData);
        if (!$id) return $this->setError('新增失败');
        $this->delrds();
        return $id;
    }


    public function edit($inputData)
    {
        $num = Db::name('lottery_gift')->where(array('id' => $inputData['id']))->update($inputData);
        if (!$num) return $this->setError('更新失败');
        $this->delrds();
        return $num;
    }


    private function delrds()
    {
        $redis = RedisClient::getInstance();
        $redis->del('cache:LotteryDetail');
    }


    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['id'] != '') {
            $where[] = ['activity_id', '=', $get['id']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'user_name,user_id');

        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }
}