<?php

namespace app\admin\service;

use think\Db;

class AdContent extends \bxkj_module\service\AdContent
{
    protected $db;

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('ad_content');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('ad_content');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $spaceIds = self::getIdsByList($result, 'space_id');
        $spaces = [];
        if (!empty($spaceIds)) $spaces = Db::name('ad_space')->whereIn('id', $spaceIds)->field('id,mark,name')->select();
        $now = time();
        foreach ($result as &$item) {
            $item['image'] = $item['image'] ? json_decode($item['image'], true) : [];
            if ($now < $item['start_time']) {
                $item['display_status'] = '0';//未开始
            } else if ($now >= $item['end_time']) {
                $item['display_status'] = '2';//已结束
            } else {
                $item['display_status'] = '1';
            }
            $item['space_info'] = $this->getItemByList($item['space_id'], $spaces);
            $item['os'] = $item['os'] ? explode(',', $item['os']) : [];
            $item['purview'] = $item['purview'] ? explode(',', $item['purview']) : [];
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
        if ($get['space_id'] != '') {
            $where[] = ['space_id', '=', $get['space_id']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'title');
        if ($get['os'] != '') {
            $this->db->where(function ($query) use ($get) {
                $query->where([['os', '=', '']]);
                $query->setLikeOr('os', $get['os']);
            });
        }
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['sort'] = 'desc';
            $order['create_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        if (!isset($inputData['os'])) $inputData['os'] = [];
        $inputData['purview'] = [];
        if (!empty($inputData['purview_login'])) $inputData['purview'][] = $inputData['purview_login'];
        if (!empty($inputData['purview_vip'])) $inputData['purview'][] = $inputData['purview_vip'];
        $data = $this->df->process('add@ad_content', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('ad_content')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        $spaceInfo = Db::name('ad_space')->where(['id' => $data['space_id']])->find();
        if (!empty($spaceInfo['mark'])) self::clearCache($spaceInfo['mark']);
        return $id;
    }

    public function update($inputData)
    {
        if (!isset($inputData['os'])) $inputData['os'] = [];
        $inputData['purview'] = [];
        if (!empty($inputData['purview_login'])) $inputData['purview'][] = $inputData['purview_login'];
        if (!empty($inputData['purview_vip'])) $inputData['purview'][] = $inputData['purview_vip'];
        $data = $this->df->process('update@ad_content', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('ad_content')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        $saveData = $this->df->getLastData(true);
        if ($saveData && $saveData['space_id']) {
            $spaceInfo = Db::name('ad_space')->where(['id' => $saveData['space_id']])->find();
            if (!empty($spaceInfo['mark'])) self::clearCache($spaceInfo['mark']);
        }
        return $num;
    }

    public function validateCity($value, $rule, $data = null, $more = null)
    {
        $cites = is_string($value) ? explode(',', $value) : $value;
        $num = Db::name('ad_space')->whereIn('id', $cites)->where('level', 2)->count();
        return $num === count($cites);
    }

    public function validateOs($value, $rule, $data = null, $more = null)
    {
        if (empty($value)) return true;
        $value = is_string($value) ? explode(',', $value) : $value;
        $lastData = $more['df']->getLastData();
        if (empty($lastData['space_id'])) return false;
        $adSpace = new AdSpace();
        $space = $adSpace->getSpace($lastData['space_id']);
        $platforms = json_decode($space['platform'], true);
        $platformArr = [];
        foreach ($platforms as $platform) {
            if ($platform['value']) $platformArr[] = $platform['value'];
        }
        return empty(array_diff($value, $platformArr));
    }

    public function getInfo($id)
    {
        $info = Db::name('ad_content')->where('id', $id)->find();
        if ($info) {
            $info['purview_login'] = '';
            $info['purview_vip'] = '';
            if (!empty($info['purview'])) {
                $tmp = explode(',', $info['purview']);
                $info['purview_login'] = in_array('login', $tmp) ? 'login' : (in_array('not_login', $tmp) ? 'not_login' : '');
                $info['purview_vip'] = in_array('vip', $tmp) ? 'vip' : (in_array('not_vip', $tmp) ? 'not_vip' : '');
            }
        }
        return $info;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择广告');
        $num = Db::name('ad_content')->whereIn('id', $ids)->update(['delete_time' => time()]);
        if ($num) {
            self::clearCacheByIds($ids);
        }
        return $num;
    }

}