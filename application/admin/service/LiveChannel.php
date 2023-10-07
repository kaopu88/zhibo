<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;
use bxkj_common\RedisClient;

class LiveChannel extends Service
{
    protected static $liveChannel = 'BG_NAV:LIVE:';

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_channel');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function add($inputData)
    {
        Service::startTrans();
        $data = $this->df->process('add@live_channel', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('live_channel')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        $this->delLiveChannel($data['parent_id']);
        if ($data['parent_id'] != '0') $this->checkSubChannel($data['parent_id']);
        Service::commit();
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@live_channel', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $last_parent_id = Db::name('live_channel')->where(array('id' => $data['id']))->value('parent_id');
        $num = Db::name('live_channel')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        $parent_id = $data['parent_id'];
        if ($last_parent_id != $parent_id && $last_parent_id != '0'){
            $this->checkSubChannel($last_parent_id);
        }
        if ($parent_id != '0'){
            $this->checkSubChannel($parent_id);
        }
        if ($last_parent_id != $parent_id){
            $this->delLiveChannel($last_parent_id);
            $this->delLiveChannel($parent_id);
        }else{
            $this->delLiveChannel($parent_id);
        }
        return $num;
    }

    public function delete($ids)
    {
        $result = Db::name('live_channel')->distinct(true)->whereIn('id', $ids)->field('parent_id')->limit(count($ids))->select();
        $num = Db::name('live_channel')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除失败');
        foreach ($result as $item){
            if ($item['parent_id'] != '0'){
                $this->checkSubChannel($item['parent_id']);
                $this->delLiveChannel($item['parent_id']);
            }else{
                $this->delLiveChannel($item['parent_id']);
            }
        }
        return $num;
    }

    public function checkSubChannel($parent_id)
    {
        $num = Db::name('live_channel')->where(array('parent_id'=>$parent_id))->count();
        $sub_channel = Db::name('live_channel')->where(array('id' => $parent_id))->value('sub_channel');
        if (($num == 0 && $sub_channel=='1') || $num > 0 && $sub_channel=='0')
        {
            Db::name('live_channel')->where(array('id' => $parent_id))->update(array('sub_channel'=>$sub_channel ? '0' : '1'));
        }
    }

    public function delLiveChannel($parent_id)
    {
        $redis = RedisClient::getInstance();
        $key = $parent_id == 0 ? 'parent' : 'childe' . $parent_id;
        $redis->del(self::$liveChannel . $key);
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_channel');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        foreach ($result as &$item)
        {
            $item['parent_str'] = $item['parent_id'] == '0' ? '' : Db::name('live_channel')->where(array('id' => $item['parent_id']))->value('name');
        }
        return $result;
    }


    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }

        if (trim($get['parent_id']) != '') {
            $where[] = ['parent_id', '=', trim($get['parent_id'])];
        }

        if ($get['sub_channel'] != '') {
            $where[] = ['sub_channel', '=', $get['sub_channel']];
        }

        if ($get['cid'] != '') {
            $where[] = ['cid', '=', $get['cid']];
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
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('live_channel')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }

    public function getChannelsByIds($channels)
    {
        if (empty($channels)) return [];
        $result = Db::name('live_channel')->whereIn('id', $channels)->field('id,name')->limit(count($channels))->select();
        return $result ? $result : [];
    }

}
