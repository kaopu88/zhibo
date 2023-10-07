<?php

namespace app\admin\service;

use think\Db;

class Menu extends \bxkj_module\service\Menu
{
    public function add($inputData)
    {
        $data = $this->df->process('add@menu', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('menu')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        $this->clearCache($id);
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@menu', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('menu')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        $this->clearCache($data['id']);
        return $num;
    }

    public function validatePid($value, $rule, $data = null, $more = null)
    {
        if ($value == 0) return true;
        $num = Db::name('menu')->where(array('id' => $value))->count();
        return $num > 0;
    }

    public function validateMark($value, $rule, $data = null, $more = null)
    {
        $lastData = $more['df']->getLastData();
        $pid = $lastData['pid'];
        $where = [];
        $where[] = ['pid', '=', $pid];
        $where[] = ['mark', '=', $value];
        if (isset($lastData['id'])) $where[] = array('id', '<>', $lastData['id']);
        $num = Db::name('menu')->where($where)->count();
        return $num <= 0;
    }

    public function fillLevel($value, $rule, $data = null, $more = null)
    {
        $oldData = $more['df']->getLastData(false);
        $lastData = $more['df']->getLastData();
        if ($oldData['pid'] != $lastData['pid']) {
            if ($lastData['pid'] == 0) return 0;
            $parent = Db::name('menu')->where(array('id' => $lastData['pid']))->find();
            return $parent['level'] + 1;
        }
        return array();
    }


}