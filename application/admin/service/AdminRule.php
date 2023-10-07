<?php

namespace app\admin\service;

use bxkj_module\service\Auth;
use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class AdminRule extends Service
{

    public function add($data)
    {
        unset($data['id']);
        $data = $this->df->process('add@admin_rule', $data)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('admin_rule')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        return $id;
    }

    public function update($data)
    {
        $data = $this->df->process('update@admin_rule', $data)->output();
        $lastData = $this->df->getLastData(false);
        if (empty($lastData)) return $this->setError('规则不存在');
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('admin_rule')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        $this->clearAllGroupRules();
        return $num;
    }

    //按照分类树返回规则列表
    public function getRulesByCategory($id = null, $hasArr = null, $ignore = false)
    {
        $cateTree = new Tree('category');
        if (isset($id)) {
            $path = $cateTree->getParentPath($id, 'rule_group', true);
            if (empty($path)) return array();
            $lv = count($path) - 1;
        } else {
            $id = 'rule_group';
            $lv = 0;
        }
        $ids = array();
        if ($lv == 2) {
            $children = array('id' => $id);
            $this->recursiveQuery($children, $lv, $hasArr, $ignore);
            return $children['children'];
        } else {
            $children = $cateTree->setFieldOptions('id,pid,name,mark,level')->getChildrenByMark($id, $ids, true);
            $this->recursiveQuery($children, $lv + 1, $hasArr, $ignore);
            return $children;
        }
    }

    //递归查询
    private function recursiveQuery(&$children, $lv, $hasArr = null, $ignore = false)
    {
        if (is_array($children) && isset($children['id'])) {
            if ($lv == 2) {
                if (isset($hasArr)) {
                    if (!empty($hasArr)) {
                        $where = array('cid' => $children['id']);
                        $result = Db::name('admin_rule')->whereIn('id', $hasArr)->where($where)->select();
                    } else {
                        $result = array();
                    }
                } else {
                    $result = Db::name('admin_rule')->where(array('cid' => $children['id']))->select();
                }
                foreach ($result as &$value) {
                    $value['_id'] = 'rule_' . $value['id'];
                    $value['type'] = 'rule';
                }
                $children['children'] = $result;
                $children['type'] = 'bottom_category';
            } else {
                if (!empty($children['children']))
                    $this->recursiveQuery($children['children'], $lv + 1, $hasArr, $ignore);
                if ($ignore && empty($children['children'])) unset($children['children']);
                $children['type'] = 'top_category';
            }
            $children['_id'] = 'cat_' . $children['id'];
            $children['state'] = array('opened' => true);
        } else {
            if (!empty($children)) {
                foreach ($children as $k => &$child) {
                    if (!empty($child))
                        $this->recursiveQuery($child, $lv, $hasArr, $ignore);
                    if ($ignore && empty($child['children'])) unset($children[$k]);
                }
            }
        }
    }

    //检查所属分类
    public function validateCid($value, $rule, $data = null, $more = null)
    {
        $cateTree = new Tree('category');
        $path = $cateTree->getParentPath((int)$value, 'rule_group', true);
        if ($path[2] != $value) {
            return false;
        }
        return true;
    }

    public function clearAllGroupRules($ids = null)
    {
        $groups = Db::name('admin_group')->field('id,rules')->select();
        foreach ($groups as $group) {
            if (is_array($ids)) {
                $rules = $group['rules'] ? explode(',', $group['rules']) : [];
                $tmp = [];
                $has = false;
                foreach ($rules as $rule) {
                    if (!in_array($rule, $ids)) {
                        $tmp[] = $rule;
                    } else {
                        $has = true;
                    }
                }
                if ($has) {
                    $tmpIds = $tmp ? implode(',', $tmp) : '';
                    Db::name('admin_group')->where('id', $group['id'])->update(['rules' => $tmpIds]);
                }
            }
            $key = Auth::getAdminGroupRulesKey($group['id']);
            cache($key, null);
        }
    }
}