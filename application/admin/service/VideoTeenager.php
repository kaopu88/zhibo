<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class VideoTeenager extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('video_teenager');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('video_teenager');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->where($where);
        $this->db->setKeywords(trim($get['keyword']), '', '', 'title');
        return $this;
    }

    //设置排序规则
    private function setOrder()
    {
        $order = array();
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择视频');
        $num = Db::name('video_teenager')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除视频失败');
        return $num;
    }

    public function changeStatus($ids, $status)
    {
        if (empty($ids)) return $this->setError('请选择视频');
        if (!in_array($status, ['0', '1'])) return $this->setError('状态值不正确');
        $num = Db::name('video_teenager')->whereIn('id', $ids)->update(["status"=>$status]);
        if (!$num) return $this->setError('状态切换失败');
        return $num;
    }


}