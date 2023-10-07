<?php

namespace app\core\service;

use bxkj_common\RedisClient;
use think\Db;

class Region extends \bxkj_module\service\Region
{
    public function getRegionName($ids, $glue = ',')
    {
        $ids = is_string($ids) ? explode(',', $ids) : (is_array($ids) ? $ids : array($ids));
        $result = Db::name('region')->whereIn('id', $ids)->field('id,name')->where('status', '1')->limit(0, count($ids))->select();
        $arr = [];
        $has = false;
        foreach ($ids as $id) {
            $item = $this->getItemByList($id, $result);
            $name = $item ? $item['name'] : '';
            if (!empty($name)) $has = true;
            $arr[] = $name;
        }
        return $has ? implode($glue, $arr) : '';
    }


    public function getRegionTree($id, $mode = 'default')
    {
        $fields = array(
            'ios' => array(
                'map' => array(
                    array('name' => 'countryname', 'children' => 'province', 'id' => 'id', 'pid' => 'parentid'),
                    array('name' => 'provincename', 'children' => 'city', 'id' => 'id', 'pid' => 'parentid'),
                    array('name' => 'cityname', 'children' => 'area', 'id' => 'id', 'pid' => 'parentid'),
                    array('name' => 'areaname', 'children' => '', 'id' => 'id', 'pid' => 'parentid')
                ),
                'pos' => true
            )
        );
        if (empty($fields[$mode])) return $this->setError('模式不正确');
        $map = $fields[$mode]['map'];
        $pos = $fields[$mode]['pos'];
        $region = Db::name('region')->where(array('status' => '1', 'id' => $id))->field('id,pid,name,level,pinyin,zipcode')->find();
        if (!$region) return $this->setError('根节点不存在');
        $childField = $map[(int)$region['level']]['children'];
        $redis = RedisClient::getInstance();
        $json = $redis->get("cache:region_tree:{$id}_{$mode}");
        if (empty($json)) {
            $max = 3;
            $tree = [];
            $tree[$map[(int)$region['level']]['name']] = $region['name'];
            $tree[$map[(int)$region['level']]['id']] = $region['id'];
            $tree[$map[(int)$region['level']]['pid']] = $region['pid'];
            $tree['pinyin'] = $region['pinyin'];
            $tree['zipcode'] = $region['zipcode'];
            $tree['level'] = $region['level'];
            if ($region['level'] < $max) {
                $tree[$childField] = [];
                $this->recursive($tree[$childField], $region, $max, $map);
            }
            $json = json_encode($tree ? $tree : array());
            $redis->set("cache:region_tree:{$id}_{$mode}", $json);
        }
        $nodes = json_decode($json, true);
        return $pos ? ($nodes[$childField] ? $nodes[$childField] : []) : $nodes;
    }

    private function recursive(&$tree, $region, $max, $map)
    {
        $arr = Db::name('region')->where(array('pid' => $region['id'], 'status' => '1'))
            ->field('id,pid,name,level,pinyin,zipcode')
            ->order('sort desc,pinyin asc,id asc')->select();
        if (!empty($arr)) {
            foreach ($arr as $item) {
                $tmp = [];
                $childField = $map[(int)$item['level']]['children'];
                $tmp[$map[(int)$item['level']]['name']] = $item['name'];
                $tmp[$map[(int)$item['level']]['id']] = $item['id'];
                $tmp[$map[(int)$item['level']]['pid']] = $item['pid'];
                $tmp['pinyin'] = $item['pinyin'];
                $tmp['zipcode'] = $item['zipcode'];
                $tmp['level'] = $item['level'];
                if ($tmp['level'] < $max) {
                    $tmp[$childField] = [];
                    $this->recursive($tmp[$childField], $item, $max, $map);
                }
                $tree[] = $tmp;
            }
        }
    }


}