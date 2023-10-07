<?php

namespace app\lottery\service;

use bxkj_module\service\Service;
use think\Db;

class Lottery extends Service
{
    public function getTotal($get){
        $this->db = Db::name('Lottery');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['aid'])) {
            $where['aid'] = $get['aid'];
        }
        if ($get['audit_status'] != '') {
            $where['audit_status'] = $get['audit_status'];
        }
        if (!empty($get['cid'])) {
            $where['cid'] = $get['cid'];
        }
        if (!empty($get['target_type'])) {
            $where['target_type'] = $get['target_type'];
        }
        if (!empty(trim($get['user_id']))) {
            $where['user_id'] = trim($get['user_id']);
        }
        if (!empty(trim($get['to_uid']))) {
            $where['to_uid'] = trim($get['to_uid']);
        }
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        if (empty($get['sort'])) {
            if ($get['audit_status'] == '0') {
                $order['create_time'] = 'asc';
                $order['id'] = 'asc';
            } else if (empty($get['audit_status'])) {
                $order['create_time'] = 'desc';
                $order['id'] = 'desc';
            } else {
                $order['handle_time'] = 'desc';
                $order['id'] = 'desc';
            }
        }
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('Lottery');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($get,$result);
        return $result;
    }

    public function getLottery($get,$offset,$lenth)
    {
        $this->db = Db::name('Lottery');
        $this->setLotteryWhere($get)->setLotteryOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }


    //获取类型总数
    public function getLotteryTotal($get){
        $this->db = Db::name('Lottery');
        $this->setLotteryWhere($get);
        return $this->db->count();
    }

    public function setLotteryWhere($get){
        $where = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name');
        $this->db->where($where);
        return $this;
    }

    public function setLotteryOrder($get){
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function lottery_add($inputData)
    {
        $data = $this->df->process('add@lottery', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('lottery')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function lottery_edit($inputData)
    {

        $data = $this->df->process('update@lottery', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('lottery')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }
}