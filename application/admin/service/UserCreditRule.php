<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class UserCreditRule extends Service
{
    protected static $change_type = ['inc'=>'增加', 'exp'=>'减少'];

    public function add($inputData)
    {
        $data = $this->df->process('add@user_credit_rule', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('user_credit_rule')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@user_credit_rule', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('user_credit_rule')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getTotal($get){
        $this->db = Db::name('user_credit_rule');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if ($get['change_type'] != '') {
            $where['change_type'] = $get['change_type'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name,number id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder(){
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('user_credit_rule');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        if (!empty($result)){
            foreach ($result as &$item){
                $item['change_type_str'] = self::$change_type[$item['change_type']];
            }
            unset($item);
        }
        return $result;
    }
}

