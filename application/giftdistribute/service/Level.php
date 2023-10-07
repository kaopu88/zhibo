<?php
namespace app\giftdistribute\service;

use app\giftdistribute\validate\GiftCommissionLevel;
use bxkj_module\service\Service;
use think\Db;

class Level extends Service
{

    public function add($inputData)
    {
        $validate = new GiftCommissionLevel();
        if (!$validate->check($inputData)) {
            return $this->setError($validate->getError());
        }
        if (empty($inputData['fenxiao_reward_num']) &&
            empty($inputData['fenxiao_reward_money']) &&
            empty($inputData['one_fenxiao_reward_num']) &&
            empty($inputData['one_fenxiao_reward_money']) &&
            empty($inputData['child_num'])) {
            return $this->setError('必选选择一种条件');
        }
        $id = Db::name('gift_commission_level')->insertGetId($inputData);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }


    public function edit($inputData)
    {
        $num = Db::name('gift_commission_level')->where(array('id' => $inputData['id']))->update($inputData);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }


    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('gift_commission_level');
        $this->setWhere($get);
        return $this->db->count();
    }


    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('gift_commission_level');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }



    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $this->db->setKeywords(trim($get['keyword']), '', 'number user_id', 'push_object,user_id');
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