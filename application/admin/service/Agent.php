<?php

namespace app\admin\service;

use bxkj_module\service\Auth;
use bxkj_module\service\Service;
use think\Db;

class Agent extends \bxkj_module\service\Agent
{

    public function getTotal($get)
    {
        $this->db = Db::name('agent');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('agent');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $regionIds = self::getIdsByList($result, 'province_id,city_id');
        $regionList = Db::name('region')->whereIn('id', $regionIds)->field('id,name')->select();
        $now = time();
        foreach ($result as &$value) {
            if (!empty($value['province_id'])) {
                $value['province_name'] = self::getItemByList($value['province_id'], $regionList, 'id', 'name');
            }
            if (!empty($value['city_id'])) {
                $value['city_name'] = self::getItemByList($value['city_id'], $regionList, 'id', 'name');
            }
            if (!empty($value['aid'])) {
                $value['admin_name'] = Db::name('admin')->where(['id' => $value['aid']])->value('username');
            }
            if (!empty($value['pid'])) {
                $value['parent'] = Db::name('agent')->field('id,name')->where(['id' => $value['pid']])->find();
            }
            $this->parseExpire($value);
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['sort'] = 'DESC';
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where[] = ['is_visible', '=', '0'];
        if ($get['pid'] != '') {
            $where[] = ['pid', '=', $get['pid']];
        }
        if ($get['aid'] != '') {
            $where[] = ['aid', '=', $get['aid']];
        }
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['level'] != '') {
            $where[] = ['level', '=', $get['level']];
        }
        if ($get['grade'] != '') {
            $where[] = ['grade', '=', $get['grade']];
        }
        if ($get['district'] != '') {
            $where[] = ['district_id', '=', $get['district']];
        } else if ($get['city'] != '') {
            $where[] = ['city_id', '=', $get['city']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone contact_phone', 'number id', 'number contact_phone,name');
        $this->db->where($where);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@agent', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $data['root_id'] = 0;
        $data['level'] = 0;
        $data['pid'] = 0;
        $id = Db::name('agent')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        return $id;
    }

    public function update($inputData)
    {
        unset($inputData['pid'], $inputData['logo'], $inputData['remark']);
        $data = $this->df->process('update@agent', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('agent')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('编辑失败');
        return $num;
    }

    //TODO
    public function delete($ids = array())
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $num = Db::name('agent')->whereIn('id', $ids)->delete();
        $del = Db::name('agent_admin')->whereIn('agent_id', $ids)->delete();
        foreach ($ids as $k => $v) {
            $delte = Db::name('promotion_relation')->where('agent_id', $v)->delete();
        }

        if ($num) {
            /* $num2 = Db::name('admin_group_access')->whereIn('uid', $ids)->delete();
             if ($num2) {
                 foreach ($ids as $uid) {
                     $key = Auth::getAdminGroupsKey($uid);
                     cache($key, null);
                 }
             }*/
        }
        return $num;
    }

    public function getIndexTotal($get)
    {
        $this->db = Db::name('agent');
        $this->setIndexWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getIndex($get, $offset = 0, $length = 10)
    {
        $this->db = Db::name('agent');
        $fields = 'id,name,logo,contact_phone,level,grade,contact_phone,contact_name,create_time,total_cons,total_fans,
        total_millet,total_duration,cash_type';
        $this->setIndexWhere($get);
        $this->setIndexOrder($get);
        $index = $this->db->field($fields)->limit($offset, $length)->select();
        return $index ? $index : [];
    }

    protected function setIndexWhere($get)
    {
        $where = [];
        if ($get['pid'] != '') {
            $where[] = ['pid', '=', $get['pid']];
        }
        $where[] = ['applystatus', '=', 0];
        $this->db->setKeywords(trim($get['keyword']), 'phone contact_phone', 'number id', 'number contact_phone,name');
        $this->db->where($where);
        return $this;
    }

    protected function setIndexOrder($get)
    {
        $this->db->order('create_time desc,id desc');
    }


    public function change_status($agent_id, $status)
    {
        Service::startTrans();
        $result = Db::name('agent')->where('id', $agent_id)->update(array('status' => $status));
        if (!$result) return $this->setError('切换' . config('app.agent_name') . '状态失败');
        //查找此代理商名下虚拟号
        $where = [['user.delete_time', 'null']];
        $where[] = ['user.isvirtual', '=', '1'];
        $where[] = ['pr.agent_id', '=', $agent_id];
        $uids = Db::name('user')->alias('user')->field('user.user_id')->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id')->where($where)->select();
        $virtualIds = array();

        if (false) {
            foreach ($uids as $item) {
                $virtualIds[] = $item['user_id'];
            }
            //冻结虚拟号
            $userService = new User();
            $num = $userService->changeStatus($virtualIds, 0, null, '', '', AID);
            if (!$num) {
                Service::rollback();
                return $this->setError('冻结虚拟号失败');
            }
            //解绑虚拟号
            $proRel = new \common_module\service\PromotionRelation();
            $total = $proRel->unbindWithAgent($virtualIds, $agent_id);
            if (!$total) {
                Service::rollback();
                return $this->setError('取消虚拟号绑定失败');
            }
            $update = array();
            //重置昵称
            $update['nickname'] = '已重置';
            $update['last_renick_time'] = null;
            //重置密码
            $update['isset_pwd'] = '0';
            $update['salt'] = '';
            $update['password'] = '';
            $update['change_pwd_time'] = time();
            //重置手机号
            $update['phone'] = '';
            $update['phone_code'] = '';
            //重置头像
            $update['avatar'] = img_url('', '', 'avatar');
            $num = Db::name('user')->whereIn('user_id', $virtualIds)->update($update);
            if (!$num) {
                Service::rollback();
                return $this->setError('重置虚拟号信息失败');
            }
            foreach ($virtualIds as $userId) {
                \app\admin\service\User::updateRedis($userId, $update);
            }
        }
        Service::commit();
        return $result;
    }

    public function handler($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        Service::startTrans();
        $resAgent = Db::name('agent')->where($where)->find();
        if (empty($resAgent)) return $this->setError('申请记录不存在');
        if ($resAgent['applystatus'] != '1') return $this->setError('审核状态不正确');
        if (!in_array($status, [0, 1])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        if ($inputData['audit_status'] == 1) {
            $update = ['handle_time' => time(), 'applystatus' => 0, 'status' => $inputData['audit_status']];
        } else {
            $update = ['handle_time' => time(), 'applystatus' => 2, 'status' => $inputData['audit_status']];
        }

        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('agent')->where('id', $resAgent['id'])->update($update);
        $insdata = [
            'user_id' => $resAgent['uid'],
            'agent_id' => $resAgent['id'],
            'create_time' => time()
        ];

        if ($inputData['audit_status'] == 1) {
            $relacation = Db::name('promotion_relation')->insertGetId($insdata);

            $userService = new User();
            $user = $userService->getUser($resAgent['uid']);

            $agentAdminService = new AgentAdmin();
            $id = $agentAdminService->setNewRoot(
                [
                    'agent_id' => $resAgent['id'],
                    'password' => $resAgent['temppass'],
                    'username' =>!empty($user['phone']) ?$user['phone'] : $resAgent['name'],
                    'phone' => !empty($user['phone'])? $user['phone'] : $resAgent['uid']
                ]);
            if (!$id) return $this->setError($agentAdminService->getError());
        } else {
            $relacation = 1;
        }

        if (!$num && !$relacation) return $this->setError('处理失败');
        Service::commit();
        return array_merge($resAgent, $update);
    }
}