<?php

namespace app\admin\service;

use bxkj_module\service\Auth;
use bxkj_module\service\Service;
use think\Db;

class Admin extends Service
{
    protected $db;

    public function getTotal($get)
    {
        $this->db = Db::name('admin');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('admin');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$value) {
            $value['is_root'] = $value['id'] == ROOT_UID ? '1' : '0';
            $value['group_list'] = Db::name('admin_group_access')
                ->alias('relation')
                ->field('relation.id rid,gp.name,gp.id')
                ->join('__ADMIN_GROUP__ gp', 'gp.id=relation.gid', 'LEFT')
                ->where(array('relation.uid' => $value['id']))->select();
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $db = Db::name('admin');
        $where = array();
        if ($get['gid'] != '') {
            $where['relation.gid'] = $get['gid'];
        }
        if ($get['status'] != '') {
            $where['admin.status'] = $get['status'];
        }
        $db->setKeywords(trim($get['keyword']), 'phone admin.phone', 'number admin.id', 'admin.username,admin.realname,number admin.phone');
        $db->where($where);
        $sql = $db->alias('admin')
            ->field('admin.id id,admin.username,admin.realname,admin.avatar,admin.phone,admin.login_time,admin.status,admin.create_time')
            ->join('__ADMIN_GROUP_ACCESS__ relation', 'relation.uid=admin.id', 'LEFT')->select(false);
        $this->db->field('id,username,realname,avatar,phone,login_time,status,create_time')->table("($sql) adminpm")->group('id');
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@admin', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $data['salt'] = sha1(uniqid() . get_ucode());
        $data['password'] = sha1($data['password'] . $data['salt']);
        $id = Db::name('admin')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        if (!empty($inputData['group_ids'])) {
            $this->relationGroup($id, $inputData['group_ids']);
        }
        return $id;
    }

    public function update($inputData)
    {
        unset($inputData['password'], $inputData['phone']);//不得再次修改管理员密码和手机号，只能管理员自己修改
        $data = $this->df->process('update@admin', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('admin')->where(array('id' => $data['id']))->update($data);
        $group_ids = empty($inputData['group_ids']) ? array() : $inputData['group_ids'];
        $this->relationGroup($data['id'], $group_ids);
        return 1;
    }

    //关联分组
    protected function relationGroup($aid, $gidArr = array())
    {
        $adminGroup = new AdminGroup();
        $myGroupIds = $adminGroup->getAdminGroups(AID, true);
        $gidArr = array_intersect($myGroupIds, $gidArr);
        $hasChange = false;
        $hasGidArr = array();
        $result = Db::name('admin_group_access')->field('id,gid')->where(array('uid' => $aid))->select();
        foreach ($result as $item) {
            $hasGidArr[] = $item['gid'];
        }
        $delGidArr = array_diff($hasGidArr, $gidArr);
        $addGidArr = array_diff($gidArr, $hasGidArr);
        foreach ($addGidArr as $gid) {
            $id = Db::name('admin_group_access')->insertGetId(array('gid' => $gid, 'uid' => $aid));
            if ($id) $hasChange = true;
        }
        if (!empty($delGidArr)) {
            $num = Db::name('admin_group_access')->whereIn('gid', $delGidArr)->where(array('uid' => $aid))->delete();
            if ($num) $hasChange = true;
        }
        if ($hasChange) {
            $key = Auth::getAdminGroupsKey($aid);
            cache($key, null);
        }
    }

    public function getInfo($id)
    {
        $result = Db::name('admin')->where(array('id' => $id))->find();
        $groupList = Db::name('admin_group_access')->alias('rel')->field('gp.id,gp.name')->where(array('uid' => $result['id']))
            ->join('__ADMIN_GROUP__ gp', 'gp.id=rel.gid', 'left')->select();
        $result['group_list'] = $groupList;
        $group_ids = array();
        foreach ($groupList as $group) {
            $group_ids[] = $group['id'];
        }
        $result['group_ids'] = $group_ids ? implode(',', $group_ids) : '';
        return $result;
    }

    public function delete($ids = array())
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $num = Db::name('admin')->whereIn('id', $ids)->delete();
        if ($num) {
            $num2 = Db::name('admin_group_access')->whereIn('uid', $ids)->delete();
            if ($num2) {
                foreach ($ids as $uid) {
                    $key = Auth::getAdminGroupsKey($uid);
                    cache($key, null);
                }
            }
        }
        return $num;
    }

    public function getAdminsByIds($aids)
    {
        if (empty($aids)) return [];
        $admins = Db::name('admin')->whereIn('id', $aids)->field('id,username,avatar,realname,phone')->select();
        return $admins ? $admins : [];
    }

}