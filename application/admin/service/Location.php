<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Location extends Service
{
    protected static $level = ['国家','省','市','区县','街道'];
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('location');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('location');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (!$result) return [];
        $regionIds = self::getIdsByList($result, 'province_id,city_id,district_id');
        $regionList = Db::name('region')->whereIn('id', $regionIds)->field('id,name')->select();
        foreach ($result as &$item) {
            if (!empty($value['province_id'])) {
                $value['province_name'] = self::getItemByList($value['province_id'], $regionList, 'id', 'name');
            }
            if (!empty($value['city_id'])) {
                $value['city_name'] = self::getItemByList($value['city_id'], $regionList, 'id', 'name');
            }
            if (!empty($value['district_id'])) {
                $value['district_name'] = self::getItemByList($value['district_id'], $regionList, 'id', 'name');
            }
            $item['level_txt'] = self::$level[$item['level']];
            $item['cover_num'] = count(json_decode($item['photos'],true));
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if ($get['city'] != '')
        {
            $where['city_id'] = $get['city'];
        }

        if ($get['province'] != '')
        {
            $where['province_id'] = $get['province'];
        }

        if ($get['district'] != '')
        {
            $where['district_id'] = $get['district'];
        }

        if ($get['level'] != '')
        {
            $where['level'] = $get['level'];
        }

        $this->db->setKeywords(trim($get['keyword']),'','number id','poi_id,name');
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }
}

