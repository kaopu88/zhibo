<?php

namespace bxkj_module\service;

use think\Db;
use think\facade\Request;

class AgentAdmin extends UserSys
{
    protected $sysName = 'agent_admin';
    protected $idName = '管理员';
    protected $tabName = 'agent_admin';

    public function setRoot($inputData, $pid = null)
    {
        if (empty($inputData['agent_id'])) return $this->setError('请选择'.config('app.agent_setting.agent_name'));
        $where = ['id' => $inputData['agent_id'], 'delete_time' => null];
        if (isset($pid)) $where['pid'] = $pid;
        $agent = Db::name('agent')->where($where)->find();
        if (!$agent) return $this->setError(config('app.agent_setting.agent_name').'不存在');
        if (!empty($agent['root_id'])) return $this->setError(config('app.agent_setting.agent_name').'已设置主账号');
        $data = $this->df->process('set_root@agent_admin', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $data['salt'] = sha1(uniqid() . get_ucode());
        $data['password'] = sha1($inputData['password'] . $data['salt']);
        $data['is_root'] = '1';
        $data['agent_id'] = $agent['id'];
        $id = Db::name('agent_admin')->insertGetId($data);
        if (!$id) return $this->setError('设置失败');
        $num = Db::name('agent')->where(['id' => $data['agent_id']])->update(['root_id' => $id]);
        if (!$num) return $this->setError('设置失败');
        Db::name('agent')->where($where)->update(['temppass'=>0]);
        return $id;
    }

    public function setNewRoot($inputData, $pid = null)
    {
        if (empty($inputData['agent_id'])) return $this->setError('请选择'.config('app.agent_setting.agent_name'));
        $where = ['id' => $inputData['agent_id'], 'delete_time' => null];
        if (isset($pid)) $where['pid'] = $pid;
        $agent = Db::name('agent')->where($where)->find();
        if (!$agent) return $this->setError(config('app.agent_setting.agent_name').'不存在');
        if (!empty($agent['root_id'])) return $this->setError(config('app.agent_setting.agent_name').'已设置主账号');
        //$data = $this->df->process('set_root@agent_admin', $inputData)->output();
        //if ($data === false) return $this->setError($this->df->getError());
        $data['username'] = $inputData['username'];
        $data['phone'] = $inputData['phone'];
        $data['salt'] = sha1(uniqid() . get_ucode());
        $data['password'] = sha1($inputData['password'] . $data['salt']);
        $data['is_root'] = '1';
        $data['agent_id'] = $agent['id'];
        $id = Db::name('agent_admin')->insertGetId($data);
        if (!$id) return $this->setError('设置失败');
        $num = Db::name('agent')->where(['id' => $data['agent_id']])->update(['root_id' => $id]);
        if (!$num) return $this->setError('设置失败');
        Db::name('agent')->where($where)->update(['temppass'=>0]);
        return $id;
    }

    public function validateUsername($value, $rule, $data, $more)
    {
        $where = [
            ['delete_time', 'null'],
            ['username', '=', $value]
        ];
        if (!empty($data['id'])) $where[] = ['id', '!=', $data['id']];
        $count = Db::name('agent_admin')->where($where)->count();
        return $count <= 0;
    }

    public function validatePhone($value, $rule, $data, $more)
    {
        $where = [
            ['delete_time', 'null'],
            ['phone', '=', $value],
        ];
        if (!empty($data['id'])) $where[] = ['id', '!=', $data['id']];
        $count = Db::name('agent_admin')->where($where)->count();
        return $count <= 0;
    }
}