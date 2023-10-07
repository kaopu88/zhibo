<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class AdSpace extends Service
{
    protected $db;

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('ad_space');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('ad_space');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['count'] = Db::name('ad_content')->where([
                ['delete_time', 'null'],
                ['space_id', '=', $item['id']]
            ])->count();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [['delete_time', 'null']];
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');
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
        $data = $this->df->process('add@ad_space', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('ad_space')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@ad_space', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $info = Db::name('ad_space')->where(['id' => $data['id']])->find();
        if (empty($info)) return $this->setError('广告位不存在');
        $num = Db::name('ad_space')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        if ($info['mark']) AdContent::clearCache($info['mark']);
        return $num;
    }

    //获取广告位选项
    public function getSpaces($status = '1', $length = 100)
    {
        $where = [['delete_time', 'null']];
        if (isset($status)) array_push($where, ['status', 'eq', $status]);
        $spaces = Db::name('ad_space')->where($where)->field('id,mark,name,img_config,platform')->limit($length)->select();
        foreach ($spaces as &$space) {
            $this->extendPlatform($space);
        }
        return $spaces;
    }

    public function getSpace($id, $status = '1')
    {
        $where = [['delete_time', 'null']];
        if (isset($status)) $where[] = ['status', 'eq', $status];
        $space = Db::name('ad_space')->where($where)->field('id,mark,name,img_config,platform')->find();
        if (empty($space)) return $space;
        $this->extendPlatform($space);
        return $space;
    }

    private function extendPlatform(&$space)
    {
        if (!empty($space['platform'])) {
            $platformArr = explode(',', $space['platform']);
            $platformConfig = [];
            foreach ($platformArr as $value) {
                $platformConfig[] = [
                    'name' => enum_attr('ad_os', $value, 'name'),
                    'value' => $value
                ];
            }
        } else {
            $platformConfig = enum_array('ad_os', null, 'name,value');
        }
        $space['platform'] = json_encode($platformConfig);
    }

    public function delete($ids)
    {
        $spaces = Db::name('ad_space')->whereIn('id', $ids)->select();
        $num = Db::name('ad_space')->whereIn('id', $ids)->update(['delete_time' => time()]);
        if ($num) {
            Db::name('ad_content')->whereIn('space_id', $ids)->update(['delete_time' => time()]);
            foreach ($spaces as $space) {
                if ($space['mark']) AdContent::clearCache($space['mark']);
            }
        }
        return $num;
    }


}