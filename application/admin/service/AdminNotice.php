<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class AdminNotice extends Service
{
	public function add($inputData)
    {
        $data = $this->df->process('add@admin_notice', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('admin_notice')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@admin_notice', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('admin_notice')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function getlist($where){
        $admin_notice = Db::name('admin_notice')->where($where)->order('sort desc,create_time desc')->select();
        return $admin_notice;
    }
}