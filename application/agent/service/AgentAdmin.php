<?php

namespace app\agent\service;

use think\Db;

class AgentAdmin extends \bxkj_module\service\AgentAdmin
{
    protected $db;

    public function getTotal($get)
    {
        $this->db = Db::name('agent_admin');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('agent_admin');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$value) {
            $value['is_root'] = $value['id'] == ROOT_UID ? '1' : '0';
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
        $where = array();
        $where['is_root'] = 0;
        $where['agent_id'] = AGENT_ID;
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone phone', 'number id', 'username,realname,number phone');
        $this->db->where($where);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@agent_admin', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $data['salt'] = sha1(uniqid() . get_ucode());
        $data['password'] = sha1($data['password'] . $data['salt']);
        $id = Db::name('agent_admin')->insertGetId($data);
        if (!$id) return $this->setError('添加失败');
        return $id;
    }

    public function update($inputData)
    {
        unset($inputData['password'], $inputData['phone']);//不得再次修改管理员密码和手机号，只能管理员自己修改
        $data = $this->df->process('update@agent_admin', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('agent_admin')->where(array('id' => $data['id']))->update($data);
        return $num;
    }

    public function delete($ids = array())
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $num = Db::name('agent_admin')->whereIn('id', $ids)->delete();
        return $num;
    }

    public function getAdminsByIds($aids)
    {
        if (empty($aids)) return [];
        $admins = Db::name('agent_admin')->whereIn('id', $aids)->field('id,username,avatar,realname,phone')->select();
        return $admins ? $admins : [];
    }
}