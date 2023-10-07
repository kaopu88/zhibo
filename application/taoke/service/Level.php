<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/6
 * Time: 17:15
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Level extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('taoke_level');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $this->db = Db::name('taoke_level');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if($result){
            foreach ($result as $key => $value){
                $result[$key]['promotion'] = json_decode($value['promotion'], true);
                $result[$key]['upgrade_condition'] = json_decode($value['upgrade_condition'], true);
            }
        }
        return $result;
    }

    protected function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['type'] != '') {
            $where['type'] = $get['type'];
        }
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['level'] = 'ASC';
        }
        $this->db->order($order);
        return $this;
    }

    public function addLevel($data)
    {
        if($data['promotion'] != ""){
            $data['promotion'] = json_encode($data['promotion'], true);
        }
        if(isset($data['upgrade_condition']) && $data['upgrade_condition'] != ""){

            if(in_array("order", $data['upgrade_condition']) && isset($data['order_condition']) && $data['order_condition'] != ""){
                $data['order_condition'] = json_encode($data['order_condition'], true);
            }
            if(in_array("people", $data['upgrade_condition']) && isset($data['people_condition']) && $data['people_condition'] != ""){
                $data['people_condition'] = json_encode($data['people_condition'], true);
            }
            if(in_array("good", $data['upgrade_condition']) && isset($data['good_condition']) && $data['good_condition'] != ""){
                $data['good_condition'] = json_encode($data['good_condition'], true);
            }
            if(in_array("commission", $data['upgrade_condition']) && isset($data['commission_condition']) && $data['commission_condition'] != ""){
                $data['commission_condition'] = json_encode($data['commission_condition'], true);
            }

            $data['upgrade_condition'] = json_encode($data['upgrade_condition'], true);
        }
        $this->db = Db::name('taoke_level');
        $data['add_time'] = time();
        $result = $this->db->insertGetId($data);
        return $result;
    }

    public function getLevelInfo($where)
    {
        $this->db = Db::name('taoke_level');
        $result = $this->db->where($where)->find();
        return $result;
    }

    public function updateLevel($where, $data)
    {
        if($data['promotion'] != ""){
            $data['promotion'] = json_encode($data['promotion'], true);
        }

        if(isset($data['upgrade_condition']) && $data['upgrade_condition'] != ""){

            if(in_array("order", $data['upgrade_condition']) && isset($data['order_condition']) && $data['order_condition'] != ""){
                $data['order_condition'] = json_encode($data['order_condition'], true);
            }else{
                $data['order_condition'] = "";
            }
            if(in_array("people", $data['upgrade_condition']) && isset($data['people_condition']) && $data['people_condition'] != ""){
                $data['people_condition'] = json_encode($data['people_condition'], true);
            }else{
                $data['people_condition'] = "";
            }
            if(in_array("good", $data['upgrade_condition']) && isset($data['good_condition']) && $data['good_condition'] != ""){
                $data['good_condition'] = $data['good_condition'];
            }else{
                $data['good_condition'] = "";
            }
            if(in_array("commission", $data['upgrade_condition']) && isset($data['commission_condition']) && $data['commission_condition'] != ""){
                $data['commission_condition'] = json_encode($data['commission_condition'], true);
            }else{
                $data['commission_condition'] = "";
            }
            $data['upgrade_condition'] = json_encode($data['upgrade_condition'], true);
        }else{
            $data['upgrade_condition'] = "";
        }
        $this->db = Db::name('taoke_level');
        $data['update_time'] = time();
        $result = $this->db->where($where)->update($data);
        return $result;
    }
}