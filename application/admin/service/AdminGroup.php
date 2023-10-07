<?php

namespace app\admin\service;

use bxkj_module\service\Auth;
use bxkj_module\service\Service;
use think\Db;

class AdminGroup extends Service
{
    protected $db;

    public function getTotal($get)
    {
        $this->db = Db::name('admin_group');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('admin_group');
        $this->setWhere($get);
        $this->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$value) {
            $value['rules_num'] = empty($value['rules']) ? 0 : (count(explode(',', $value['rules'])));
            $value['works_num'] = empty($value['works']) ? 0 : (count(explode(',', $value['works'])));
            $value['num'] = Db::name('admin_group_access')->where(array('gid' => $value['id']))->count();
        }
        return $result;
    }

    public function getInfo($id)
    {
        $result = Db::name('admin_group')->where(array('id' => $id))->find();
        $rules = empty($result['rules']) ? array() : explode(',', $result['rules']);
        $adminRule = new AdminRule();
        $tree = $adminRule->getRulesByCategory(null, $rules, false);
        $result['tree'] = $tree;
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');
        $this->db->where($where);
        return $this;
    }

    //添加分组
    public function add($inputData)
    {
        $data = $this->df->process('add@admin_group', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('admin_group')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        return $id;
    }

    //更新分组
    public function update($inputData)
    {
        $data = $this->df->process('update@admin_group', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('admin_group')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('编辑失败');
        $lastData = $this->df->getLastData(false);
        if ($lastData['rules'] != $data['rules']) {
            $key = Auth::getAdminGroupRulesKey($data['id']);
            cache($key, null);
        }
        if ($lastData['status'] != $data['status']) {
            $this->clearAllAdminGroups([$data['id']]);
        }
        return $num;
    }

    public function validateRules($value, $rule = '', $data = null, $more = null)
    {
        if (empty($value)) return true;
        $arr = is_string($value) ? explode(',', $value) : $value;
        $num = Db::name('admin_rule')->whereIn('id', $arr)->count();
        return $num == count($arr);
    }

    public function del($ids = array())
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $result = Db::name('admin_group')->whereIn('id', $ids)->delete();
        if ($result) {
            $this->clearAllAdminGroups($ids);
            Db::name('admin_group_access')->whereIn('gid', $ids)->delete();
        }
        return $result;
    }

    public function changeStatus($id, $status)
    {
        $ids = is_array($id) ? $id : array($id);
        if (empty($ids)) return $this->setError('请选择分组');
        $result = Db::name('admin_group')->whereIn('id', $ids)->update(array('status' => $status));
        if (!$result) return $this->setError('更新失败');
        $this->clearAllAdminGroups($ids);
        return $result;
    }

    //清除所有关联组的用户缓存
    protected function clearAllAdminGroups($ids)
    {
        $users = Db::name('admin_group_access')->whereIn('gid', $ids)->group('uid')->select();
        foreach ($users as $user) {
            $key = Auth::getAdminGroupsKey($user['uid']);
            cache($key, null);
        }
    }

    public function getAdminGroups($uid = null, $returnId = false)
    {
        $groups = Db::name('admin_group')->field('id,name,rules')->order('create_time desc')->select();
        if (!isset($uid) || $uid == ROOT_UID) return $returnId ? self::getIdsByList($groups, 'id') : $groups;
        $myGroupIds = [];
        $groupAccess = Db::name('admin_group_access')->where(['uid' => $uid])->select();
        foreach ($groupAccess as $item) {
            $myGroupIds[] = $item['gid'];
        }
        if (empty($myGroupIds)) return [];
        $myRules = [];
        foreach ($groups as $group) {
            if (in_array($group['id'], $myGroupIds)) {
                $myRules = array_merge($myRules, $group['rules'] ? explode(',', $group['rules']) : []);
            }
        }
        $newMyGroupIds = $myGroupIds;
        $myRules = array_unique($myRules);
        //包含相同权限的组也可以
        foreach ($groups as $group) {
            if (!in_array($group['id'], $myGroupIds)) {
                $rules = array_unique($group['rules'] ? explode(',', $group['rules']) : []);
                if (empty(array_diff($rules, $myRules))) {
                    $newMyGroupIds[] = $group['id'];
                }
            }
        }
        if ($returnId) return $newMyGroupIds;
        $newGroups = [];
        foreach ($groups as $group) {
            if (in_array($group['id'], $newMyGroupIds)) {
                $newGroups[] = $group;
            }
        }
        return $newGroups;
    }

    public function getAdminGroupsByAid($aid = null)
    {
        if (!isset($aid) || $aid == ROOT_UID) {
            return [];
        }
        $group_list = Db::name('admin_group_access')
            ->alias('relation')
            ->field('relation.id rid,gp.name,gp.id')
            ->join('__ADMIN_GROUP__ gp', 'gp.id=relation.gid', 'LEFT')
            ->where(array('relation.uid' => $aid))->select();
        return $group_list;
    }

}