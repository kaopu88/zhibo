<?php

namespace app\admin\service;

use think\Model;

class UserPoster extends Model
{
    public function getTotal($get)
    {
        $where = $this->setWhere($get);
        $count = $this->where($where)->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $where = $this->setWhere($get);
        $result = $this->where($where)->order(['id' => 'desc', 'sort' => 'desc'])->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }

        return $where;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = $this->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }

    public function update_poster($inputData)
    {
        if (empty($inputData['bg_url'])) return ['code' => 101, 'msg' => '背景图片不能为空'];
        $num = $this->where(array('id' => $inputData['id']))->update($inputData);
        if (!$num) return ['code' => 102, 'msg' => '更新失败'];
        return ['code' => 200];
    }

    public function add($inputData)
    {
        if (empty($inputData['bg_url'])) return ['code' => 101, 'msg' => '背景图片不能为空'];
        $inputData['create_time'] = time();
        $id = $this->insertGetId($inputData);
        if (!$id) return ['code' => 102, 'msg' => '添加失败'];
        return ['code' => 200];
    }
}