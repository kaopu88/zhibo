<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class Help extends Service
{
	public function add($inputData)
    {
        $data = $this->df->process('add@help', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('help')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@help', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('help')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function validateCategoryId($value, $rule, $data = null, $more = null)
    {
        $treeModel = new Tree('category');
        $pathArr = $treeModel->getParentPath($value, 'help_category', true);
        return !empty($pathArr);
    }

    public function fillPCatId($value, $rule, $data = null, $more = null)
    {
        if (empty($data['pcat_id'])) {
            $result = Db::name('category')->where(['id' => $value])->find();
            if (!empty($result)) {
                return ['pcat_id' => $result['pid']];
            }
        }
    }

    //获取总类目
    public function getTotalList($result)
    {
        for ($i = 0; $i < count($result); $i++) {
            $child_list = Db::name('category')->where(array('pid' => $result[$i]['id']))->select();
            foreach($child_list as &$value){
                $value['help_list'] = Db::name('help')->where(array('status' => 1,'pcat_id' => $result[$i]['id'],'cat_id' => $value['id']))->order('sort desc,create_time desc')->limit(8)->select();
            }
            unset($value);
            $result[$i]['child_num'] = Db::name('category')->where(array('pid' => $result[$i]['id']))->count();
            $result[$i]['child_list'] = $child_list;
        }
        return $result;
    }
}