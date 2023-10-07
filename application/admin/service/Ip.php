<?php

namespace app\admin\service;

use think\Model;

class Ip extends Model
{
    protected static $map = array('y' => 'years', 'm' => 'months', 'w' => 'week', 'd' => 'days');

    public function getTotal($get)
    {
        $where = $this->setWhere($get);
        $count = $this->where($where)->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $where = $this->setWhere($get);
        $result = $this->where($where)->order(['id' => 'desc'])->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['keyword'] != '') {
            $where[] = ['ip_adress', '=', $get['keyword']];
        }

        return $where;
    }

    public function update_ip($inputData)
    {
        if (empty($inputData['ip_adress'])) return ['code' => 101, 'msg' => 'Ip地址不能为空'];
        if (empty($inputData['length'])) return ['code' => 102, 'msg' => '时长不能为空'];
        $res = $this->where(array('id' => $inputData['id']))->find();
        if (empty($res)) return ['code' => 103, 'msg' => '数据不存在'];
        $data['ip_adress'] = $inputData['ip_adress'];
        $data['status'] = $inputData['status'];
        $start = $res['expire_time'] > time() ? $res['expire_time'] : time();
        $length = $inputData['length'];
        $unit = self::$map[$inputData['unit']];
        $data['expire_time'] = strtotime("+{$length} {$unit}", $start);

        $num = $this->where(array('id' => $inputData['id']))->update($data);
        if (!$num) return ['code' => 102, 'msg' => '更新失败'];
        return ['code' => 200];
    }

    public function add($inputData)
    {
        if (empty($inputData['ip_adress'])) return ['code' => 101, 'msg' => 'Ip地址不能为空'];
        if (empty($inputData['length'])) return ['code' => 101, 'msg' => '时长不能为空'];
        $res = $this->where(array('ip_adress' => $inputData['ip_adress']))->find();
        if (!empty($res)) return ['code' => 103, 'msg' => '该IP添加过啦!请去更新即可'];
        $data['ip_adress'] = $inputData['ip_adress'];
        $data['status'] = $inputData['status'];
        $data['unit'] = $inputData['unit'];
        $data['length'] = $inputData['length'];
        $data['create_time'] = time();
        $start = time();
        $length = $inputData['length'];
        $unit = self::$map[$inputData['unit']];
        $data['expire_time'] = strtotime("+{$length} {$unit}", $start);
        $id = $this->insertGetId($data);
        if (!$id) return ['code' => 102, 'msg' => '添加失败'];
        return ['code' => 200];
    }
}